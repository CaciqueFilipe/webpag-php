<?php

namespace WebPag\Requests\Payments;

use WebPag\Contracts\RequestPayload;
use WebPag\Support\ArrayHelper;

class CreditCardData implements RequestPayload
{
    /** @var string|null */
    public $number;

    /** @var string|null */
    public $name;

    /** @var string|null */
    public $expirationMonth;

    /** @var string|null */
    public $expirationYear;

    /** @var string|null */
    public $securityCode;

    /**
     * @return array<string, mixed>
     */
    public function toArray()
    {
        return ArrayHelper::filterNull([
            'number' => $this->number,
            'name' => $this->name,
            'expiration_month' => $this->expirationMonth,
            'expiration_year' => $this->expirationYear,
            'security_code' => $this->securityCode,
        ]);
    }

    /**
     * Cria uma instância a partir de um array associativo.
     *
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        $request = new self();

        $request->number          = $data['number'] ?? null;
        $request->name            = $data['name'] ?? null;
        $request->expirationMonth = $data['expiration_month'] ?? null;
        $request->expirationYear  = $data['expiration_year'] ?? null;
        $request->securityCode    = $data['security_code'] ?? null;

        return $request;
    }
}

class PaymentSplit implements RequestPayload
{
    /** @var int */
    public $businessId;

    /** @var float|null */
    public $percentage;

    /** @var int|null Valor fixo em centavos */
    public $amount;

    /**
     * @return array<string, mixed>
     */
    public function toArray()
    {
        return ArrayHelper::filterNull([
            'business_id' => $this->businessId,
            'percentage' => $this->percentage,
            'amount' => $this->amount,
        ]);
    }

    /**
     * Cria uma instância a partir de um array associativo.
     *
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        $request = new self();

        $request->businessId = isset($data['business_id'])
            ? (int) $data['business_id']
            : 0;
        $request->percentage = isset($data['percentage'])
            ? (float) $data['percentage']
            : null;
        $request->amount     = isset($data['amount'])
            ? (int) $data['amount']
            : null;

        return $request;
    }
}

class ProcessPaymentRequest implements RequestPayload
{
    /** @var int */
    public $payerId;

    /** @var string */
    public $name;

    /** @var int Valor em centavos */
    public $amount;

    /** @var string credit_card, pix ou bank_slip */
    public $method;

    /** @var int|null */
    public $installments;

    /** @var bool|null */
    public $notifyPayer;

    /** @var int|null */
    public $cardPayerId;

    /** @var string|null */
    public $cardToken;

    /** @var CreditCardData|null */
    public $card;

    /** @var string|null YYYY-MM-DD HH:MM */
    public $dueDate;

    /** @var bool|null */
    public $acceptAfterDueDate;

    /** @var string|null @deprecated */
    public $notificationUrl;

    /** @var string|null */
    public $orderId;

    /** @var string|null */
    public $paymentLinkId;

    /** @var string|null */
    public $softDescriptor;

    /** @var PaymentSplit[]|null */
    public $splits;

    /**
     * @return array<string, mixed>
     */
    public function toArray()
    {
        $data = ArrayHelper::filterNull([
            'payer_id' => $this->payerId,
            'name' => $this->name,
            'amount' => $this->amount,
            'method' => $this->method,
            'installments' => $this->installments,
            'notify_payer' => $this->notifyPayer,
            'card_payer_id' => $this->cardPayerId,
            'card_token' => $this->cardToken,
            'due_date' => $this->dueDate,
            'accept_after_due_date' => $this->acceptAfterDueDate,
            'notification_url' => $this->notificationUrl,
            'order_id' => $this->orderId,
            'payment_link_id' => $this->paymentLinkId,
            'soft_descriptor' => $this->softDescriptor,
        ]);

        if ($this->card !== null) {
            $data['card'] = $this->card->toArray();
        }

        if ($this->splits !== null) {
            $data['splits'] = array_map(function (PaymentSplit $split) {
                return $split->toArray();
            }, $this->splits);
        }

        return $data;
    }

