<?php

namespace WebPag\Requests\Payments;

use WebPag\Contracts\RequestPayload;
use WebPag\Support\ArrayHelper;

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
        $request->amount = isset($data['amount'])
            ? (int) $data['amount']
            : null;

        return $request;
    }
}
