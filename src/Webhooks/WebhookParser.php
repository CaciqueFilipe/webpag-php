<?php

namespace WebPag\Webhooks;

use WebPag\Exceptions\WebPagException;

class WebhookEvent
{
    const TYPE_PAYMENT = 'payment';
    const TYPE_TRANSFER = 'transfer';
    const TYPE_REFUND = 'refund';
    const TYPE_UNKNOWN = 'unknown';

    /** @var string */
    private $type;

    /** @var array<string, mixed> */
    private $payload;

    /**
     * @param string               $type
     * @param array<string, mixed> $payload
     */
    public function __construct($type, array $payload)
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
     * @return array<string, mixed>
     */
    public function getPayload()
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
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return array_key_exists($key, $this->payload) ? $this->payload[$key] : $default;
    }

    /**
     * @return int|null
     */
    public function getStatus()
    {
        return isset($this->payload['status']) ? (int) $this->payload['status'] : null;
    }

    /**
     * @return int|null
     */
    public function getBusinessId()
    {
        if (isset($this->payload['business']['id'])) {
            return (int) $this->payload['business']['id'];
        }

        return null;
    }
}

class WebhookParser
{
    /**
     * Interpreta o payload recebido via webhook da WebPag.
     *
     * @param string|array<string, mixed> $payload JSON bruto ou array decodificado
     *
     * @return WebhookEvent
     *
     * @throws WebPagException
     */
    public function parse($payload)
    {
        if (is_string($payload)) {
            $decoded = json_decode($payload, true);

            if (!is_array($decoded)) {
                throw new WebPagException('Payload de webhook inválido: JSON malformado.');
            }

            $payload = $decoded;
        }

        if (!is_array($payload)) {
            throw new WebPagException('Payload de webhook inválido.');
        }

        return new WebhookEvent($this->detectType($payload), $payload);
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return string
     */
    private function detectType(array $payload)
    {
        if (isset($payload['destination_type']) || isset($payload['destination_type_name'])) {
            return WebhookEvent::TYPE_TRANSFER;
        }

        if (isset($payload['refund_id']) || (isset($payload['type']) && $payload['type'] === 'refund')) {
            return WebhookEvent::TYPE_REFUND;
        }

        if (isset($payload['payer_id']) || isset($payload['method']) || isset($payload['method_label'])) {
            return WebhookEvent::TYPE_PAYMENT;
        }

        return WebhookEvent::TYPE_UNKNOWN;
    }
}
