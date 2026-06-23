<?php

namespace WebPag\Responses\Recurrency;

use WebPag\Contracts\ResponsePayload;
use WebPag\Responses\Business\Business;
use WebPag\Responses\Payers\Payer;
use WebPag\Responses\Payments\BankSlip;
use WebPag\Responses\Payments\Refund;

class Recurrency implements ResponsePayload
{
    /** @var int|null */
    public $id;

    /** @var Business|null */
    public $business;

    /** @var int|null */
    public $payerId;

    /** @var int|null */
    public $cardId;

    /** @var Payer|null */
    public $payer;

    /** @var string|null */
    public $name;

    /** @var int|null */
    public $amount;

    /** @var int|null */
    public $amountRefunded;

    /** @var float|null */
    public $feeValue;

    /** @var int|null */
    public $refundedFee;

    /** @var int|null */
    public $installments;

    /** @var bool|null */
    public $isRecurrent;

    /** @var string|null */
    public $recurrenceCode;

    /** @var string|null */
    public $frequency;

    /** @var int|null */
    public $method;

    /** @var string|null */
    public $methodLabel;

    /** @var string|null */
    public $methodSlug;

    /** @var BankSlip|null */
    public $boleto;

    /** @var string|null */
    public $notificationUrl;

    /** @var array<string, mixed>|null */
    public $pix;

    /** @var int|null */
    public $installmentsPaid;

    /** @var bool|null */
    public $spplited;

    /** @var string|null */
    public $softDescriptor;

    /** @var string|null */
    public $orderId;

    /** @var bool|null */
    public $active;

    /** @var int|null */
    public $status;

    /** @var string|null */
    public $statusLabel;

    /** @var string|null */
    public $startDate;

    /** @var string|null */
    public $nextDate;

    /** @var string|null */
    public $nextRecurrenceDate;

    /** @var Refund[]|null */
    public $refunds;

    /** @var string|null */
    public $createdAt;

    /** @var string|null */
    public $paidAt;

    /** @var string|null */
    public $updatedAt;

    /** @var string|null */
    public $receiptPdfPath;

    /** @var string|null */
    public $cardFlag;

    /** @var string|null */
    public $cardFlagLabel;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $instance = new self();

        $instance->id = isset($data['id']) ? (int)$data['id'] : null;
        $instance->payerId = isset($data['payer_id']) ? (int)$data['payer_id'] : null;
        $instance->cardId = isset($data['card_id']) ? (int)$data['card_id'] : null;
        $instance->name = $data['name'] ?? null;
        $instance->amount = isset($data['amount']) ? (int)$data['amount'] : null;
        $instance->amountRefunded = isset($data['amount_refunded']) ? (int)$data['amount_refunded'] : null;
        $instance->feeValue = isset($data['fee_value']) ? (float)$data['fee_value'] : null;
        $instance->refundedFee = isset($data['refunded_fee']) ? (int)$data['refunded_fee'] : null;
        $instance->installments = isset($data['installments']) ? (int)$data['installments'] : null;
        $instance->isRecurrent = isset($data['is_recurrent']) ? (bool)$data['is_recurrent'] : null;
        $instance->recurrenceCode = $data['recurrence_code'] ?? null;
        $instance->frequency = $data['frequency'] ?? null;
        $instance->method = isset($data['method']) ? (int)$data['method'] : null;
        $instance->methodLabel = $data['method_label'] ?? null;
        $instance->methodSlug = $data['method_slug'] ?? null;
        $instance->notificationUrl = $data['notification_url'] ?? null;
        $instance->pix = $data['pix'] ?? null;
        $instance->installmentsPaid = isset($data['installments_paid']) ? (int)$data['installments_paid'] : null;
        $instance->spplited = isset($data['spplited']) ? (bool)$data['spplited'] : null;
        $instance->softDescriptor = $data['soft_descriptor'] ?? null;
        $instance->orderId = $data['order_id'] ?? null;
        $instance->active = isset($data['active']) ? (bool)$data['active'] : null;
        $instance->status = isset($data['status']) ? (int)$data['status'] : null;
        $instance->statusLabel = $data['status_label'] ?? null;
        $instance->startDate = $data['start_date'] ?? null;
        $instance->nextDate = $data['next_date'] ?? null;
        $instance->nextRecurrenceDate = $data['next_recurrence_date'] ?? null;
        $instance->createdAt = $data['created_at'] ?? null;
        $instance->paidAt = $data['paid_at'] ?? null;
        $instance->updatedAt = $data['updated_at'] ?? null;
        $instance->receiptPdfPath = $data['receipt_pdf_path'] ?? null;
        $instance->cardFlag = $data['card_flag'] ?? null;
        $instance->cardFlagLabel = $data['card_flag_label'] ?? null;

        if (isset($data['business']) && is_array($data['business'])) {
            $instance->business = Business::fromArray($data['business']);
        }
        if (isset($data['payer']) && is_array($data['payer'])) {
            $instance->payer = Payer::fromArray($data['payer']);
        }
        if (isset($data['boleto']) && is_array($data['boleto'])) {
            $instance->boleto = BankSlip::fromArray($data['boleto']);
        }
        if (isset($data['refunds']) && is_array($data['refunds'])) {
            $instance->refunds = Refund::fromArrayCollection($data['refunds']);
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
            'business' => $this->business ? $this->business->toArray() : null,
            'payer_id' => $this->payerId,
            'card_id' => $this->cardId,
            'payer' => $this->payer ? $this->payer->toArray() : null,
            'name' => $this->name,
            'amount' => $this->amount,
            'amount_refunded' => $this->amountRefunded,
            'fee_value' => $this->feeValue,
            'refunded_fee' => $this->refundedFee,
            'installments' => $this->installments,
            'is_recurrent' => $this->isRecurrent,
            'recurrence_code' => $this->recurrenceCode,
            'frequency' => $this->frequency,
            'method' => $this->method,
            'method_label' => $this->methodLabel,
            'method_slug' => $this->methodSlug,
            'boleto' => $this->boleto ? $this->boleto->toArray() : null,
            'notification_url' => $this->notificationUrl,
            'pix' => $this->pix,
            'installments_paid' => $this->installmentsPaid,
            'spplited' => $this->spplited,
            'soft_descriptor' => $this->softDescriptor,
            'order_id' => $this->orderId,
            'active' => $this->active,
            'status' => $this->status,
            'status_label' => $this->statusLabel,
            'start_date' => $this->startDate,
            'next_date' => $this->nextDate,
            'next_recurrence_date' => $this->nextRecurrenceDate,
            'refunds' => $this->refunds ? array_map(fn ($refund) => $refund->toArray(), $this->refunds) : null,
            'created_at' => $this->createdAt,
            'paid_at' => $this->paidAt,
            'updated_at' => $this->updatedAt,
            'receipt_pdf_path' => $this->receiptPdfPath,
            'card_flag' => $this->cardFlag,
            'card_flag_label' => $this->cardFlagLabel,
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
