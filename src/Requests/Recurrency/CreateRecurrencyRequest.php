<?php

namespace WebPag\Requests\Recurrency;

use WebPag\Support\ArrayHelper;
use WebPag\Contracts\RequestPayload;
use WebPag\Requests\Payments\CreditCardData;

class CreateRecurrencyRequest implements RequestPayload
{
    /** @var int */
    public $payerId;

    /** @var string */
    public $name;

    /** @var string monthly, bimonthly, quarterly, semiannual, yearly */
    public $frequency;

    /** @var string Y-m-d H:i */
    public $startDate;

    /** @var int Valor em centavos */
    public $amount;

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

    /**
     * @return array<string, mixed>
     */
    public function toArray()
    {
        $data = ArrayHelper::filterNull([
            'payer_id' => $this->payerId,
            'name' => $this->name,
            'frequency' => $this->frequency,
            'start_date' => $this->startDate,
            'amount' => $this->amount,
            'installments' => $this->installments,
            'notify_payer' => $this->notifyPayer,
            'card_payer_id' => $this->cardPayerId,
            'card_token' => $this->cardToken,
        ]);

        if ($this->card !== null) {
            $data['card'] = $this->card->toArray();
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

        $request->payerId      = isset($data['payer_id'])
            ? (int) $data['payer_id']
            : 0;
        $request->name         = $data['name'] ?? '';
        $request->frequency    = $data['frequency'] ?? '';
        $request->startDate    = $data['start_date'] ?? '';
        $request->amount       = isset($data['amount'])
            ? (int) $data['amount']
            : 0;
        $request->installments = isset($data['installments'])
            ? (int) $data['installments']
            : null;
        $request->notifyPayer  = isset($data['notify_payer'])
            ? (bool) $data['notify_payer']
            : null;
        $request->cardPayerId  = isset($data['card_payer_id'])
            ? (int) $data['card_payer_id']
            : null;
        $request->cardToken    = $data['card_token'] ?? null;

        if (isset($data['card']) && is_array($data['card'])) {
            $request->card = CreditCardData::fromArray($data['card']);
        }

        return $request;
    }
}
