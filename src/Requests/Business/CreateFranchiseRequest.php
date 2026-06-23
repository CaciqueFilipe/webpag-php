<?php

namespace WebPag\Requests\Business;

use WebPag\Contracts\RequestPayload;
use WebPag\Requests\Payers\Address;
use WebPag\Support\ArrayHelper;

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

    /**
     * Cria uma instância a partir de um array associativo.
     *
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        $request = new self();

        $request->razaoSocial = $data['razao_social'] ?? '';
        $request->cnpj = $data['cnpj'] ?? '';
        $request->transferFrequency = isset($data['transfer_frequency'])
            ? (int) $data['transfer_frequency']
            : null;
        $request->creditInstallmentLimit = isset($data['credit_installment_limit'])
            ? (int) $data['credit_installment_limit']
            : null;
        $request->notificationEmail = $data['notification_email'] ?? null;
        $request->gatewayName = $data['gateway_name'] ?? null;
        $request->accountBankCode = $data['account_bank_code'] ?? null;
        $request->accountType = $data['account_type'] ?? null;
        $request->accountHolder = $data['account_holder'] ?? null;
        $request->accountDocument = $data['account_document'] ?? null;
        $request->accountAgency = $data['account_agency'] ?? null;
        $request->accountNumber = $data['account_number'] ?? null;
        $request->pixKeyType = isset($data['pix_key_type'])
            ? (int) $data['pix_key_type']
            : null;
        $request->pixKey = $data['pix_key'] ?? null;
        $request->phoneNumber = $data['phone_number'] ?? null;

        // Trata o objeto aninhado de Endereço se ele vier como array
        if (isset($data['address']) && is_array($data['address'])) {
            // Assumindo que sua classe Address também tenha um método fromArray ou deserialize
            $request->address = Address::fromArray($data['address']);
        }

        return $request;
    }
}
