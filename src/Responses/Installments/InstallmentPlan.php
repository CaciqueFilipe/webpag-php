<?php

namespace WebPag\Responses\Installments;

use WebPag\Contracts\ResponsePayload;
use WebPag\Responses\Payers\Payer;

class InstallmentPlan implements ResponsePayload
{
    /** @var int|null */
    public $id;

    /** @var string|null */
    public $name;

    /** @var int|null */
    public $businessId;

    /** @var int|null */
    public $payerId;

    /** @var int|null */
    public $numberInstallments;

    /** @var float|null */
    public $amountOriginal;

    /** @var string|null */
    public $firstDate;

    /** @var int|null */
    public $type;

    /** @var string|null */
    public $typeLabel;

    /** @var int|null */
    public $status;

    /** @var string|null */
    public $statusLabel;

    /** @var bool|null */
    public $acceptAfterDueDate;

    /** @var string|null */
    public $createdAt;

    /** @var string|null */
    public $updatedAt;

    /** @var Payer|null */
    public $payer;

    /** @var Installment[]|null */
    public $installments;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $instance = new self();

        $instance->id = isset($data['id']) ? (int) $data['id'] : null;
        $instance->name = $data['name'] ?? null;
        $instance->businessId = isset($data['business_id']) ? (int) $data['business_id'] : null;
        $instance->payerId = isset($data['payer_id']) ? (int) $data['payer_id'] : null;
        $instance->numberInstallments = isset($data['number_installments']) ? (int) $data['number_installments'] : null;
        $instance->amountOriginal = isset($data['amount_original']) ? (float) $data['amount_original'] : null;
        $instance->firstDate = $data['first_date'] ?? null;
        $instance->type = isset($data['type']) ? (int) $data['type'] : null;
        $instance->typeLabel = $data['type_label'] ?? null;
        $instance->status = isset($data['status']) ? (int) $data['status'] : null;
        $instance->statusLabel = $data['status_label'] ?? null;
        $instance->acceptAfterDueDate = isset($data['accept_after_due_date']) ? (bool) $data['accept_after_due_date'] : null;
        $instance->createdAt = $data['created_at'] ?? null;
        $instance->updatedAt = $data['updated_at'] ?? null;

        if (isset($data['payer']) && is_array($data['payer'])) {
            $instance->payer = Payer::fromArray($data['payer']);
        }

        if (isset($data['installments']) && is_array($data['installments'])) {
            $instance->installments = array_map(function ($installmentData) {
                return Installment::fromArray($installmentData);
            }, $data['installments']);
        }

        return $instance;
    }

    /**
     * @param array<array<string, mixed>> $data
     * @return self[]
     */
    public static function fromArrayCollection(array $data): array
    {
        return array_map(function ($item) {
            return self::fromArray($item);
        }, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
            'business_id' => $this->businessId,
            'payer_id' => $this->payerId,
            'number_installments' => $this->numberInstallments,
            'amount_original' => $this->amountOriginal,
            'first_date' => $this->firstDate,
            'type' => $this->type,
            'type_label' => $this->typeLabel,
            'status' => $this->status,
            'status_label' => $this->statusLabel,
            'accept_after_due_date' => $this->acceptAfterDueDate,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'payer' => $this->payer ? $this->payer->toArray() : null,
            'installments' => $this->installments ? array_map(function (Installment $installment) {
                return $installment->toArray();
            }, $this->installments) : null,
        ], function ($value) {
            return $value !== null;
        });
    }
}
