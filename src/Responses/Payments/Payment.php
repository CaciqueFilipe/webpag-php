<?php

namespace WebPag\Responses\Payments;

use WebPag\Responses\Payers\Payer;
use WebPag\Contracts\ResponsePayload;
use WebPag\Support\ArrayHelper;
use WebPag\Responses\Business\Business;

class Payment implements ResponsePayload
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

    /** @var int|null (em centavos) */
    public $amount;

    /** @var int|null (em centavos) */
    public $amountRefunded;

    /** @var float|null (em reais, ex: 0.36) */
    public $feeValue;

    /** @var int|null (em centavos) */
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

    /** @var Pix|null */
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

    /** @var Transaction[]|null */
    public $transactions;

    /** @var Split[]|null */
    public $splits;

    /** @var Refund[]|null */
    public $refunds;

    /** @var string|null (Y-m-d H:i) */
    public $createdAt;

    /** @var string|null (Y-m-d H:i) */
    public $paidAt;

    /** @var string|null (Y-m-d H:i) */
    public $updatedAt;

    /** @var string|null */
    public $receiptPdfPath;

    /** @var CreditSchedule[]|null */
    public $creditSchedule;

    /** @var int|null */
    public $cardFlag;

    /** @var string|null */
    public $cardFlagLabel;

    /**
     * @param array<string, mixed> $data
     * @return static
     */
    public static function fromArray(array $data): self
    {
        // "static" para que subclasses (ex: Recurrency) recebam a própria instância
        $instance = new static();

        $instance->id = isset($data['id']) ? (int) $data['id'] : null;
        $instance->payerId = isset($data['payer_id']) ? (int) $data['payer_id'] : null;
        $instance->cardId = isset($data['card_id']) ? (int) $data['card_id'] : null;
        $instance->name = $data['name'] ?? null;
        $instance->amount = isset($data['amount']) ? (int) $data['amount'] : null;
        $instance->amountRefunded = isset($data['amount_refunded']) ? (int) $data['amount_refunded'] : null;
        $instance->feeValue = isset($data['fee_value']) ? (float) $data['fee_value'] : null;
        $instance->refundedFee = isset($data['refunded_fee']) ? (int) $data['refunded_fee'] : null;
        $instance->installments = isset($data['installments']) ? (int) $data['installments'] : null;
        $instance->isRecurrent = isset($data['is_recurrent']) ? (bool) $data['is_recurrent'] : null;
        $instance->recurrenceCode = $data['recurrence_code'] ?? null;
        $instance->frequency = $data['frequency'] ?? null;
        $instance->method = isset($data['method']) ? (int) $data['method'] : null;
        $instance->methodLabel = $data['method_label'] ?? null;
        $instance->methodSlug = $data['method_slug'] ?? null;
        $instance->notificationUrl = $data['notification_url'] ?? null;
        $instance->installmentsPaid = isset($data['installments_paid']) ? (int) $data['installments_paid'] : null;
        $instance->spplited = isset($data['spplited']) ? (bool) $data['spplited'] : null;
        $instance->softDescriptor = $data['soft_descriptor'] ?? null;
        $instance->orderId = $data['order_id'] ?? null;
        $instance->active = isset($data['active']) ? (bool) $data['active'] : null;
        $instance->status = isset($data['status']) ? (int) $data['status'] : null;
        $instance->statusLabel = $data['status_label'] ?? null;
        $instance->startDate = $data['start_date'] ?? null;
        $instance->nextDate = $data['next_date'] ?? null;
        $instance->nextRecurrenceDate = $data['next_recurrence_date'] ?? null;
        $instance->createdAt = $data['created_at'] ?? null;
        $instance->paidAt = $data['paid_at'] ?? null;
        $instance->updatedAt = $data['updated_at'] ?? null;
        $instance->receiptPdfPath = $data['receipt_pdf_path'] ?? null;
        $instance->cardFlag = isset($data['card_flag']) ? (int) $data['card_flag'] : null;
        $instance->cardFlagLabel = $data['card_flag_label'] ?? null;

        if (isset($data['business']) && is_array($data['business'])) {
            $instance->business = Business::fromArray($data['business']);
        }

        if (isset($data['transactions']) && is_array($data['transactions'])) {
            $instance->transactions = Transaction::fromArrayCollection($data['transactions']);
        }

        if (isset($data['splits']) && is_array($data['splits'])) {
            $instance->splits = Split::fromArrayCollection($data['splits']);
        }

        if (isset($data['refunds']) && is_array($data['refunds'])) {
            $instance->refunds = Refund::fromArrayCollection($data['refunds']);
        }

        if (isset($data['credit_schedule']) && is_array($data['credit_schedule'])) {
            $instance->creditSchedule = CreditSchedule::fromArrayCollection($data['credit_schedule']);
        }

        if (isset($data['payer']) && is_array($data['payer'])) {
            $instance->payer = Payer::fromArray($data['payer']);
        }

        if (isset($data['boleto']) && is_array($data['boleto'])) {
            $instance->boleto = BankSlip::fromArray($data['boleto']);
        }
    
        if (isset($data['pix']) && is_array($data['pix'])) {
            $instance->pix = Pix::fromArray($data['pix']);
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
            'pix' => $this->pix ? $this->pix->toArray() : null,
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
            'transactions' => self::collectionToArray($this->transactions),
            'splits' => self::collectionToArray($this->splits),
            'refunds' => self::collectionToArray($this->refunds),
            'created_at' => $this->createdAt,
            'paid_at' => $this->paidAt,
            'updated_at' => $this->updatedAt,
            'receipt_pdf_path' => $this->receiptPdfPath,
            'credit_schedule' => self::collectionToArray($this->creditSchedule),
            'card_flag' => $this->cardFlag,
            'card_flag_label' => $this->cardFlagLabel,
        ], function ($value) {
            return $value !== null;
        });
    }

    /**
     * @param ResponsePayload[]|null $items
     * @return array<array<string, mixed>>|null
     */
    private static function collectionToArray($items)
    {
        if ($items === null) {
            return null;
        }

        return array_map(function (ResponsePayload $item) {
            return $item->toArray();
        }, $items);
    }

    /**
     * Cria uma coleção de instâncias a partir de um array de dados da API.
     *
     * @param array<array<string, mixed>> $collection
     * @return static[]
     */
    public static function fromArrayCollection(array $collection): array
    {
        return array_map(function ($data) {
            return static::fromArray($data);
        }, ArrayHelper::onlyArrays($collection));
    }
}
