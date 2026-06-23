<?php

namespace WebPag\Requests\Payers;

use WebPag\Contracts\RequestPayload;
use WebPag\Support\ArrayHelper;

class CreatePayerRequest implements RequestPayload
{
    /** @var string */
    public $firstName;

    /** @var string */
    public $lastName;

    /** @var string|null */
    public $email;

    /** @var bool */
    public $isBusiness;

    /** @var string */
    public $cpfCnpj;

    /** @var string|null */
    public $phoneNumber;

    /** @var string|null M ou F */
    public $gender;

    /** @var string|null Y-m-d */
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
            'gender' => $this->gender,
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

        $request->firstName = $data['first_name'] ?? '';
        $request->lastName = $data['last_name'] ?? '';
        $request->email = $data['email'] ?? null;
        $request->isBusiness = isset($data['is_business'])
            ? (bool) $data['is_business']
            : false;
        $request->cpfCnpj = $data['cpf_cnpj'] ?? '';
        $request->phoneNumber = $data['phone_number'] ?? null;
        $request->gender = $data['gender'] ?? null;
        $request->birthDate = $data['birth_date'] ?? null;

        if (isset($data['address']) && is_array($data['address'])) {
            $request->address = Address::fromArray($data['address']);
        }

        return $request;
    }
}
