<?php

namespace WebPag\Requests\Recurrency;

use WebPag\Contracts\RequestPayload;
use WebPag\Requests\Payments\CreditCardData;
use WebPag\Support\ArrayHelper;

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
}

class ListRecurrencyRequest implements RequestPayload
{
    /** @var int|null */
    public $payerId;

    /** @var string|null */
    public $recurrenceCode;

    /** @var int|null */
    public $page;

    /**
     * @return array<string, mixed>
     */
    public function toArray()
    {
        return ArrayHelper::filterNull([
            'payer_id' => $this->payerId,
            'recurrence_code' => $this->recurrenceCode,
            'page' => $this->page,
        ]);
    }
}

class UpdateRecurrencyRequest implements RequestPayload
{
    /** @var string|null */
    public $name;

    /** @var int|null */
    public $amount;

    /** @var int|null */
    public $installments;

    /** @var string|null */
    public $frequency;

    /** @var int|null */
    public $cardPayerId;

    /** @var string|null Y-m-d H:i */
    public $nextDate;

    /**
     * @return array<string, mixed>
     */
    public function toArray()
    {
        return ArrayHelper::filterNull([
            'name' => $this->name,
            'amount' => $this->amount,
            'installments' => $this->installments,
            'frequency' => $this->frequency,
            'card_payer_id' => $this->cardPayerId,
            'next_date' => $this->nextDate,
        ]);
    }
}
