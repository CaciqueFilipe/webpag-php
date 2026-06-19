<?php

namespace WebPag\Tests;

use PHPUnit\Framework\TestCase;
use WebPag\Exceptions\WebPagException;
use WebPag\Webhooks\WebhookEvent;
use WebPag\Webhooks\WebhookParser;

class WebhookParserTest extends TestCase
{
    /** @var WebhookParser */
    private $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new WebhookParser();
    }

    public function testParsePaymentWebhookFromString()
    {
        $json = '{"id": 123, "payer_id": 15, "method": "pix", "status": 40, "business": {"id": 5}}';
        $event = $this->parser->parse($json);

        $this->assertInstanceOf(WebhookEvent::class, $event);
        $this->assertEquals(WebhookEvent::TYPE_PAYMENT, $event->getType());
        $this->assertTrue($event->isPayment());
        $this->assertFalse($event->isTransfer());
        $this->assertFalse($event->isRefund());
    }

    public function testParseTransferWebhook()
    {
        $payload = [
            'id' => 456,
            'destination_type' => 10,
            'status' => 40,
            'amount' => 5000,
        ];
        $event = $this->parser->parse($payload);

        $this->assertEquals(WebhookEvent::TYPE_TRANSFER, $event->getType());
        $this->assertTrue($event->isTransfer());
    }

    public function testParseTransferWebhookWithDestinationTypeName()
    {
        $payload = [
            'destination_type_name' => 'PIX',
            'status' => 20,
        ];
        $event = $this->parser->parse($payload);

        $this->assertEquals(WebhookEvent::TYPE_TRANSFER, $event->getType());
    }

    public function testParseRefundWebhook()
    {
        $payload = [
            'refund_id' => 'ref_123',
            'payment_id' => 789,
            'status' => 50,
        ];
        $event = $this->parser->parse($payload);

        $this->assertEquals(WebhookEvent::TYPE_REFUND, $event->getType());
        $this->assertTrue($event->isRefund());
    }

    public function testParseRefundWebhookWithTypeField()
    {
        $payload = [
            'type' => 'refund',
            'id' => 999,
        ];
        $event = $this->parser->parse($payload);

        $this->assertEquals(WebhookEvent::TYPE_REFUND, $event->getType());
    }

    public function testParseUnknownWebhook()
    {
        $payload = ['some_unknown_field' => 'value'];
        $event = $this->parser->parse($payload);

        $this->assertEquals(WebhookEvent::TYPE_UNKNOWN, $event->getType());
        $this->assertFalse($event->isPayment());
        $this->assertFalse($event->isTransfer());
        $this->assertFalse($event->isRefund());
    }

    public function testParseInvalidJsonThrowsException()
    {
        $this->expectException(WebPagException::class);

        $this->parser->parse('not-valid-json-at-all');
    }

    public function testParseInvalidPayloadThrowsException()
    {
        $this->expectException(WebPagException::class);

        $this->parser->parse(null);
    }

    public function testGetPayload()
    {
        $payload = ['id' => 1, 'payer_id' => 5, 'status' => 40];
        $event = $this->parser->parse($payload);

        $this->assertEquals($payload, $event->getPayload());
    }

    public function testGetStatus()
    {
        $payload = ['payer_id' => 1, 'status' => 40];
        $event = $this->parser->parse($payload);

        $this->assertEquals(40, $event->getStatus());
    }

    public function testGetStatusReturnsNullWhenMissing()
    {
        $payload = ['payer_id' => 1];
        $event = $this->parser->parse($payload);

        $this->assertNull($event->getStatus());
    }

    public function testGetBusinessId()
    {
        $payload = ['payer_id' => 1, 'business' => ['id' => 10]];
        $event = $this->parser->parse($payload);

        $this->assertEquals(10, $event->getBusinessId());
    }

    public function testGetBusinessIdReturnsNullWhenMissing()
    {
        $payload = ['payer_id' => 1];
        $event = $this->parser->parse($payload);

        $this->assertNull($event->getBusinessId());
    }

    public function testGetCustomField()
    {
        $payload = ['payer_id' => 1, 'custom_field' => 'custom_value'];
        $event = $this->parser->parse($payload);

        $this->assertEquals('custom_value', $event->get('custom_field'));
        $this->assertNull($event->get('nonexistent'));
        $this->assertEquals('default', $event->get('nonexistent', 'default'));
    }

    public function testVerifySignatureValid()
    {
        $payload = '{"id": 1, "status": 40}';
        $apiToken = 'my-secret-token';
        $expected = hash_hmac('sha256', $payload, $apiToken);

        $this->assertTrue(WebhookParser::verifySignature($payload, $expected, $apiToken));
    }

    public function testVerifySignatureInvalid()
    {
        $payload = '{"id": 1, "status": 40}';

        $this->assertFalse(WebhookParser::verifySignature($payload, 'invalid-signature', 'my-secret-token'));
    }

    public function testVerifySignatureDifferentToken()
    {
        $payload = '{"id": 1, "status": 40}';
        $signature = hash_hmac('sha256', $payload, 'correct-token');

        $this->assertFalse(WebhookParser::verifySignature($payload, $signature, 'wrong-token'));
    }
}
