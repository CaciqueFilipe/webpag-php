<?php

namespace WebPag\Requests\Payments;

use WebPag\Contracts\RequestPayload;
use WebPag\Support\ArrayHelper;

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
