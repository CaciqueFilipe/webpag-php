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
        $this->assertSame('gateway-exemplo', $business->gatewayName);
        $this->assertSame('empresa@example.com', $business->notificationEmail);
        $this->assertSame('00000000000191', $business->cnpj);
        $this->assertSame('(00)00000-000', $business->phoneNumber);
        $this->assertSame(10, $business->status);
        $this->assertSame(1234, $business->currentBalance);

        $fees = $business->fees;
        $this->assertInstanceOf(BusinessFees::class, $fees);
        $this->assertNull($fees->serviceFeeType);
        $this->assertSame(3, $fees->creditInstallmentLimit);
        $this->assertTrue($fees->boletoEnabled);
        $this->assertSame(1.5, $fees->serviceFee);
        $this->assertSame(7.5, $fees->transferExpressFee);
        $this->assertSame(2.5, $fees->credit1xFee);
        $this->assertSame(3.5, $fees->credit6xFee);
        $this->assertSame(4.5, $fees->credit12xFee);
        $this->assertSame(0.0, $fees->credit18xFee);
        $this->assertSame(1.25, $fees->debitFee);
        $this->assertSame(0.75, $fees->pixFee);
        $this->assertSame(0.0, $fees->pixFeePercentage);
        $this->assertSame(0.0, $fees->pixAutomaticFee);
        $this->assertSame(0.0, $fees->pixAutomaticFeePercentage);
        $this->assertSame(2.25, $fees->boletoFee);
        $this->assertSame(1.5, $fees->boletoInterest);
        $this->assertSame(2.5, $fees->boletoFine);
        $this->assertSame(3.25, $fees->installmentPlanFee);
        $this->assertSame(1.25, $fees->installmentPlanFeeFixed);
        $this->assertSame(1.75, $fees->anticipationFee);

        // toArray() omite nulls (service_fee_type)
        unset($body['fees']['service_fee_type']);
        $this->assertSame($body, $business->toArray());
    }

    public function testMeFeesNumbersAreFloatsEvenWhenApiSendsIntegersOrStrings()
    {
        $http = $this->createMock(HttpClient::class);
        $http->method('get')->willReturn(new ApiResponse([
            'id' => 1,
            'fees' => ['transfer_express_fee' => 10, 'pix_fee' => '0.99', 'credit_18x_fee' => 0, 'boleto_enabled' => false],
        ], 200));

        $fees = (new Business($http))->me()->fees;

        $this->assertSame(10.0, $fees->transferExpressFee);
        $this->assertSame(0.99, $fees->pixFee);
        $this->assertSame(0.0, $fees->credit18xFee);
        $this->assertFalse($fees->boletoEnabled);
        $this->assertNull($fees->debitFee);
        $this->assertSame(['transfer_express_fee' => 10.0, 'credit_18x_fee' => 0.0, 'pix_fee' => 0.99, 'boleto_enabled' => false], $fees->toArray());
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
