<?php

namespace WebPag\Responses\Payers;

use WebPag\Contracts\ResponsePayload;
use WebPag\Requests\Payers\Address;

class Payer implements ResponsePayload
{
    /** @var int|null */
    public $id;

    /** @var string|null */
    public $cpfCnpj;

    /** @var bool|null */
    public $isBusiness;

    /** @var string|null */
    public $email;

    /** @var string|null */
    public $firstName;

    /** @var string|null */
    public $lastName;

    /** @var string|null {F|M} */
    public $gender;

    /** @var string|null */
    public $phoneNumber;

    /** @var string|null (Y-m-d) */
    public $birthDate;

    /** @var Address|null */
    public $address;

    /** @var bool|null */
    public $useBoleto;

    /** @var int|null */
    public $status;

    /** @var string|null */
    public $statusLabel;

    /** @var string|null (Y-m-d H:i:s) */
    public $createdAt;

    /** @var string|null (Y-m-d H:i:s) */
    public $updatedAt;

    /**
     * Cria uma instância de Payer a partir de um array de dados da API.
     *
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        $payer = new self();

        $payer->id          = isset($data['id']) ? (int)$data['id'] : null;
        $payer->cpfCnpj     = $data['cpf_cnpj'] ?? null;
        $payer->isBusiness  = isset($data['is_business'])
            ? (bool)$data['is_business']
            : null;
        $payer->email       = $data['email'] ?? null;
        $payer->firstName   = $data['first_name'] ?? null;
        $payer->lastName    = $data['last_name'] ?? null;
        $payer->gender      = $data['gender'] ?? null;
        $payer->phoneNumber = $data['phone_number'] ?? null;
        $payer->birthDate   = $data['birth_date'] ?? null;
        $payer->useBoleto   = isset($data['use_boleto'])
            ? (bool)$data['use_boleto']
            : null;
        $payer->status      = isset($data['status']) ? (int)$data['status'] : null;
        $payer->statusLabel = $data['status_label'] ?? null;
        $payer->createdAt   = $data['created_at'] ?? null;
        $payer->updatedAt   = $data['updated_at'] ?? null;

        if (isset($data['address']) && is_array($data['address'])) {
            $payer->address = Address::fromArray($data['address']);
        }

        return $payer;
    }

    /**
     * Cria uma coleção de instâncias de Payer a partir de um array de dados da API.
     *
     * @param array<array<string, mixed>> $data
     * @return self[]
     */
    public static function fromArrayCollection(array $data): array
    {
        $payers = [];
        foreach ($data as $payerData) {
            if (is_array($payerData)) {
                $payers[] = self::fromArray($payerData);
            }
        }
        return $payers;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return [
            'id'           => $this->id,
            'cpf_cnpj'     => $this->cpfCnpj,
            'is_business'  => $this->isBusiness,
            'email'        => $this->email,
            'first_name'   => $this->firstName,
            'last_name'    => $this->lastName,
            'gender'       => $this->gender,
            'phone_number' => $this->phoneNumber,
            'birth_date'   => $this->birthDate,
            'address'      => $this->address ? $this->address->toArray() : null,
            'use_boleto'   => $this->useBoleto,
            'status'       => $this->status,
            'status_label' => $this->statusLabel,
            'created_at'   => $this->createdAt,
            'updated_at'   => $this->updatedAt,
        ];
    }
}