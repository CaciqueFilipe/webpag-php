<?php

namespace WebPag\Requests\PaymentLinks;

use WebPag\Support\ArrayHelper;
use WebPag\Contracts\RequestPayload;

class ListPaymentLinkRequest implements RequestPayload
{
     /** @var int|null */
    public $page;

    /** @var int|null */
    public $perPage;

    /** @var string|null */
    public $payerId;

    /** @var int|null */
    public $value;

    /**
     * @return array<string, mixed>
     */
    public function toArray()
    {
        return ArrayHelper::filterNull([
            'payer_id' => $this->payerId,
            'value' => $this->value,
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

        $request->payerId = isset($data['payer_id'])
            ? $data['payer_id']
            : null;
        $request->value = isset($data['value'])
            ? (int) $data['value']
            : null;
        $request->page = isset($data['page'])
            ? (int) $data['page']
            : null;
        $request->perPage = isset($data['per_page'])
            ? (int) $data['per_page']
            : null;

        return $request;
    }
}