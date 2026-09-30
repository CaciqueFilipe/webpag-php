<?php

namespace WebPag\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use WebPag\Configuration;
use WebPag\Environment;
use WebPag\Exceptions\ApiException;
use WebPag\Exceptions\WebPagException;
use WebPag\Http\ApiResponse;
use WebPag\Http\HttpClient;
use WebPag\Requests\Payers\SaveCreditCardRequest;
use WebPag\Requests\Payments\CreditCardData;
use WebPag\Requests\Payments\ProcessPaymentRequest;
use WebPag\Resources\Payers;
use WebPag\Resources\Recurrency;
use WebPag\Support\SensitiveData;
use WebPag\Webhooks\WebhookEvent;
use WebPag\Webhooks\WebhookParser;
use WebPag\WebPag;

class SecurityTest extends TestCase
{
    private const TOKEN = 'tok_live_1234567890abcdef';

    /** @var array<int, array<string, mixed>> */
    private $history = [];

    /**
     * @param array<int, mixed> $queue Respostas/exceções simuladas, em ordem
     * @param int               $maxRetries
     * @param \Psr\Log\LoggerInterface|null $logger
     *
     * @return HttpClient
     */
    private function clientWith(array $queue, $maxRetries = 3, $logger = null)
    {
        $this->history = [];
        $stack = HandlerStack::create(new MockHandler($queue));
        $stack->push(Middleware::history($this->history));

        return new HttpClient(new Configuration(self::TOKEN), new Client(['handler' => $stack]), $logger, $maxRetries);
    }

    // ---------------------------------------------------------------- Configuration / URL

    /**
     * @return array<string, array{0: string}>
     */
    public function insecureBaseUrlProvider()
    {
        return [
            'http remoto' => ['http://api.webpag.com.br'],
            'sem esquema' => ['api.webpag.com.br'],
            'ftp' => ['ftp://api.webpag.com.br'],
            'com credenciais' => ['https://user:pass@api.webpag.com.br'],
            'com query' => ['https://api.webpag.com.br?x=1'],
            'com fragmento' => ['https://api.webpag.com.br#x'],
            'vazia' => [''],
        ];
    }

    /**
     * @dataProvider insecureBaseUrlProvider
     *
     * @param string $url
     */
    public function testRejectsInsecureOrInvalidBaseUrl($url)
    {
        $this->expectException(InvalidArgumentException::class);

        new Configuration(self::TOKEN, $url);
    }

    public function testAllowsHttpOnlyForLocalhost()
    {
        $this->assertSame('http://localhost:8080', (new Configuration(self::TOKEN, 'http://localhost:8080/'))->getBaseUrl());
        $this->assertSame('http://127.0.0.1', (new Configuration(self::TOKEN, 'http://127.0.0.1'))->getBaseUrl());
        $this->assertSame('https://sandbox.example.com/v1', (new Configuration(self::TOKEN, 'https://sandbox.example.com/v1/'))->getBaseUrl());
    }

    public function testRejectsInvalidTimeout()
    {
        $this->expectException(InvalidArgumentException::class);

        new Configuration(self::TOKEN, null, 0);
    }

    public function testEnvironmentWithHttpUrlFailsOnConversion()
    {
        $this->expectException(InvalidArgumentException::class);

        Environment::fromArray(['api_token' => self::TOKEN, 'base_url' => 'http://evil.example.com'])->toConfiguration();
    }

    // ---------------------------------------------------------------- Vazamento de token em debug

    public function testTokenIsMaskedInDebugOutput()
    {
        $webpag = WebPag::create(self::TOKEN);
        $environment = Environment::fromArray(['api_token' => self::TOKEN]);

        foreach ([$webpag, $webpag->getConfiguration(), $webpag->getHttpClient(), $environment] as $object) {
            $output = print_r($object, true);
            ob_start();
            var_dump($object);
            $output .= ob_get_clean();

            $this->assertStringNotContainsString(self::TOKEN, $output, get_class($object) . ' expôs o token');
            $this->assertStringContainsString('****cdef', $output);
        }

        // O getter continua devolvendo o valor real para uso interno
        $this->assertSame(self::TOKEN, $webpag->getConfiguration()->getApiToken());
    }

