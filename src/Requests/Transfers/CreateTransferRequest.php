<?php

namespace WebPag\Requests\Transfers;

use WebPag\Contracts\RequestPayload;
use WebPag\Support\ArrayHelper;

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

        $request->amount = (int) ($data['amount'] ?? 0);
        $request->type = (int) ($data['type'] ?? 0);
        $request->transferType = isset($data['transfer_type']) ? (int) $data['transfer_type'] : null;
        $request->notes = isset($data['notes']) ? (string) $data['notes'] : null;
        $request->pixKey = isset($data['pix_key']) ? (string) $data['pix_key'] : null;
        $request->pixKeyType = isset($data['pix_key_type']) ? (string) $data['pix_key_type'] : null;
        $request->accountDocument = isset($data['account_document']) ? (string) $data['account_document'] : null;
        $request->accountHolder = isset($data['account_holder']) ? (string) $data['account_holder'] : null;
        $request->notificationUrl = isset($data['notification_url']) ? (string) $data['notification_url'] : null;

        return $request;
    }
}
