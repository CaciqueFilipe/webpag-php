<?php

namespace WebPag\Requests\Payers;

use WebPag\Support\ArrayHelper;
use WebPag\Contracts\RequestPayload;

class ListPayerRequest implements RequestPayload
{
    /** @var int|null */
    public $page;

    /** @var int|null */
    public $perPage;

    /** @var string|null */
    public $cpfCnpj;
    
    /** @var string|null */
    public $email;

    /** @var int|null */
    public $status;

    /**
     * @return array<string, mixed>
     */
    public function toArray()
    {
        return ArrayHelper::filterNull([
            'cpf_cnpj' => $this->cpfCnpj,
            'email' => $this->email,
            'status' => $this->status,
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

        $request->cpfCnpj = isset($data['cpf_cnpj'])
            ? $data['cpf_cnpj']
            : null;
        $request->email = isset($data['email'])
            ? $data['email']
            : null;
        $request->status = isset($data['status'])
            ? (int) $data['status']
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
