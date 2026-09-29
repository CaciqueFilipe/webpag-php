<?php

namespace WebPag\Responses\Pagination;

use WebPag\Contracts\ResponsePayload;

/**
 * Links de navegação da listagem (chave "links" da resposta).
 */
class PaginationLinks implements ResponsePayload
{
    /** @var string|null */
    public $first;

    /** @var string|null */
    public $last;

    /** @var string|null */
    public $prev;

    /** @var string|null */
    public $next;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $instance = new self();
        $instance->first = $data['first'] ?? null;
        $instance->last = $data['last'] ?? null;
        $instance->prev = $data['prev'] ?? null;
        $instance->next = $data['next'] ?? null;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return [
            'first' => $this->first,
            'last' => $this->last,
            'prev' => $this->prev,
            'next' => $this->next,
        ];
    }
}
