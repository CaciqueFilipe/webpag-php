<?php

namespace WebPag\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WebPag\Configuration;
use WebPag\Exceptions\ApiException;
use WebPag\Http\HttpClient;
use WebPag\Http\Transport\CurlTransport;

/**
 * Testa o transporte cURL de verdade, contra o servidor embutido do PHP em 127.0.0.1.
 */
class CurlTransportTest extends TestCase
{
    private const TOKEN = 'tok_live_1234567890abcdef';

    /** @var resource|null */
    private static $process;

    /** @var string */
    private static $baseUrl = '';

    public static function setUpBeforeClass(): void
    {
        if (! extension_loaded('curl')) {
            self::markTestSkipped('ext-curl indisponível.');
        }

        $port = self::freePort();
        $windows = DIRECTORY_SEPARATOR === '\\';
        $devNull = $windows ? 'NUL' : '/dev/null';
        $command = ($windows ? '' : 'exec ') . escapeshellarg(PHP_BINARY)
            . ' -S 127.0.0.1:' . $port . ' ' . escapeshellarg(__DIR__ . '/Support/curl_server.php');

        $pipes = [];
        self::$process = proc_open(
            $command,
            [0 => ['pipe', 'r'], 1 => ['file', $devNull, 'w'], 2 => ['file', $devNull, 'w']],
            $pipes,
            null,
            null,
            ['bypass_shell' => true]
        );

        for ($i = 0; $i < 50; $i++) {
            $socket = @fsockopen('127.0.0.1', $port, $errno, $errstr, 0.1);
            if ($socket !== false) {
                fclose($socket);
                self::$baseUrl = 'http://127.0.0.1:' . $port;

                return;
            }
            usleep(100000);
        }

        self::markTestSkipped('Não foi possível iniciar o servidor HTTP local.');
    }

    public static function tearDownAfterClass(): void
    {
        if (is_resource(self::$process)) {
            proc_terminate(self::$process);
            proc_close(self::$process);
        }
    }

    /**
     * @param int                  $timeout
     * @param array<string, mixed> $transportOptions
     *
     * @return HttpClient
     */
    private function client($timeout = 5, array $transportOptions = [])
    {
        $config = new Configuration(self::TOKEN, self::$baseUrl, $timeout);

        return new HttpClient($config, new CurlTransport($config->getBaseUrl(), $transportOptions), null, 0);
    }

    /**
     * @return int
     */
    private static function freePort()
    {
        $server = stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);
        $name = stream_socket_get_name($server, false);
        fclose($server);