    public function testCardDataIsMaskedInDebugOutput()
    {
        $card = new CreditCardData();
        $card->number = '5555 4444 3333 1111';
        $card->securityCode = '987';
        $card->name = 'FULANO';

        $save = new SaveCreditCardRequest();
        $save->number = '4111111111111111';
        $save->securityCode = '123';
        $save->cardToken = 'card_tok_abcdefghijklmnop';

        $payment = new ProcessPaymentRequest();
        $payment->card = $card;

        $output = print_r($payment, true) . print_r($save, true);

        $this->assertStringNotContainsString('5555444433331111', str_replace(' ', '', $output));
        $this->assertStringNotContainsString('4111111111111111', $output);
        $this->assertStringNotContainsString('987', $output);
        $this->assertStringNotContainsString('card_tok_abcdefghijklmnop', $output);
        $this->assertStringContainsString('************1111', $output);

        // O payload enviado à API não é alterado pela máscara
        $this->assertSame('5555 4444 3333 1111', $card->toArray()['number']);
        $this->assertSame('123', $save->toArray()['security_code']);
    }

    public function testMaskHelpers()
    {
        $this->assertSame('', SensitiveData::mask(''));
        $this->assertSame('****', SensitiveData::mask('short'));
        $this->assertSame('****cdef', SensitiveData::mask(self::TOKEN));
        $this->assertNull(SensitiveData::maskCardNumber(null));
        $this->assertSame('****', SensitiveData::maskCardNumber('123'));
        $this->assertSame('************1111', SensitiveData::maskCardNumber('4111-1111-1111-1111'));
    }

    // ---------------------------------------------------------------- Retry sem duplicar cobrança

    public function testPostIsNotRetriedOnServerError()
    {
        $client = $this->clientWith([
            new Response(500, [], '{"message": "Internal error"}'),
            new Response(201, [], '{"data": {"id": 2}}'),
        ]);

        try {
            $client->post('api/payments/process', ['amount' => 1000]);
            $this->fail('Deveria lançar ApiException sem repetir o POST.');
        } catch (ApiException $e) {
            $this->assertSame(500, $e->getStatusCode());
        }

        $this->assertCount(1, $this->history, 'POST foi reenviado: risco de cobrança duplicada');
    }

    public function testPutIsNotRetriedOnServerError()
    {
        $client = $this->clientWith([
            new Response(503),
            new Response(200, [], '{}'),
        ]);

        try {
            $client->put('api/payments/1/refund');
            $this->fail('Deveria lançar ApiException sem repetir o PUT.');
        } catch (ApiException $e) {
            $this->assertSame(503, $e->getStatusCode());
        }

        $this->assertCount(1, $this->history, 'PUT (estorno) foi reenviado: risco de estorno duplicado');
    }

    public function testPostIsNotRetriedOnNetworkError()
    {
        $client = $this->clientWith([
            new ConnectException('timeout', new Request('POST', 'api/transfers')),
            new Response(201, [], '{}'),
        ]);

        try {
            $client->post('api/transfers', ['amount' => 5000]);
            $this->fail('Deveria lançar ApiException sem repetir o POST.');
        } catch (ApiException $e) {
            $this->assertStringContainsString('pode ter sido processada', $e->getMessage());
            $this->assertInstanceOf(ConnectException::class, $e->getPrevious());
        }

        $this->assertCount(1, $this->history, 'Saque foi reenviado após erro de rede');
    }

