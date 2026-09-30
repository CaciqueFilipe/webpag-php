<?php

namespace WebPag\Responses\Payments;

use WebPag\Contracts\ResponsePayload;
use WebPag\Support\ArrayHelper;

class CreditSchedule implements ResponsePayload
{
    /** @var int|null */
    public $id;

    /** @var int|null (em centavos) */
    public $amount;

    /** @var string|null (ISO 8601) */
    public $dateScheduled;

    /** @var bool|null */
    public $credited;

    /** @var int|null */
    public $status;

    /** @var int|null */
    public $transferId;

    /** @var string|null (ISO 8601) */
    public $transferredAt;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $instance = new self();
        $instance->id = isset($data['id']) ? (int) $data['id'] : null;
        $instance->amount = isset($data['amount']) ? (int) $data['amount'] : null;
        $instance->dateScheduled = $data['date_scheduled'] ?? null;
        $instance->credited = isset($data['credited']) ? (bool) $data['credited'] : null;
        $instance->status = isset($data['status']) ? (int) $data['status'] : null;
        $instance->transferId = isset($data['transfer_id']) ? (int) $data['transfer_id'] : null;
        $instance->transferredAt = $data['transferred_at'] ?? null;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'amount' => $this->amount,
            'date_scheduled' => $this->dateScheduled,
            'credited' => $this->credited,
            'status' => $this->status,
            'transfer_id' => $this->transferId,
            'transferred_at' => $this->transferredAt,
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
        }, ArrayHelper::onlyArrays($collection));
    }
}
