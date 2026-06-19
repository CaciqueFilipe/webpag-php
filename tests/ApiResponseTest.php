<?php

namespace WebPag\Tests;

use PHPUnit\Framework\TestCase;
use WebPag\Http\ApiResponse;

class ApiResponseTest extends TestCase
{
    public function testToArrayReturnsBody()
    {
        $body = ['id' => 1, 'name' => 'Test'];
        $response = new ApiResponse($body, 200);

        $this->assertEquals($body, $response->toArray());
    }

    public function testGetStatusCode()
    {
        $response = new ApiResponse([], 201);

        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testGetDataReturnsDataKey()
    {
        $body = ['data' => ['id' => 1], 'message' => 'ok'];
        $response = new ApiResponse($body, 200);

        $this->assertEquals(['id' => 1], $response->getData());
    }

    public function testGetDataReturnsWholeBodyWhenNoDataKey()
    {
        $body = ['id' => 1, 'name' => 'Test'];
        $response = new ApiResponse($body, 200);

        $this->assertEquals($body, $response->getData());
    }

    public function testGetReturnsValueForKey()
    {
        $response = new ApiResponse(['message' => 'hello'], 200);

        $this->assertEquals('hello', $response->get('message'));
    }

    public function testGetReturnsDefaultWhenKeyMissing()
    {
        $response = new ApiResponse([], 200);

        $this->assertNull($response->get('nonexistent'));
        $this->assertEquals('default', $response->get('nonexistent', 'default'));
    }

    public function testArrayAccessExists()
    {
        $response = new ApiResponse(['foo' => 'bar'], 200);

        $this->assertTrue(isset($response['foo']));
        $this->assertFalse(isset($response['baz']));
    }

    public function testArrayAccessGet()
    {
        $response = new ApiResponse(['foo' => 'bar'], 200);

        $this->assertEquals('bar', $response['foo']);
    }

    public function testArrayAccessSet()
    {
        $response = new ApiResponse([], 200);
        $response['foo'] = 'bar';

        $this->assertEquals('bar', $response['foo']);
    }

    public function testArrayAccessUnset()
    {
        $response = new ApiResponse(['foo' => 'bar'], 200);
        unset($response['foo']);

        $this->assertFalse(isset($response['foo']));
    }

    public function testJsonSerialize()
    {
        $body = ['id' => 1, 'data' => ['key' => 'value']];
        $response = new ApiResponse($body, 200);

        $this->assertEquals($body, $response->jsonSerialize());
    }

    public function testFromResponseCreatesFromPsrResponse()
    {
        $stream = $this->createMock(\Psr\Http\Message\StreamInterface::class);
        $stream->method('__toString')->willReturn('{"data": {"id": 1}}');

        $psrResponse = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
        $psrResponse->method('getBody')->willReturn($stream);
        $psrResponse->method('getStatusCode')->willReturn(200);

        $response = ApiResponse::fromResponse($psrResponse);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['id' => 1], $response->getData());
    }

    public function testFromResponseHandlesInvalidJson()
    {
        $stream = $this->createMock(\Psr\Http\Message\StreamInterface::class);
        $stream->method('__toString')->willReturn('not-json');

        $psrResponse = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
        $psrResponse->method('getBody')->willReturn($stream);
        $psrResponse->method('getStatusCode')->willReturn(500);

        $response = ApiResponse::fromResponse($psrResponse);

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals(['raw' => 'not-json'], $response->toArray());
    }
}
