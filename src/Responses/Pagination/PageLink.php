<?php

namespace WebPag\Responses\Pagination;

use WebPag\Contracts\ResponsePayload;

/**
 * Item de navegação de página (meta.links).
 */
class PageLink implements ResponsePayload
{
    /** @var string|null */
    public $url;

    /** @var string|null */
    public $label;

    /** @var bool|null */
    public $active;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $instance = new self();
        $instance->url = $data['url'] ?? null;
        $instance->label = $data['label'] ?? null;
        $instance->active = isset($data['active']) ? (bool) $data['active'] : null;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return [
            'url' => $this->url,
            'label' => $this->label,
            'active' => $this->active,
        ];
    }
}
