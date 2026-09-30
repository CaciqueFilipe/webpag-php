<?php

namespace WebPag\Responses\Payments;

use WebPag\Contracts\ResponsePayload;

class Pix implements ResponsePayload
{
    /** @var int|null */
    public $id;

    /** @var string|null */
    public $uuid;

    /** @var int|null */
    public $businessId;

    /** @var int|null */
    public $paymentId;

    /** @var string|null */
    public $name;

    /** @var string|null */
    public $txid;

    /** @var string|null */
    public $key;

    /** @var string|null */
    public $qrcodeData;

    /** @var float|null (em reais, ex: 2.55) */
    public $amount;

    /** @var string|null (ISO 8601) */
    public $expirationDate;

    /** @var int|null */
    public $status;

    /** @var string|null (ISO 8601) */
    public $createdAt;

    /** @var string|null (ISO 8601) */
    public $updatedAt;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $instance = new self();
        $instance->id = isset($data['id']) ? (int)$data['id'] : null;
        $instance->uuid = $data['uuid'] ?? null;
        $instance->businessId = isset($data['business_id']) ? (int)$data['business_id'] : null;
        $instance->paymentId = isset($data['payment_id']) ? (int)$data['payment_id'] : null;
        $instance->name = $data['name'] ?? null;
        $instance->txid = $data['txid'] ?? null;
        $instance->key = $data['key'] ?? null;
        $instance->qrcodeData = $data['qrcode_data'] ?? null;
        $instance->amount = isset($data['amount']) ? (float)$data['amount'] : null;
        $instance->expirationDate = $data['expiration_date'] ?? null;
        $instance->status = isset($data['status']) ? (int)$data['status'] : null;
        $instance->createdAt = $data['created_at'] ?? null;
        $instance->updatedAt = $data['updated_at'] ?? null;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'uuid' => $this->uuid,
            'business_id' => $this->businessId,
            'payment_id' => $this->paymentId,
            'name' => $this->name,
            'txid' => $this->txid,
            'key' => $this->key,
            'qrcode_data' => $this->qrcodeData,
            'amount' => $this->amount,
            'expiration_date' => $this->expirationDate,
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ], function ($value) {
            return $value !== null;
        });
    }
}
