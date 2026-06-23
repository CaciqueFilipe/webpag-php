<?php

namespace WebPag\Requests\Recurrency;

use WebPag\Contracts\RequestPayload;
use WebPag\Support\ArrayHelper;

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

        $request->payerId = isset($data['payer_id'])
            ? (int) $data['payer_id']
            : null;
        $request->recurrenceCode = $data['recurrence_code'] ?? null;
        $request->page = isset($data['page'])
            ? (int) $data['page']
            : null;
        $request->perPage = isset($data['per_page'])
            ? (int) $data['per_page']
            : null;

        return $request;
    }
}
