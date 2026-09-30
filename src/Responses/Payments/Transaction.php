<?php

namespace WebPag\Responses\Payments;

use WebPag\Contracts\ResponsePayload;

class Transaction implements ResponsePayload
{
    /** @var int|null */
    public $id;

    /** @var int|null */
    public $type;

    /** @var string|null */
    public $typeLabel;

    /** @var string|null */
    public $transactionId;

    /** @var string|null */
    public $responseStatus;

    /** @var mixed|null */
    public $errors;

    /** @var int|null */
    public $status;

    /** @var string|null */
    public $statusLabel;

    /** @var int|null (em centavos) */
    public $refundedAmount;

    /** @var string|null (Y-m-d H:i) */
    public $createdAt;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $instance = new self();
        $instance->id = isset($data['id']) ? (int)$data['id'] : null;
        $instance->type = isset($data['type']) ? (int)$data['type'] : null;
        $instance->typeLabel = $data['type_label'] ?? null;
        $instance->transactionId = $data['transaction_id'] ?? null;
        $instance->responseStatus = $data['response_status'] ?? null;
        $instance->errors = $data['errors'] ?? null;
        $instance->status = isset($data['status']) ? (int)$data['status'] : null;
        $instance->statusLabel = $data['status_label'] ?? null;
        $instance->refundedAmount = isset($data['refunded_amount']) ? (int)$data['refunded_amount'] : null;
        $instance->createdAt = $data['created_at'] ?? null;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'type' => $this->type,
            'type_label' => $this->typeLabel,
            'transaction_id' => $this->transactionId,
            'response_status' => $this->responseStatus,
            'errors' => $this->errors,
            'status' => $this->status,
            'status_label' => $this->statusLabel,
            'refunded_amount' => $this->refundedAmount,
            'created_at' => $this->createdAt,
        ], function ($value) {
            return $value !== null;
        });
    }

    /**
     * Cria uma coleção de instâncias a partir de um array de dados da API.
     *
     * @param array<array<string, mixed>> $collection
     * @return self[]
     */
    public static function fromArrayCollection(array $collection): array
    {
        return array_map(function ($data) {
            return self::fromArray($data);
        }, $collection);
    }
}
