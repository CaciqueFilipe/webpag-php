<?php

namespace WebPag\Responses\Pagination;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use LogicException;
use Traversable;
use WebPag\Contracts\ResponsePayload;
use WebPag\Http\ApiResponse;

/**
 * Resultado de uma listagem: os itens (DTOs) + dados de paginação.
 *
 * Pode ser usado como um array somente leitura: foreach, count() e $colecao[0].
 * Para obter um array PHP de fato, use all().
 */
class PaginatedCollection implements ArrayAccess, IteratorAggregate, Countable, JsonSerializable
{
    /** @var ResponsePayload[] */
    private $items;

    /** @var PaginationLinks|null */
    private $links;

    /** @var PaginationMeta|null */
    private $meta;

    /**
     * @param ResponsePayload[]    $items
     * @param PaginationLinks|null $links
     * @param PaginationMeta|null  $meta
     */
    public function __construct(array $items, ?PaginationLinks $links = null, ?PaginationMeta $meta = null)
    {
        $this->items = array_values($items);
        $this->links = $links;
        $this->meta = $meta;
    }

    /**
     * Monta a coleção a partir da resposta da API.
     *
     * Aceita tanto o formato paginado ({"data": [...], "links": {...}, "meta": {...}})
     * quanto uma lista simples, sem paginação.
     *
     * @param ApiResponse $response
     * @param callable    $factory Recebe o array de cada item e retorna o DTO
     *
     * @return self
     */
    public static function fromResponse(ApiResponse $response, callable $factory): self
    {
        $body = $response->toArray();
        $data = $response->getData();

        $items = [];
        if (is_array($data) && self::isList($data)) {
            foreach ($data as $item) {
                if (is_array($item)) {
                    $items[] = $factory($item);
                }
            }
        }

        $links = null;
        if (isset($body['links']) && is_array($body['links']) && ! self::isList($body['links'])) {
            $links = PaginationLinks::fromArray($body['links']);
        }

        $meta = null;
        if (isset($body['meta']) && is_array($body['meta'])) {
            $meta = PaginationMeta::fromArray($body['meta']);
        }

        return new self($items, $links, $meta);
    }

    /**
     * Itens da página atual como array PHP.
     *
     * @return ResponsePayload[]
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * @return ResponsePayload|null
     */
    public function first()
    {
        return $this->items[0] ?? null;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return count($this->items) === 0;
    }

    /**
     * @return PaginationLinks|null
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * @return PaginationMeta|null
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * Indica se a resposta trouxe dados de paginação.
     *
     * @return bool
     */
    public function isPaginated(): bool
    {
        return $this->meta !== null;
    }

    /**
     * @return int|null
     */
    public function currentPage()
    {
        return $this->meta !== null ? $this->meta->currentPage : null;
    }

    /**
     * @return int|null
     */
    public function lastPage()
    {
        return $this->meta !== null ? $this->meta->lastPage : null;
    }

    /**
     * @return int|null
     */
    public function perPage()
    {
        return $this->meta !== null ? $this->meta->perPage : null;
    }

    /**
     * Total de registros em todas as páginas.
     *
     * @return int|null
     */
    public function total()
    {
        return $this->meta !== null ? $this->meta->total : null;
    }

    /**
     * @return bool
     */
    public function hasMorePages(): bool
    {
        if ($this->meta !== null && $this->meta->currentPage !== null && $this->meta->lastPage !== null) {
            return $this->meta->currentPage < $this->meta->lastPage;
        }

        return $this->links !== null && $this->links->next !== null;
    }

    /**
     * Número da próxima página, para usar no filtro "page" da próxima chamada.
     *
     * @return int|null
     */
    public function nextPage()
    {
        if (! $this->hasMorePages() || $this->currentPage() === null) {
            return null;
        }

        return $this->currentPage() + 1;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'data' => array_map(function (ResponsePayload $item) {
                return $item->toArray();
            }, $this->items),
            'links' => $this->links !== null ? $this->links->toArray() : null,
            'meta' => $this->meta !== null ? $this->meta->toArray() : null,
        ], function ($value) {
            return $value !== null;
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->items[$offset]);
    }

    /**
     * @param mixed $offset
     *
     * @return ResponsePayload|null
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->items[$offset] ?? null;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     *
     * @throws LogicException
     */
    public function offsetSet($offset, $value): void
    {
        throw new LogicException('PaginatedCollection é somente leitura.');
    }

    /**
     * @param mixed $offset
     *
     * @throws LogicException
     */
    public function offsetUnset($offset): void
    {
        throw new LogicException('PaginatedCollection é somente leitura.');
    }

    /**
     * @param array<mixed> $array
     *
     * @return bool
     */
    private static function isList(array $array): bool
    {
        return $array === [] || array_keys($array) === range(0, count($array) - 1);
    }
}
