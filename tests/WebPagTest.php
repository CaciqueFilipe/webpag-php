<?php

namespace WebPag\Tests;

use PHPUnit\Framework\TestCase;
use WebPag\Configuration;
use WebPag\Environment;
use WebPag\Http\HttpClient;
use WebPag\Resources\Business;
use WebPag\Resources\Installments;
use WebPag\Resources\Payers;
use WebPag\Resources\PaymentLinks;
use WebPag\Resources\Payments;
use WebPag\Resources\Recurrency;
use WebPag\Resources\Transfers;
use WebPag\Webhooks\WebhookParser;
use WebPag\WebPag;

class WebPagTest extends TestCase
{
    public function testCreateReturnsInstance()
    {
        $webpag = WebPag::create('test-token');

        $this->assertInstanceOf(WebPag::class, $webpag);
        $this->assertEquals('test-token', $webpag->getConfiguration()->getApiToken());
    }

    public function testCreateWithCustomUrl()
    {
        $webpag = WebPag::create('token', 'https://custom.api.com');

        $this->assertEquals('https://custom.api.com', $webpag->getConfiguration()->getBaseUrl());
    }

    public function testFromEnvironmentReturnsInstance()
    {
        $env = new Environment();
        $env->setApiToken('env-token');

        $webpag = WebPag::fromEnvironment($env);

        $this->assertInstanceOf(WebPag::class, $webpag);
        $this->assertEquals('env-token', $webpag->getConfiguration()->getApiToken());
    }

    public function testFromEnvironmentWithEmptyTokenThrowsException()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('WEBPAG_API_TOKEN não definido');

        WebPag::fromEnvironment(new Environment());
    }

    public function testConstructorInitializesAllResources()
    {
        $config = new Configuration('token');
        $webpag = new WebPag($config);

        $this->assertInstanceOf(Installments::class, $webpag->installments);
        $this->assertInstanceOf(Business::class, $webpag->business);
        $this->assertInstanceOf(PaymentLinks::class, $webpag->paymentLinks);
        $this->assertInstanceOf(Payers::class, $webpag->payers);
        $this->assertInstanceOf(Payments::class, $webpag->payments);
        $this->assertInstanceOf(Recurrency::class, $webpag->recurrency);
        $this->assertInstanceOf(Transfers::class, $webpag->transfers);
        $this->assertInstanceOf(WebhookParser::class, $webpag->webhooks);
    }

    public function testConstructorAcceptsCustomHttpClient()
    {
        $config = new Configuration('token');
        $client = new HttpClient($config);
        $webpag = new WebPag($config, $client);

        $this->assertSame($client, $webpag->getHttpClient());
    }

    public function testGetConfiguration()
    {
        $config = new Configuration('token');
        $webpag = new WebPag($config);

        $this->assertSame($config, $webpag->getConfiguration());
    }

    public function testGetHttpClient()
    {
        $webpag = WebPag::create('token');

        $this->assertInstanceOf(HttpClient::class, $webpag->getHttpClient());
    }
}
