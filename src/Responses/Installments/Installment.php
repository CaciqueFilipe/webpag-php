<?php

namespace WebPag\Responses\Installments;

use WebPag\Contracts\ResponsePayload;
use WebPag\Responses\Payments\Payment;

class Installment implements ResponsePayload
{
    /** @var int|null */
    public $id;

    /** @var int|null */
    public $installmentPlanId;

    /** @var int|null */
    public $paymentId;

    /** @var string|null */
    public $code;

    /** @var string|null */
    public $dueDate;

    /** @var string|null */
    public $liquidationDate;

    /** @var mixed|null */
    public $paymentInfo;

    /** @var int|null */
    public $number;

    /** @var float|null */
    public $amount;

    /** @var int|null */
    public $status;

    /** @var string|null */
    public $statusLabel;

    /** @var Payment|null */
    public $payment;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $instance = new self();

        $instance->id = isset($data['id']) ? (int) $data['id'] : null;
        $instance->installmentPlanId = isset($data['installment_plan_id']) ? (int) $data['installment_plan_id'] : null;
        $instance->paymentId = isset($data['payment_id']) ? (int) $data['payment_id'] : null;
        $instance->code = $data['code'] ?? null;
        $instance->dueDate = $data['due_date'] ?? null;
        $instance->liquidationDate = $data['liquidation_date'] ?? null;
        $instance->paymentInfo = $data['payment_info'] ?? null;
        $instance->number = isset($data['number']) ? (int) $data['number'] : null;
        $instance->amount = isset($data['amount']) ? (float) $data['amount'] : null;
        $instance->status = isset($data['status']) ? (int) $data['status'] : null;
        $instance->statusLabel = $data['status_label'] ?? null;

        if (isset($data['payment']) && is_array($data['payment'])) {
            $instance->payment = Payment::fromArray($data['payment']);
        }

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'installment_plan_id' => $this->installmentPlanId,
            'payment_id' => $this->paymentId,
            'code' => $this->code,
            'due_date' => $this->dueDate,
            'liquidation_date' => $this->liquidationDate,
            'payment_info' => $this->paymentInfo,
            'number' => $this->number,
            'amount' => $this->amount,
            'status' => $this->status,
            'status_label' => $this->statusLabel,
            'payment' => $this->payment ? $this->payment->toArray() : null,
        ], function ($value) {
            return $value !== null;
        });
    }
}
