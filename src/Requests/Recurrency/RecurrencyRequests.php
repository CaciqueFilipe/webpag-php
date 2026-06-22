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

class ListRecurrencyRequest implements RequestPayload
{
    /** @var int|null */
    public $payerId;

    /** @var string|null */
    public $recurrenceCode;

    /** @var int|null */
    public $page;

    /** @var int|null */
    public $perPage;

    /**
     * @return array<string, mixed>
     */
    public function toArray()
    {
        return ArrayHelper::filterNull([
            'payer_id' => $this->payerId,
            'recurrence_code' => $this->recurrenceCode,
            'page' => $this->page,
            'per_page' => $this->perPage,
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

        $request->payerId        = isset($data['payer_id'])
            ? (int) $data['payer_id']
            : null;
        $request->recurrenceCode = $data['recurrence_code'] ?? null;
        $request->page           = isset($data['page'])
            ? (int) $data['page']
            : null;
        $request->perPage = isset($data['per_page'])
            ? (int) $data['per_page']
            : null;

        return $request;
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

    /**
     * Cria uma instância a partir de um array associativo.
     *
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        $request = new self();

        $request->name         = $data['name'] ?? null;
        $request->amount       = isset($data['amount'])
            ? (int) $data['amount']
            : null;
        $request->installments = isset($data['installments'])
            ? (int) $data['installments']
            : null;
        $request->frequency    = $data['frequency'] ?? null;
        $request->cardPayerId  = isset($data['card_payer_id'])
            ? (int) $data['card_payer_id']
            : null;
        $request->nextDate     = $data['next_date'] ?? null;

        return $request;
    }
}