    public function testPostIsRetriedOnTooManyRequests()
    {
        $client = $this->clientWith([
            new Response(429, ['Retry-After' => '0']),
            new Response(201, [], '{"data": {"id": 9}}'),
        ]);

        $response = $client->post('api/payments/process', ['amount' => 1000]);

        $this->assertSame(['id' => 9], $response->getData());
        $this->assertCount(2, $this->history);
    }

    public function testGetIsRetriedOnServerError()
    {
        $client = $this->clientWith([
            new Response(502, ['Retry-After' => '0']),
            new Response(200, [], '{"data": {"id": 1}}'),
        ]);

        $this->assertSame(['id' => 1], $client->get('api/payments/1')->getData());
        $this->assertCount(2, $this->history);
    }

    public function testClientErrorsAreNeverRetried()
    {
        $client = $this->clientWith([
            new Response(422, [], '{"errors": {"amount": ["obrigatório"]}}'),
            new Response(200, [], '{}'),
        ]);

        try {
            $client->get('api/payments');
            $this->fail('Deveria lançar ApiException.');
        } catch (ApiException $e) {
            $this->assertSame('amount: obrigatório', $e->getMessage());
        }

        $this->assertCount(1, $this->history);
    }

    // ---------------------------------------------------------------- Redirect / logs

    public function testRedirectsAreDisabledEvenWithCustomClient()
    {
        $client = $this->clientWith([
            new Response(302, ['Location' => 'https://evil.example.com/steal']),
        ], 0);

        $response = $client->get('api/me');

        $this->assertSame(302, $response->getStatusCode());
        $this->assertCount(1, $this->history, 'Redirect foi seguido: o header auth-token iria para outro host');
        $this->assertFalse($this->history[0]['options']['allow_redirects']);
    }

    public function testDefaultClientDisablesRedirectsAndVerifiesTls()
    {
        $http = new HttpClient(new Configuration(self::TOKEN));
        $guzzle = (new \ReflectionProperty(HttpClient::class, 'client'));
        $guzzle->setAccessible(true);
        /** @var Client $client */
        $client = $guzzle->getValue($http);

        $config = method_exists($client, 'getConfig') ? $client->getConfig() : [];
        $this->assertFalse($config['allow_redirects']);
        $this->assertTrue($config['verify']);
        $this->assertSame(10, $config['connect_timeout']);
    }

    public function testLogsNeverContainQueryValuesOrToken()
    {
        $logger = new class () extends AbstractLogger {
            /** @var string */
            public $output = '';

            public function log($level, $message, array $context = []): void
            {
                $this->output .= $message . ' ' . json_encode($context) . "\n";
            }
        };

        $client = $this->clientWith([new Response(200, [], '{"data": []}')], 0, $logger);
        $client->get('api/payers', ['cpf_cnpj' => '12345678909', 'email' => 'pessoa@example.com']);

        $this->assertStringContainsString('cpf_cnpj', $logger->output);
        $this->assertStringNotContainsString('12345678909', $logger->output);
        $this->assertStringNotContainsString('pessoa@example.com', $logger->output);
        $this->assertStringNotContainsString(self::TOKEN, $logger->output);
    }

    // ---------------------------------------------------------------- Path injection

    /**
     * @return array<string, array{0: mixed}>
     */
    public function maliciousIdProvider()
    {
        return [
            'dot-dot' => ['..'],
            'dot' => ['.'],
            'vazio' => [''],
            'espaços' => ['   '],
            'null' => [null],
            'array' => [['1']],
            'float' => [1.5],
        ];
    }

    /**
     * @dataProvider maliciousIdProvider
     *
     * @param mixed $id
     */
    public function testRejectsInvalidIds($id)
    {
        $http = $this->createMock(HttpClient::class);
        $http->expects($this->never())->method('get');

        $this->expectException(InvalidArgumentException::class);

        (new Payers($http))->find($id);
    }