    /**
     * Cria uma instância a partir de um array associativo.
     *
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        $request = new self();

        $request->payerId            = isset($data['payer_id'])
            ? (int) $data['payer_id']
            : 0;
        $request->name               = $data['name'] ?? '';
        $request->amount             = isset($data['amount'])
            ? (int) $data['amount']
            : 0;
        $request->method             = $data['method'] ?? '';
        $request->installments       = isset($data['installments'])
            ? (int) $data['installments']
            : null;
        $request->notifyPayer        = isset($data['notify_payer'])
            ? (bool) $data['notify_payer']
            : null;
        $request->cardPayerId        = isset($data['card_payer_id'])
            ? (int) $data['card_payer_id']
            : null;
        $request->cardToken          = $data['card_token'] ?? null;
        $request->dueDate            = $data['due_date'] ?? null;
        $request->acceptAfterDueDate = isset($data['accept_after_due_date'])
            ? (bool) $data['accept_after_due_date']
            : null;
        $request->notificationUrl    = $data['notification_url'] ?? null;
        $request->orderId            = $data['order_id'] ?? null;
        $request->paymentLinkId      = $data['payment_link_id'] ?? null;
        $request->softDescriptor     = $data['soft_descriptor'] ?? null;

        // Mapeia objeto simples aninhado
        if (isset($data['card']) && is_array($data['card'])) {
            $request->card = CreditCardData::fromArray($data['card']);
        }

        // Mapeia coleção (array) de objetos aninhados
        if (isset($data['splits']) && is_array($data['splits'])) {
            $request->splits = array_map(function (array $splitData) {
                return PaymentSplit::fromArray($splitData);
            }, $data['splits']);
        }

        return $request;
    }
}

class ListPaymentsRequest implements RequestPayload
{
    /** @var int|null */
    public $paymentId;

    /** @var int|null */
    public $payerId;

    /** @var int|null */
    public $page;

    /** @var string|null Y-m-d */
    public $createdAtStart;

    /** @var string|null Y-m-d */
    public $createdAtEnd;

    /** @var string|null Y-m-d */
    public $paidAtStart;

    /** @var string|null Y-m-d */
    public $paidAtEnd;

    /** @var int|null */
    public $status;

    /** @var string|null credit, debit, pix */
    public $method;

    /** @var bool|null */
    public $active;

    /** @var int|null */
    public $recurrenceCode;

    /** @var bool|null */
    public $isRecurrent;

    /** @var string|null */
    public $orderId;

    /** @var string|null */
    public $txid;

    /**
     * @return array<string, mixed>
     */
    public function toArray()
    {
        return ArrayHelper::filterNull([
            'payment_id' => $this->paymentId,
            'payer_id' => $this->payerId,
            'page' => $this->page,
            'created_at_start' => $this->createdAtStart,
            'created_at_end' => $this->createdAtEnd,
            'paid_at_start' => $this->paidAtStart,
            'paid_at_end' => $this->paidAtEnd,
            'status' => $this->status,
            'method' => $this->method,
            'active' => $this->active,
            'recurrence_code' => $this->recurrenceCode,
            'is_recurrent' => $this->isRecurrent,
            'order_id' => $this->orderId,
            'txid' => $this->txid,
        ]);
    }

    /**
     * Cria uma instância a partir de um array associativo.
     *
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        $request = new self();

        $request->paymentId      = isset($data['payment_id'])
            ? (int) $data['payment_id']
            : null;
        $request->payerId        = isset($data['payer_id'])
            ? (int) $data['payer_id']
            : null;
        $request->page           = isset($data['page'])
            ? (int) $data['page']
            : null;
        $request->createdAtStart = $data['created_at_start'] ?? null;
        $request->createdAtEnd   = $data['created_at_end'] ?? null;
        $request->paidAtStart    = $data['paid_at_start'] ?? null;
        $request->paidAtEnd      = $data['paid_at_end'] ?? null;
        $request->status         = isset($data['status'])
            ? (int) $data['status']
            : null;
        $request->method         = $data['method'] ?? null;
        $request->active         = isset($data['active'])
            ? (bool) $data['active']
            : null;
        $request->recurrenceCode = isset($data['recurrence_code'])
            ? (int) $data['recurrence_code']
            : null;
        $request->isRecurrent    = isset($data['is_recurrent'])
            ? (bool) $data['is_recurrent']
            : null;
        $request->orderId        = $data['order_id'] ?? null;
        $request->txid           = $data['txid'] ?? null;

        return $request;
    }
}

class RefundPaymentRequest implements RequestPayload
{
    /** @var int|null Valor do estorno em centavos */
    public $value;

    /**
     * @return array<string, mixed>
     */
    public function toArray()
    {
        return ArrayHelper::filterNull([
            'value' => $this->value,
        ]);
    }

    /**
     * Cria uma instância a partir de um array associativo.
     *
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        $request = new self();
        $request->value = isset($data['value'])
            ? (int) $data['value']
            : null;
        return $request;
    }
}
