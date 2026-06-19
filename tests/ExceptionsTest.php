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
}
