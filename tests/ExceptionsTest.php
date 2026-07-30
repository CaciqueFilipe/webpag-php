<?php

namespace WebPag\Tests;

use PHPUnit\Framework\TestCase;
use WebPag\Exceptions\ApiException;
use WebPag\Exceptions\WebPagException;

class ExceptionsTest extends TestCase
{
    public function testWebPagExceptionIsThrowable()
    {
        $e = new WebPagException('Generic error');

        $this->assertInstanceOf(\Throwable::class, $e);
        $this->assertEquals('Generic error', $e->getMessage());
    }

    public function testApiExceptionExtendsWebPagException()
    {
        $e = new ApiException('API error');

        $this->assertInstanceOf(WebPagException::class, $e);
    }

    public function testApiExceptionGetStatusCode()
    {
        $e = new ApiException('Not found', 404);

        $this->assertEquals(404, $e->getStatusCode());
    }

    public function testApiExceptionGetResponseBody()
    {
        $body = ['message' => 'Payment not found', 'error' => 'not_found'];
        $e = new ApiException('Not found', 404, $body);

        $this->assertEquals($body, $e->getResponseBody());
    }

    public function testApiExceptionGetErrorMessageFromMessage()
    {
        $body = ['message' => 'Payment not found'];
        $e = new ApiException('Not found', 404, $body);

        $this->assertEquals('Payment not found', $e->getErrorMessage());
    }

    public function testApiExceptionGetErrorMessageFromError()
    {
        $body = ['error' => 'Invalid data'];
        $e = new ApiException('Error', 422, $body);

        $this->assertEquals('Invalid data', $e->getErrorMessage());
    }

    public function testApiExceptionGetErrorMessageFromErrorArray()
    {
        $body = ['error' => ['field' => 'Required']];
        $e = new ApiException('Error', 422, $body);

        $this->assertJson($e->getErrorMessage());
    }

    public function testApiExceptionGetErrorMessageReturnsNull()
    {
        $e = new ApiException('Error', 500);

        $this->assertNull($e->getErrorMessage());
    }

    public function testApiExceptionGetErrorMessageReturnsNullForNonArrayBody()
    {
        $e = new ApiException('Error', 500, null);

        $this->assertNull($e->getErrorMessage());
    }

    public function testApiExceptionPreviousException()
    {
        $previous = new \Exception('Previous error');
        $e = new ApiException('API error', 500, null, $previous);

        $this->assertSame($previous, $e->getPrevious());
    }

    public function testApiExceptionGetErrorCode()
    {
        $body = ['error_code' => 18];
        $e = new ApiException('API Error', 400, $body);

        $this->assertEquals(18, $e->getErrorCode());
    }

    public function testApiExceptionGetErrorCodeReturnsNullWhenMissing()
    {
        $e = new ApiException('API Error', 400, []);

        $this->assertNull($e->getErrorCode());
    }

    public function testApiExceptionGetErrorPreviousParsesJsonString()
    {
        $jsonPrevious = '{"erros":[{"codigo":"4500718","mensagem":"O CPF informado para o pagador está inválido."}]}';
        $body = [
            'error_previous' => $jsonPrevious
        ];

        $e = new ApiException('API Error', 400, $body);

        $resultArray = $e->getErrorPrevious(true);
        $this->assertIsArray($resultArray);
        $this->assertEquals('O CPF informado para o pagador está inválido.', $resultArray['erros'][0]['mensagem']);

        $resultObject = $e->getErrorPrevious(false);
        $this->assertIsObject($resultObject);
        $this->assertEquals('O CPF informado para o pagador está inválido.', $resultObject->erros[0]->mensagem);
    }

    public function testApiExceptionGetErrorPreviousReturnsRawStringIfNotJson()
    {
        $body = ['error_previous' => 'Texto simples de erro'];
        $e = new ApiException('API Error', 400, $body);

        $this->assertEquals('Texto simples de erro', $e->getErrorPrevious());
    }

    public function testApiExceptionGetErrorPreviousReturnsNullWhenMissing()
    {
        $e = new ApiException('API Error', 400, []);

        $this->assertNull($e->getErrorPrevious());
    }

    public function testApiExceptionGetErrorTrace()
    {
        $trace = ['#0 /path/to/file.php(10): method()'];
        $body = ['error_trace' => $trace];

        $e = new ApiException('API Error', 500, $body);

        $this->assertEquals($trace, $e->getErrorTrace());
    }

    public function testApiExceptionGetErrorTraceReturnsNullWhenMissing()
    {
        $e = new ApiException('API Error', 500, []);

        $this->assertNull($e->getErrorTrace());
    }
}
