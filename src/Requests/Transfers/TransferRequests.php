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

    /**
     * Cria uma instância a partir de um array associativo.
     *
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        $request = new self();

        $request->page    = isset($data['page'])
            ? (int) $data['page']
            : null;
        $request->perPage = isset($data['per_page'])
            ? (int) $data['per_page']
            : null;

        return $request;
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

    /**
     * Cria uma instância a partir de um array associativo.
     *
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        $request = new self();

        // Convertendo para int para garantir a integridade dos centavos
        $request->amount          = isset($data['amount'])
            ? (int) $data['amount']
            : 0;
        $request->type            = isset($data['type'])
            ? (int) $data['type']
            : 0;
        $request->transferType    = isset($data['transfer_type'])
            ? (int) $data['transfer_type']
            : null;
        
        $request->notes           = $data['notes'] ?? null;
        $request->pixKey          = $data['pix_key'] ?? null;
        $request->pixKeyType      = $data['pix_key_type'] ?? null;
        $request->accountDocument = $data['account_document'] ?? null;
        $request->accountHolder   = $data['account_holder'] ?? null;
        $request->notificationUrl = $data['notification_url'] ?? null;

        return $request;
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