        return (int) substr($name, strrpos($name, ':') + 1);
    }

    // ---------------------------------------------------------------- Requisições reais

    public function testGetSendsAuthHeadersAndEncodedQuery()
    {
        $data = $this->client()->get('api/echo', ['cpf_cnpj' => '00000000191', 'nome' => 'João Silva', 'page' => 2])->getData();

        $this->assertSame('GET', $data['method']);
        $this->assertSame(['cpf_cnpj' => '00000000191', 'nome' => 'João Silva', 'page' => '2'], $data['query']);
        $this->assertStringContainsString('nome=Jo%C3%A3o%20Silva', $data['uri']);
        $this->assertSame(self::TOKEN, $data['headers']['auth-token']);
        $this->assertSame('application/json', $data['headers']['accept']);
        $this->assertArrayNotHasKey('expect', $data['headers']);
        $this->assertSame('', $data['body']);
    }

    public function testPostSendsJsonBody()
    {
        $body = ['amount' => 1000, 'name' => 'Pedido nº 1 — ação', 'splits' => [['amount' => 99]]];

        $data = $this->client()->post('api/echo', $body)->getData();

        $this->assertSame('POST', $data['method']);
        $this->assertSame('application/json', $data['headers']['content-type']);
        $this->assertSame($body, json_decode($data['body'], true));
    }

    public function testPutAndDeleteMethods()
    {
        $this->assertSame('PUT', $this->client()->put('api/echo', ['x' => 1])->getData()['method']);
        $this->assertSame('DELETE', $this->client()->delete('api/echo')->getData()['method']);
    }

    public function testHttpErrorBecomesApiExceptionWithStatusAndBody()
    {
        try {
            $this->client()->get('api/status/404');
            $this->fail('Deveria lançar ApiException.');
        } catch (ApiException $e) {
            $this->assertSame(404, $e->getStatusCode());
            $this->assertSame('status 404', $e->getMessage());
            $this->assertSame(['message' => 'status 404'], $e->getResponseBody());
        }
    }

    public function testRedirectIsNeverFollowed()
    {
        $response = $this->client()->get('api/redirect');

        // Se seguisse, a resposta seria o /api/echo (200) com o auth-token reenviado
        $this->assertSame(302, $response->getStatusCode());
    }

    public function testResponseSizeLimit()
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('maior que o limite');

        $this->client(5, ['max_response_bytes' => 1024 * 1024])->get('api/big');
    }

    public function testConnectionFailureOnPostIsNotRetriedAndWarns()
    {
        $port = self::freePort(); // porta sem servidor
        $config = new Configuration(self::TOKEN, 'http://127.0.0.1:' . $port, 2);
        $client = new HttpClient($config, null, null, 3);

        try {
            $client->post('api/payments/process', ['amount' => 1000]);
            $this->fail('Deveria lançar ApiException.');
        } catch (ApiException $e) {
            $this->assertStringContainsString('pode ter sido processada', $e->getMessage());
            $this->assertStringContainsString('cURL', $e->getMessage());
        }
    }

    public function testResponseHeaderFloodIsAborted()
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('headers');

        $this->client()->get('api/header-flood');
    }

    public function testTimeout()
    {
        // Mantido por último: o servidor embutido atende uma requisição por vez
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('cURL 28');

        $this->client(1)->get('api/slow');
    }

    // ---------------------------------------------------------------- Opções do cURL (sem rede)

    public function testSecureCurlOptionsByDefault()
    {
        $options = (new CurlTransport('https://api.webpag.com.br'))->buildCurlOptions('GET', 'api/me', [
            'headers' => ['auth-token' => self::TOKEN],
            'timeout' => 30,
            'connect_timeout' => 10,
        ]);

        $this->assertSame('https://api.webpag.com.br/api/me', $options[CURLOPT_URL]);
        $this->assertFalse($options[CURLOPT_FOLLOWLOCATION]);
        $this->assertTrue($options[CURLOPT_SSL_VERIFYPEER]);
        $this->assertSame(2, $options[CURLOPT_SSL_VERIFYHOST]);
        $this->assertSame(CURLPROTO_HTTPS | CURLPROTO_HTTP, $options[CURLOPT_PROTOCOLS]);
        $this->assertSame('', $options[CURLOPT_PROXY], 'Proxies de variáveis de ambiente precisam ficar desativados');
        $this->assertSame(30, $options[CURLOPT_TIMEOUT]);
        $this->assertSame(10, $options[CURLOPT_CONNECTTIMEOUT]);
        $this->assertContains('Expect:', $options[CURLOPT_HTTPHEADER]);
        $this->assertArrayNotHasKey(CURLOPT_POSTFIELDS, $options);
        $this->assertArrayNotHasKey(CURLOPT_CAINFO, $options);
    }

    public function testExplicitProxyAndCaBundle()
    {
        $transport = new CurlTransport('https://api.webpag.com.br', [
            'proxy' => 'http://user:secret@proxy.interno:3128',
            'ca_bundle' => __FILE__,
        ]);
        $options = $transport->buildCurlOptions('GET', 'api/me', []);

        $this->assertSame('http://user:secret@proxy.interno:3128', $options[CURLOPT_PROXY]);
        $this->assertSame(__FILE__, $options[CURLOPT_CAINFO]);
        $this->assertStringNotContainsString('secret', print_r($transport, true));
    }

    public function testHeadHasNoBody()
    {
        $options = (new CurlTransport('https://api.webpag.com.br'))->buildCurlOptions('HEAD', 'api/me', ['json' => ['x' => 1]]);

        $this->assertTrue($options[CURLOPT_NOBODY]);
        $this->assertArrayNotHasKey(CURLOPT_POSTFIELDS, $options);
    }

    /**
     * @return array<string, array{0: array<string, mixed>, 1: string}>
     */
    public function invalidRequestProvider()
    {
        return [
            'header injection (CRLF no valor)' => [['headers' => ['auth-token' => "tok\r\nX-Evil: 1"]], 'GET'],
            'LF no valor' => [['headers' => ['X-Test' => "a\nb"]], 'GET'],
            'NUL no valor' => [['headers' => ['X-Test' => "a\0b"]], 'GET'],
            'nome de header inválido' => [['headers' => ["X-Evil\r\n" => '1']], 'GET'],
            'valor não escalar' => [['headers' => ['X-Test' => ['a']]], 'GET'],
            'método inválido' => [[], "GET /x HTTP/1.1\r\n"],
            'JSON inválido (UTF-8 malformado)' => [['json' => ['name' => "\xB1\x31"]], 'POST'],
        ];
    }

    /**
     * @dataProvider invalidRequestProvider
     *
     * @param array<string, mixed> $options
     * @param string               $method
     */
    public function testRejectsUnsafeRequests(array $options, $method)
    {
        $this->expectException(InvalidArgumentException::class);

        (new CurlTransport('https://api.webpag.com.br'))->buildCurlOptions($method, 'api/me', $options);
    }

    /**
     * @return array<string, array{0: array<string, mixed>}>
     */
    public function invalidTransportOptionsProvider()
    {
        return [
            'proxy socks' => [['proxy' => 'socks5://127.0.0.1:1080']],
            'proxy file' => [['proxy' => 'file:///etc/passwd']],
            'proxy sem host' => [['proxy' => 'proxy-sem-esquema']],
            'ca_bundle inexistente' => [['ca_bundle' => __DIR__ . '/nao-existe.pem']],
            'limite zero' => [['max_response_bytes' => 0]],
        ];
    }

    /**
     * @dataProvider invalidTransportOptionsProvider
     *
     * @param array<string, mixed> $options
     */
    public function testRejectsInvalidTransportOptions(array $options)
    {
        $this->expectException(InvalidArgumentException::class);

        new CurlTransport('https://api.webpag.com.br', $options);
    }
}
