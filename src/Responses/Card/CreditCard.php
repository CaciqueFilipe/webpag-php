<?php

namespace WebPag\Responses\Card;

use WebPag\Contracts\ResponsePayload;

class CreditCard implements ResponsePayload
{
    /** @var int|null */
    public $id;

    /** @var string|null */
    public $lastNumbers;

    /** @var bool|null */
    public $active;

    /** @var string|null (Y-m-d H:i:s) */
    public $createdAt;

    /**
     * Cria uma instância de CreditCard a partir de um array de dados da API.
     *
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        $card = new self();

        $card->id = isset($data['id']) ? (int)$data['id'] : null;
        $card->lastNumbers = $data['last_numbers'] ?? null;
        $card->active = isset($data['active']) ? (bool)$data['active'] : null;
        $card->createdAt = $data['created_at'] ?? null;

        return $card;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'last_numbers' => $this->lastNumbers,
            'active' => $this->active,
            'created_at' => $this->createdAt,
        ];
    }
}
