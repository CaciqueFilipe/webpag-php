<?php

namespace WebPag\Responses\Card;

use WebPag\Contracts\ResponsePayload;

class CardToken implements ResponsePayload
{
    /** @var string|null */
    public $publicKey;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $instance = new self();
        $instance->publicKey = $data['public_key'] ?? null;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return array_filter([
            'public_key' => $this->publicKey,
        ], function ($value) {
            return $value !== null;
        });
    }
}
