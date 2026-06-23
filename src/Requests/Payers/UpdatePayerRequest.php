<?php

namespace WebPag\Requests\Payers;

use WebPag\Support\ArrayHelper;
use WebPag\Contracts\RequestPayload;

class UpdatePayerRequest implements RequestPayload
{
    /** @var string|null */
    public $firstName;

    /** @var string|null */
    public $lastName;

    /** @var string|null */
    public $email;

    /** @var bool|null */
    public $isBusiness;

    /** @var string|null */
    public $cpfCnpj;

    /** @var string|null */
    public $phoneNumber;

    /** @var string|null */
    public $birthDate;

    /** @var Address|null */
    public $address;

    /**
     * @return array<string, mixed>
     */
    public function toArray()
    {
        $data = ArrayHelper::filterNull([
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'is_business' => $this->isBusiness,
            'cpf_cnpj' => $this->cpfCnpj,
            'phone_number' => $this->phoneNumber,
            'birth_date' => $this->birthDate,
        ]);

        if ($this->address !== null) {
            $data['address'] = $this->address->toArray();
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

        $request->firstName   = $data['first_name'] ?? null;
        $request->lastName    = $data['last_name'] ?? null;
        $request->email       = $data['email'] ?? null;
        $request->isBusiness  = isset($data['is_business'])
            ? (bool) $data['is_business']
            : null;
        $request->cpfCnpj     = $data['cpf_cnpj'] ?? null;
        $request->phoneNumber = $data['phone_number'] ?? null;
        $request->birthDate   = $data['birth_date'] ?? null;

        if (isset($data['address']) && is_array($data['address'])) {
            $request->address = Address::fromArray($data['address']);
        }

        return $request;
    }
}
