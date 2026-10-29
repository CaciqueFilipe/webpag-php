<?php

namespace WebPag\Tests;

use PHPUnit\Framework\TestCase;
use WebPag\Http\ApiResponse;
use WebPag\Http\HttpClient;
use WebPag\Resources\Business;
use WebPag\Responses\Business\Business as BusinessResponse;
use WebPag\Responses\Business\BusinessFees;

class BusinessResponseTest extends TestCase
{
    public function testMeFieldTypes()
    {
        $body = json_decode(file_get_contents(__DIR__ . '/fixtures/me.json'), true);

        $http = $this->createMock(HttpClient::class);
        $http->expects($this->once())
            ->method('get')
            ->with('api/me')
            ->willReturn(new ApiResponse($body, 200));

        $business = (new Business($http))->me();

        $this->assertInstanceOf(BusinessResponse::class, $business);
        $this->assertSame(1, $business->id);
        $this->assertSame('Empresa Exemplo', $business->name);
        $this->assertSame('Empresa Exemplo', $business->gatewayName);
        $this->assertSame('empresa@example.com', $business->notificationEmail);
        $this->assertSame('12.345.678/0001-99', $business->cnpj);
        $this->assertSame('41999999999', $business->phoneNumber);
        $this->assertSame(10, $business->status);
        $this->assertSame(150050, $business->currentBalance);

        $this->assertInstanceOf(BusinessFees::class, $business->fees);
        $this->assertSame(2.5, $business->fees->serviceFee);
        $this->assertSame('F', $business->fees->serviceFeeType);
        $this->assertSame(3.99, $business->fees->credit1xFee);
        $this->assertSame(0.99, $business->fees->pixFee);
        $this->assertSame(2.5, $business->fees->boletoFee);

        $this->assertSame($body, $business->toArray());
    }

    public function testMeDecimalStringFeesAreNotTruncated()
    {
        $http = $this->createMock(HttpClient::class);
        $http->method('get')->willReturn(new ApiResponse(['id' => 1, 'fees' => ['pix_fee' => '0.99']], 200));

        $business = (new Business($http))->me();

        $this->assertSame(0.99, $business->fees->pixFee);
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
        $this->assertNull($business->fees);
    }
}
