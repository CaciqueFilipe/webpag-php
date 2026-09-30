<?php

namespace WebPag\Tests;

use PHPUnit\Framework\TestCase;
use WebPag\Http\ApiResponse;
use WebPag\Http\HttpClient;
use WebPag\Resources\Business;
use WebPag\Responses\Business\Business as BusinessResponse;

class BusinessResponseTest extends TestCase
{
    public function testMeFieldTypes()
    {
        $body = [
            'id' => 8421,
            'name' => 'EMPRESA TESTE',
            'notification_email' => 'financeiro@example.com',
            'cnpj' => '00000000000191',
        ];

        $http = $this->createMock(HttpClient::class);
        $http->expects($this->once())
            ->method('get')
            ->with('api/me')
            ->willReturn(new ApiResponse($body, 200));

        $business = (new Business($http))->me();

        $this->assertInstanceOf(BusinessResponse::class, $business);
        $this->assertSame(8421, $business->id);
        $this->assertSame('EMPRESA TESTE', $business->name);
        $this->assertSame('financeiro@example.com', $business->notificationEmail);
        $this->assertSame('00000000000191', $business->cnpj);
        $this->assertSame($body, $business->toArray());
    }

    public function testMeAlsoAcceptsDataEnvelope()
    {
        $http = $this->createMock(HttpClient::class);
        $http->method('get')
            ->willReturn(new ApiResponse(['data' => ['id' => 1, 'name' => 'X']], 200));

        $business = (new Business($http))->me();

        $this->assertSame(1, $business->id);
        $this->assertSame('X', $business->name);
        $this->assertNull($business->cnpj);
    }
}
