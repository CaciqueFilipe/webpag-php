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

    /** @var string|null (Y-m-d H:i:s) */
    public $createdAt;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data)
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
        $instance->createdAt = $data['created_at'] ?? null;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return array_filter(get_object_vars($this), function ($value) {
            return $value !== null;
        });
    }
}
