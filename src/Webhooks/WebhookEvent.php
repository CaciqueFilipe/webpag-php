<?php

namespace WebPag\Webhooks;

use WebPag\Contracts\ResponsePayload;

class WebhookEvent
{
    public const TYPE_PAYMENT = 'payment';
    public const TYPE_TRANSFER = 'transfer';
    public const TYPE_REFUND = 'refund';
    public const TYPE_UNKNOWN = 'unknown';
    public const TYPE_RECURRENCY = 'recurrency';

    /** @var string */
    private $type;

    /** @var ResponsePayload */
    private $payload;

    /**
     * @param string          $type
     * @param ResponsePayload $payload
     */
    public function __construct(string $type, ResponsePayload $payload)
    {
        $this->type = $type;
        $this->payload = $payload;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return ResponsePayload
     */
    public function getPayload(): ResponsePayload
    {
        return $this->payload;
    }

    /**
     * @return bool
     */
    public function isPayment()
    {
        return $this->type === self::TYPE_PAYMENT;
    }

    /**
     * @return bool
     */
    public function isTransfer()
    {
        return $this->type === self::TYPE_TRANSFER;
    }

    /**
     * @return bool
     */
    public function isRefund()
    {
        return $this->type === self::TYPE_REFUND;
    }

    /**
     * @return bool
     */
    public function isRecurrency()
    {
        return $this->type === self::TYPE_RECURRENCY;
    }
}
