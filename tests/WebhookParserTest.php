<?php

namespace WebPag\Tests;

use PHPUnit\Framework\TestCase;
use WebPag\Enums\PaymentStatus;
use WebPag\Exceptions\WebPagException;
use WebPag\Responses\Payments\Payment;
use WebPag\Responses\Payments\Refund;
use WebPag\Responses\Transfers\Transfer;
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
        $this->assertInstanceOf(Payment::class, $event->getPayload());
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
            'status' => PaymentStatus::PAID,
            'amount' => 5000,
        ];
        $event = $this->parser->parse($payload);

        $this->assertEquals(WebhookEvent::TYPE_TRANSFER, $event->getType());
        $this->assertInstanceOf(Transfer::class, $event->getPayload());
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
        $this->assertInstanceOf(Transfer::class, $event->getPayload());
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
        $this->assertInstanceOf(Refund::class, $event->getPayload());
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
        $this->assertInstanceOf(Refund::class, $event->getPayload());
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
        $payload = ['id' => 1, 'payer_id' => 5, 'status' => PaymentStatus::PAID];
        $event = $this->parser->parse($payload);

        $this->assertInstanceOf(Payment::class, $event->getPayload());
        $this->assertEquals($payload, $event->getPayload()->toArray());
    }

    public function testGetStatus()
    {
        $payload = ['payer_id' => 1, 'status' => PaymentStatus::PAID];
        $event = $this->parser->parse($payload);
        /** @var Payment $payment */
        $payment = $event->getPayload();

        $this->assertEquals(PaymentStatus::PAID, $payment->status);
    }

    public function testGetStatusReturnsNullWhenMissing()
    {
        $payload = ['payer_id' => 1];
        $event = $this->parser->parse($payload);
        /** @var Payment $payment */
        $payment = $event->getPayload();

        $this->assertNull($payment->status);
    }

    public function testGetBusinessId()
    {
        $payload = ['payer_id' => 1, 'business' => ['id' => 10]];
        $event = $this->parser->parse($payload);
        /** @var Payment $payment */
        $payment = $event->getPayload();

        $this->assertNotNull($payment->business);
        $this->assertEquals(10, $payment->business->id);
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
