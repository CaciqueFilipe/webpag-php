<?php

namespace WebPag\Requests\Payments;

use WebPag\Support\ArrayHelper;
use WebPag\Contracts\RequestPayload;

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
