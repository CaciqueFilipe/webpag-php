<?php

namespace WebPag\Responses\Payments;

use WebPag\Contracts\ResponsePayload;
use WebPag\Support\ArrayHelper;

class Split implements ResponsePayload
{
    /** @var int|null */
    public $businessDestinationId;

    /** @var string|null */
    public $businessDestinationName;

    /** @var float|null */
    public $value;

    /** @var float|null */
    public $percentage;

    /** @var int|null (em centavos) */
    public $amount;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $instance = new self();
        $instance->businessDestinationId = isset($data['business_destination_id']) ? (int) $data['business_destination_id'] : null;
        $instance->businessDestinationName = $data['business_destination_name'] ?? null;
        $instance->value = isset($data['value']) ? (float) $data['value'] : null;
        $instance->percentage = isset($data['percentage']) ? (float) $data['percentage'] : null;
        $instance->amount = isset($data['amount']) ? (int) $data['amount'] : null;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return array_filter([
            'business_destination_id' => $this->businessDestinationId,
            'business_destination_name' => $this->businessDestinationName,
            'value' => $this->value,
            'percentage' => $this->percentage,
            'amount' => $this->amount,
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
