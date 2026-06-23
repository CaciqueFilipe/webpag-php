<?php

namespace WebPag\Responses\Business;

use WebPag\Contracts\ResponsePayload;

class Franchise implements ResponsePayload
{
    /** @var Business|null */
    public $franchise;

    /** @var string|null */
    public $apiToken;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $instance = new self();

        if (isset($data['franchise']) && is_array($data['franchise'])) {
            $instance->franchise = Business::fromArray($data['franchise']);
        }

        $instance->apiToken = $data['api_token'] ?? null;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return array_filter([
            'franchise' => $this->franchise ? $this->franchise->toArray() : null,
            'api_token' => $this->apiToken,
        ], function ($value) {
            return $value !== null;
        });
    }
}