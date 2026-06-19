<?php

namespace WebPag\Requests\Business;

use WebPag\Contracts\RequestPayload;
use WebPag\Requests\Payers\Address;
use WebPag\Support\ArrayHelper;

class AuthenticateRequest implements RequestPayload
{
    /** @var string */
    public $email;

    /** @var string */
    public $password;

    /**
     * @return array<string, mixed>
     */
    public function toArray()
    {
        return [
            'email' => $this->email,
            'password' => $this->password,
        ];
    }
}

class CreateFranchiseRequest implements RequestPayload
{
    /** @var string */
    public $razaoSocial;

    /** @var string */
    public $cnpj;

    /** @var int|null */
    public $transferFrequency;

    /** @var int|null */
    public $creditInstallmentLimit;

    /** @var string|null */
    public $notificationEmail;

    /** @var string|null */
    public $gatewayName;

    /** @var string|null */
    public $accountBankCode;

    /** @var string|null */
    public $accountType;

    /** @var string|null */
    public $accountHolder;

    /** @var string|null */
    public $accountDocument;

    /** @var string|null */
    public $accountAgency;

    /** @var string|null */
    public $accountNumber;

    /** @var int|null */
    public $pixKeyType;

    /** @var string|null */
    public $pixKey;

    /** @var string|null */
    public $phoneNumber;

    /** @var Address|null */
    public $address;

    /**
     * @return array<string, mixed>
     */
    public function toArray()
    {
        $data = ArrayHelper::filterNull([
            'razao_social' => $this->razaoSocial,
            'cnpj' => $this->cnpj,
            'transfer_frequency' => $this->transferFrequency,
            'credit_installment_limit' => $this->creditInstallmentLimit,
            'notification_email' => $this->notificationEmail,
            'gateway_name' => $this->gatewayName,
            'account_bank_code' => $this->accountBankCode,
            'account_type' => $this->accountType,
            'account_holder' => $this->accountHolder,
            'account_document' => $this->accountDocument,
            'account_agency' => $this->accountAgency,
            'account_number' => $this->accountNumber,
            'pix_key_type' => $this->pixKeyType,
            'pix_key' => $this->pixKey,
            'phone_number' => $this->phoneNumber,
        ]);

        if ($this->address !== null) {
            $data['address'] = $this->address->toArray();
        }

        return $data;
    }
}
