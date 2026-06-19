<?php

namespace WebPag\Requests\Transfers;

use WebPag\Contracts\RequestPayload;
use WebPag\Support\ArrayHelper;

class ListTransfersRequest implements RequestPayload
{
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
            'page' => $this->page,
            'per_page' => $this->perPage,
        ]);
    }
}

class CreateTransferRequest implements RequestPayload
{
    /** @var int|float Valor em centavos */
    public $amount;

    /** @var int 10 = minha conta, 30 = outra conta */
    public $type;

    /** @var int|null 10 = normal, 20 = express */
    public $transferType;

    /** @var string|null */
    public $notes;

    /** @var string|null */
    public $pixKey;

    /** @var string|null cpf, cnpj, email, phone, random */
    public $pixKeyType;

    /** @var string|null */
    public $accountDocument;

    /** @var string|null */
    public $accountHolder;

    /** @var string|null */
    public $notificationUrl;

    /**
     * @return array<string, mixed>
     */
    public function toArray()
    {
        return ArrayHelper::filterNull([
            'amount' => $this->amount,
            'type' => $this->type,
            'transfer_type' => $this->transferType,
            'notes' => $this->notes,
            'pix_key' => $this->pixKey,
            'pix_key_type' => $this->pixKeyType,
            'account_document' => $this->accountDocument,
            'account_holder' => $this->accountHolder,
            'notification_url' => $this->notificationUrl,
        ]);
    }
}

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
}
