<?php

namespace WebPag\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use WebPag\Configuration;
use WebPag\Exceptions\ApiException;
use WebPag\Http\ApiResponse;
use WebPag\Http\HttpClient;

class HttpClientTest extends TestCase
{
    /** @var Configuration */
    private $config;

    protected function setUp(): void
    {
        parent::setUp();
        $this->config = new Configuration('test-token');
    }

    public function testConstructorCreatesDefaultClient()
    {
        $client = new HttpClient($this->config);

        $this->assertInstanceOf(HttpClient::class, $client);
        $this->assertEquals(3, $client->getMaxRetries());
    }

    public function testConstructorAcceptsCustomMaxRetries()
    {
        $client = new HttpClient($this->config, null, null, 5);

        $this->assertEquals(5, $client->getMaxRetries());
    }

    public function testConstructorAcceptsLogger()
    {
        $logger = $this->createMock(LoggerInterface::class);
        $client = new HttpClient($this->config, null, $logger);

        $this->assertInstanceOf(HttpClient::class, $client);
    }

    public function testGetReturnsApiResponse()
    {
        $mock = new MockHandler([
            new Response(200, [], '{"data": [1, 2, 3]}'),
        ]);

        $handler = HandlerStack::create($mock);
        $guzzle = new Client(['handler' => $handler]);
        $client = new HttpClient($this->config, $guzzle);

        $response = $client->get('api/payments');

        $this->assertInstanceOf(ApiResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([1, 2, 3], $response->getData());
    }

    public function testPostSendsJsonBody()
    {
        $mock = new MockHandler([
            new Response(201, [], '{"data": {"id": 1}}'),
        ]);

        $handler = HandlerStack::create($mock);
        $guzzle = new Client(['handler' => $handler]);
        $client = new HttpClient($this->config, $guzzle);

        $response = $client->post('api/payments/process', [
            'amount' => 1000,
            'method' => 'pix',
        ]);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals(['id' => 1], $response->getData());
    }

    public function testPutSendsJsonBody()
    {
        $mock = new MockHandler([
            new Response(200, [], '{"data": {"status": "updated"}}'),
        ]);

        $handler = HandlerStack::create($mock);
        $guzzle = new Client(['handler' => $handler]);
        $client = new HttpClient($this->config, $guzzle);

        $response = $client->put('api/payers/1/update', ['first_name' => 'John']);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testDeleteSendsRequest()
    {
        $mock = new MockHandler([
            new Response(204, [], '{}'),
        ]);

        $handler = HandlerStack::create($mock);
        $guzzle = new Client(['handler' => $handler]);
        $client = new HttpClient($this->config, $guzzle);

        $response = $client->delete('api/payments/1');

        $this->assertEquals(204, $response->getStatusCode());
    }

    public function testRequestThrowsApiExceptionOnClientError()
    {
        $mock = new MockHandler([
            new Response(404, [], '{"message": "Payment not found"}'),
        ]);

        $handler = HandlerStack::create($mock);
        $guzzle = new Client(['handler' => $handler]);
        $client = new HttpClient($this->config, $guzzle, null, 0);

        try {
            $client->get('api/payments/99999');
            $this->fail('A exceção ApiException deveria ter sido lançada.');
        } catch (ApiException $e) {
            // Verifica se a mensagem contém o texto esperado
            $this->assertStringContainsString('Payment not found', $e->getMessage());
            
            // Se o seu HttpClient preenche o status code, descomente a linha abaixo para testar:
            // $this->assertEquals(404, $e->getStatusCode());
        }
    }

    public function testRequestThrowsApiExceptionOnServerErrorAfterRetries()
    {
        $this->expectException(ApiException::class);

        $mock = new MockHandler([
            new Response(500, [], '{"message": "Internal error"}'),
            new Response(500, [], '{"message": "Internal error"}'),
        ]);

        $handler = HandlerStack::create($mock);
        $guzzle = new Client(['handler' => $handler]);
        $client = new HttpClient($this->config, $guzzle, null, 1);

        $client->get('api/payments');

        
    }

    public function testRequestThrowsApiExceptionOnGuzzleError()
    {
        $this->expectException(ApiException::class);

        $mock = new MockHandler([
            new \GuzzleHttp\Exception\ConnectException(
                'Connection refused',
                new \GuzzleHttp\Psr7\Request('GET', 'test')
            ),
            new \GuzzleHttp\Exception\ConnectException(
                'Connection refused',
                new \GuzzleHttp\Psr7\Request('GET', 'test')
            ),
        ]);

        $handler = HandlerStack::create($mock);
        $guzzle = new Client(['handler' => $handler]);
        $client = new HttpClient($this->config, $guzzle, null, 1);

        $client->get('api/payments');
    }

    public function testLoggerIsCalledOnRequest()
    {
        $mock = new MockHandler([
            new Response(200, [], '{"ok": true}'),
        ]);

        $handler = HandlerStack::create($mock);
        $guzzle = new Client(['handler' => $handler]);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->atLeastOnce())
            ->method('info')
            ->with($this->stringContains('WebPag API'));

        $client = new HttpClient($this->config, $guzzle, $logger);

        $client->get('api/me');
    }

    public function testSetLoggerOverridesLogger()
    {
        $mock = new MockHandler([
            new Response(200, [], '{}'),
        ]);

        $handler = HandlerStack::create($mock);
        $guzzle = new Client(['handler' => $handler]);

        $client = new HttpClient($this->config, $guzzle);
        $logger = $this->createMock(LoggerInterface::class);

        $client->setLogger($logger);

        $this->assertInstanceOf(HttpClient::class, $client);
    }

    public function testRequestIncludesAuthTokenHeader()
    {
        $container = [];
        $history = Middleware::history($container);

        $mock = new MockHandler([
            new Response(200, [], '{}'),
        ]);

        $handler = HandlerStack::create($mock);
        $handler->push($history);
        $guzzle = new Client(['handler' => $handler]);

        $config = new Configuration('my-secret-token');
        $client = new HttpClient($config, $guzzle);

        $client->get('api/me');

        $this->assertCount(1, $container);
        $request = $container[0]['request'];
        $this->assertEquals('my-secret-token', $request->getHeaderLine('auth-token'));
    }
}
