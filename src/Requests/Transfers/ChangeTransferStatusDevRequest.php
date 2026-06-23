<?php

namespace WebPag\Requests\Transfers;

use WebPag\Contracts\RequestPayload;

class ChangeTransferStatusDevRequest implements RequestPayload
{
    /** @var int */
    public $status;

    /**
     * @return array<string, mixed>
     */
    public function toArray()
    {
        return [
            'status' => $this->status,
        ];
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
        $request->status = isset($data['status'])
            ? (int) $data['status']
            : 0;
        return $request;
    }
}
