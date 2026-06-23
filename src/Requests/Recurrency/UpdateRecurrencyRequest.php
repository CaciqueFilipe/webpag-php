<?php

namespace WebPag\Requests\Recurrency;

use WebPag\Support\ArrayHelper;
use WebPag\Contracts\RequestPayload;

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
