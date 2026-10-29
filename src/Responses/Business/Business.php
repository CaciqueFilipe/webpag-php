<?php

namespace WebPag\Responses\Business;

use WebPag\Contracts\ResponsePayload;

class Business implements ResponsePayload
{
    /** @var int|null */
    public $id;

    /** @var string|null */
    public $name;

    /** @var string|null */
    public $gatewayName;

    /** @var string|null */
    public $notificationEmail;

    /** @var string|null Somente dígitos, ex: "00000000000191" */
    public $cnpj;

    /** @var string|null Somente dígitos, ex: "41999999999" */
    public $phoneNumber;

    /** @var int|null */
    public $status;

    /** @var int|null Saldo atual (centavos) */
    public $currentBalance;

    /** @var BusinessFees|null */
    public $fees;

    /**
     * Cria uma instância de Business a partir de um array de dados da API.
     *
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        $instance = new self();
        $instance->id = isset($data['id']) ? (int)$data['id'] : null;
        $instance->name = $data['name'] ?? null;
        $instance->gatewayName = $data['gateway_name'] ?? null;
        $instance->notificationEmail = $data['notification_email'] ?? null;
        $instance->cnpj = $data['cnpj'] ?? null;
        $instance->phoneNumber = $data['phone_number'] ?? null;
        $instance->status = isset($data['status']) ? (int)$data['status'] : null;
        $instance->currentBalance = isset($data['current_balance']) ? (int)$data['current_balance'] : null;
        $instance->fees = isset($data['fees']) && is_array($data['fees'])
            ? BusinessFees::fromArray($data['fees'])
            : null;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
            'gateway_name' => $this->gatewayName,
            'notification_email' => $this->notificationEmail,
            'cnpj' => $this->cnpj,
            'phone_number' => $this->phoneNumber,
            'status' => $this->status,
            'current_balance' => $this->currentBalance,
            'fees' => $this->fees !== null ? $this->fees->toArray() : null,
        ], function ($value) {
            return $value !== null;
        });
    }
}
