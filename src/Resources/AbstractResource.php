<?php

namespace WebPag\Resources;

use InvalidArgumentException;
use WebPag\Contracts\RequestPayload;
use WebPag\Http\ApiResponse;
use WebPag\Http\HttpClient;
use WebPag\Responses\Pagination\PaginatedCollection;

abstract class AbstractResource
{
    /** @var HttpClient */
    protected $http;

    /**
     * @param HttpClient $http
     */
    public function __construct(HttpClient $http)
    {
        $this->http = $http;
    }

    /**
     * Resolve o payload de requisição: aceita tanto objetos RequestPayload quanto arrays.
     *
     * @param RequestPayload|array<string, mixed>|null $payload
     *
     * @return array<string, mixed>
     */
    protected function resolvePayload($payload)
    {
        if ($payload === null) {
            return [];
        }

        if ($payload instanceof RequestPayload) {
            return $payload->toArray();
        }

        return $payload;
    }

    /**
     * Monta um caminho da API com segmentos dinâmicos (IDs) validados e codificados.
     *
     * Impede path traversal/injeção: um ID como "1/../../transfers" ou "1?x=y"
     * poderia desviar a requisição autenticada para outro endpoint.
     *
     * Ex: $this->path('api/payers/%s/creditcard/%s/remove', $payerId, $cardId)
     *
     * @param string     $template Caminho com um "%s" para cada segmento dinâmico
     * @param int|string ...$segments
     *
     * @return string
     *
     * @throws InvalidArgumentException Se algum segmento for vazio, não escalar ou "." / ".."
     */
    protected function path($template, ...$segments)
    {
        $encoded = array_map(function ($segment) {
            if (! is_int($segment) && ! is_string($segment)) {
                throw new InvalidArgumentException('Identificador inválido: informe um inteiro ou string.');
            }

            $segment = trim((string) $segment);

            if ($segment === '' || $segment === '.' || $segment === '..') {
                throw new InvalidArgumentException('Identificador inválido: valor vazio ou reservado.');
            }

            return rawurlencode($segment);
        }, $segments);

        return vsprintf($template, $encoded);
    }

    /**
     * Converte a resposta de uma listagem em uma coleção paginada de DTOs.
     *
     * @param ApiResponse $response
     * @param string      $dtoClass Classe que implementa ResponsePayload
     *
     * @return PaginatedCollection
     */
    protected function paginate(ApiResponse $response, $dtoClass)
    {
        return PaginatedCollection::fromResponse($response, [$dtoClass, 'fromArray']);
    }
}
