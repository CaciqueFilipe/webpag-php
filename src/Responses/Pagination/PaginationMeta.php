<?php

namespace WebPag\Responses\Pagination;

use WebPag\Contracts\ResponsePayload;

/**
 * Metadados de paginação (chave "meta" da resposta).
 */
class PaginationMeta implements ResponsePayload
{
    /** @var int|null */
    public $currentPage;

    /** @var int|null Posição do primeiro item da página (null quando a página está vazia) */
    public $from;

    /** @var int|null */
    public $lastPage;

    /** @var PageLink[]|null */
    public $links;

    /** @var string|null */
    public $path;

    /** @var int|null */
    public $perPage;

    /** @var int|null Posição do último item da página (null quando a página está vazia) */
    public $to;

    /** @var int|null */
    public $total;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $instance = new self();
        $instance->currentPage = isset($data['current_page']) ? (int) $data['current_page'] : null;
        $instance->from = isset($data['from']) ? (int) $data['from'] : null;
        $instance->lastPage = isset($data['last_page']) ? (int) $data['last_page'] : null;
        $instance->path = $data['path'] ?? null;
        $instance->perPage = isset($data['per_page']) ? (int) $data['per_page'] : null;
        $instance->to = isset($data['to']) ? (int) $data['to'] : null;
        $instance->total = isset($data['total']) ? (int) $data['total'] : null;

        if (isset($data['links']) && is_array($data['links'])) {
            $instance->links = array_map(function ($link) {
                return PageLink::fromArray($link);
            }, array_values(array_filter($data['links'], 'is_array')));
        }

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return array_filter([
            'current_page' => $this->currentPage,
            'from' => $this->from,
            'last_page' => $this->lastPage,
            'links' => $this->links !== null ? array_map(function (PageLink $link) {
                return $link->toArray();
            }, $this->links) : null,
            'path' => $this->path,
            'per_page' => $this->perPage,
            'to' => $this->to,
            'total' => $this->total,
        ], function ($value) {
            return $value !== null;
        });
    }
}
