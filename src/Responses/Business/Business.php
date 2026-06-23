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
    public $notificationEmail;

    /** @var string|null */
    public $cnpj;

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
        $instance->notificationEmail = $data['notification_email'] ?? null;
        $instance->cnpj = $data['cnpj'] ?? null;

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
            'notification_email' => $this->notificationEmail,
            'cnpj' => $this->cnpj,
        ], function ($value) {
            return $value !== null;
        });
    }
}
