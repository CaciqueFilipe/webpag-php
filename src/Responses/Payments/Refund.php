<?php

namespace WebPag\Responses\Payments;

use WebPag\Contracts\ResponsePayload;

class Refund implements ResponsePayload
{
    /** @var string|null */
    public $id;

    /** @var int|null */
    public $paymentId;

    /** @var int|null */
    public $businessId;

    /** @var int|null */
    public $status;

    /** @var string|null */
    public $statusLabel;

    /** @var int|null */
    public $refundAmount;

    /** @var int|null */
    public $refundFee;

    /** @var int|null */
    public $beforeAmount;

    /** @var int|null */
    public $beforeFee;

    /** @var int|null */
    public $afterAmount;

    /** @var int|null */
    public $afterFee;

    /** @var int|null */
    public $creditScheduleRefund;

    /** @var int|null */
    public $financialEntryRefund;

    /** @var string|null */
    public $refundReceiptUrl;

    /** @var string|null */
    public $processedAt;

    /** @var string|null */
    public $createdAt;

    /** @var string|null */
    public $updatedAt;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $instance = new self();
        $instance->id = $data['id'] ?? null;
        $instance->paymentId = isset($data['payment_id']) ? (int)$data['payment_id'] : null;
        $instance->businessId = isset($data['business_id']) ? (int)$data['business_id'] : null;
        $instance->status = isset($data['status']) ? (int)$data['status'] : null;
        $instance->statusLabel = $data['status_label'] ?? null;
        $instance->refundAmount = isset($data['refund_amount']) ? (int)$data['refund_amount'] : null;
        $instance->refundFee = isset($data['refund_fee']) ? (int)$data['refund_fee'] : null;
        $instance->beforeAmount = isset($data['before_amount']) ? (int)$data['before_amount'] : null;
        $instance->beforeFee = isset($data['before_fee']) ? (int)$data['before_fee'] : null;
        $instance->afterAmount = isset($data['after_amount']) ? (int)$data['after_amount'] : null;
        $instance->afterFee = isset($data['after_fee']) ? (int)$data['after_fee'] : null;
        $instance->creditScheduleRefund = isset($data['credit_schedule_refund']) ? (int)$data['credit_schedule_refund'] : null;
        $instance->financialEntryRefund = isset($data['financial_entry_refund']) ? (int)$data['financial_entry_refund'] : null;
        $instance->refundReceiptUrl = $data['refund_receipt_url'] ?? null;
        $instance->processedAt = $data['processed_at'] ?? null;
        $instance->createdAt = $data['created_at'] ?? null;
        $instance->updatedAt = $data['updated_at'] ?? null;

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
