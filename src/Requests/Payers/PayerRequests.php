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
}
