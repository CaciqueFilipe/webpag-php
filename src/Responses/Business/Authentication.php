<?php

namespace WebPag\Responses\Business;

use WebPag\Contracts\ResponsePayload;

class Authentication implements ResponsePayload
{
    /** @var int|null */
    public $id;

    /** @var int|null */
    public $businessId;

    /** @var string|null */
    public $email;

    /** @var string|null */
    public $name;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $instance = new self();
        $instance->id = isset($data['id']) ? (int) $data['id'] : null;
        $instance->businessId = isset($data['business_id']) ? (int) $data['business_id'] : null;
        $instance->email = $data['email'] ?? null;
        $instance->name = $data['name'] ?? null;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'business_id' => $this->businessId,
            'email' => $this->email,
            'name' => $this->name,
        ], function ($value) {
            return $value !== null;
        });
    }
}
