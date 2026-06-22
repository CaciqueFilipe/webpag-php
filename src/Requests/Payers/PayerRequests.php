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

        $request->firstName   = $data['first_name'] ?? '';
        $request->lastName    = $data['last_name'] ?? '';
        $request->email       = $data['email'] ?? null;
        $request->isBusiness  = isset($data['is_business'])
            ? (bool) $data['is_business']
            : false;
        $request->cpfCnpj     = $data['cpf_cnpj'] ?? '';
        $request->phoneNumber = $data['phone_number'] ?? null;
        $request->gender      = $data['gender'] ?? null;
        $request->birthDate   = $data['birth_date'] ?? null;

        if (isset($data['address']) && is_array($data['address'])) {
            $request->address = Address::fromArray($data['address']);
        }

        return $request;
    }
}

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

class SaveCreditCardRequest implements RequestPayload
{
    /** @var string|null Token gerado no frontend */
    public $cardToken;

    /** @var string|null */
    public $number;

    /** @var string|null */
    public $name;

    /** @var string|null MM */
    public $expirationMonth;

    /** @var string|null YYYY */
    public $expirationYear;

    /** @var string|null */
    public $securityCode;

    /**
     * @return array<string, mixed>
     */
    public function toArray()
    {
        return ArrayHelper::filterNull([
            'card_token' => $this->cardToken,
            'number' => $this->number,
            'name' => $this->name,
            'expiration_month' => $this->expirationMonth,
            'expiration_year' => $this->expirationYear,
            'security_code' => $this->securityCode,
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

        $request->cardToken       = $data['card_token'] ?? null;
        $request->number          = $data['number'] ?? null;
        $request->name            = $data['name'] ?? null;
        $request->expirationMonth = $data['expiration_month'] ?? null;
        $request->expirationYear  = $data['expiration_year'] ?? null;
        $request->securityCode    = $data['security_code'] ?? null;

        return $request;
    }
}