    public function testIdsAreUrlEncodedSoTheyCannotChangeThePath()
    {
        $calls = [];
        $http = $this->createMock(HttpClient::class);
        $http->method('get')->willReturnCallback(function ($uri) use (&$calls) {
            $calls[] = $uri;

            return new ApiResponse(['data' => ['id' => 1]], 200);
        });

        $payers = new Payers($http);
        $payers->find(15);
        $payers->find('1/../../transfers');
        $payers->find('1?cpf_cnpj=x#y');

        $this->assertSame([
            'api/payers/15',
            'api/payers/1%2F..%2F..%2Ftransfers',
            'api/payers/1%3Fcpf_cnpj%3Dx%23y',
        ], $calls);
    }

    public function testRecurrenceCodeIsEncoded()
    {
        $http = $this->createMock(HttpClient::class);
        $http->expects($this->once())
            ->method('put')
            ->with('api/payments/recurrency/abc%2Fdef/cancel')
            ->willReturn(new ApiResponse(['data' => []], 200));

        (new Recurrency($http))->cancel('abc/def');
    }

    // ---------------------------------------------------------------- Webhook

    public function testSignatureRejectsMissingOrMalformedInput()
    {
        $payload = '{"id":1}';
        $valid = hash_hmac('sha256', $payload, self::TOKEN);

        $this->assertTrue(WebhookParser::verifySignature($payload, $valid, self::TOKEN));
        $this->assertTrue(WebhookParser::verifySignature($payload, strtoupper($valid), self::TOKEN));
        $this->assertTrue(WebhookParser::verifySignature($payload, ' ' . $valid . "\n", self::TOKEN));

        $this->assertFalse(WebhookParser::verifySignature($payload, null, self::TOKEN), 'header ausente');
        $this->assertFalse(WebhookParser::verifySignature($payload, '', self::TOKEN));
        $this->assertFalse(WebhookParser::verifySignature($payload, substr($valid, 0, 63), self::TOKEN));
        $this->assertFalse(WebhookParser::verifySignature($payload, str_repeat('z', 64), self::TOKEN));
        $this->assertFalse(WebhookParser::verifySignature($payload, ['x'], self::TOKEN));
        $this->assertFalse(WebhookParser::verifySignature('', hash_hmac('sha256', '', self::TOKEN), self::TOKEN));
        $this->assertFalse(WebhookParser::verifySignature($payload . ' ', $valid, self::TOKEN), 'payload adulterado');
    }

    public function testSignatureWithEmptyTokenIsAlwaysRejected()
    {
        // Com chave vazia qualquer pessoa calcularia a assinatura
        $payload = '{"id":1,"status":40}';
        $forged = hash_hmac('sha256', $payload, '');

        $this->assertFalse(WebhookParser::verifySignature($payload, $forged, ''));
    }

    public function testParseVerified()
    {
        $parser = new WebhookParser();
        $payload = '{"id": 5, "payer_id": 1, "status": 40}';

        $event = $parser->parseVerified($payload, hash_hmac('sha256', $payload, self::TOKEN), self::TOKEN);
        $this->assertSame(WebhookEvent::TYPE_PAYMENT, $event->getType());

        $this->expectException(WebPagException::class);
        $parser->parseVerified($payload, hash_hmac('sha256', $payload, 'outro-token'), self::TOKEN);
    }

    // ---------------------------------------------------------------- Facade

    public function testMethodAccessorsForLaravelFacade()
    {
        $webpag = WebPag::create(self::TOKEN);

        $this->assertSame($webpag->payers, $webpag->payers());
        $this->assertSame($webpag->payments, $webpag->payments());
        $this->assertSame($webpag->paymentLinks, $webpag->paymentLinks());
        $this->assertSame($webpag->installments, $webpag->installments());
        $this->assertSame($webpag->recurrency, $webpag->recurrency());
        $this->assertSame($webpag->transfers, $webpag->transfers());
        $this->assertSame($webpag->business, $webpag->business());
        $this->assertSame($webpag->webhooks, $webpag->webhooks());
    }
}
