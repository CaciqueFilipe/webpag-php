<?php

namespace WebPag\Resources;

use InvalidArgumentException;
use WebPag\Contracts\RequestPayload;
use WebPag\Exceptions\ApiException;
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
     *
     * @throws InvalidArgumentException Para qualquer outro tipo (ex: query string crua)
     */
    protected function resolvePayload($payload)
    {
        if ($payload === null) {
            return [];
        }

        if ($payload instanceof RequestPayload) {
            $payload = $payload->toArray();
        }

        if (! is_array($payload)) {
            throw new InvalidArgumentException('Payload inválido: use um DTO de Requests\* ou um array associativo.');
        }

        return $payload;
    }

    /**
     * Converte a resposta de um objeto único em DTO, validando o formato antes.
     *
     * Sem esta checagem, um "data" inesperado (string, null, lista) causaria TypeError fatal,
     * que escapa do catch (WebPagException) da aplicação.
     *
     * @param ApiResponse $response
     * @param string      $dtoClass Classe que implementa ResponsePayload
     * @param string|null $key      Chave dentro de "data" onde está o objeto (ex: "transfer")
     *
     * @return mixed Instância de $dtoClass
     *
     * @throws ApiException Se a resposta não trouxer um objeto
     */
    protected function item(ApiResponse $response, $dtoClass, $key = null)
    {
        $data = $response->getData();

        if ($key !== null && is_array($data) && array_key_exists($key, $data)) {
            $data = $data[$key];
        }

        if (! is_array($data) || ($data !== [] && array_keys($data) === range(0, count($data) - 1))) {
            throw new ApiException(
                'Resposta inesperada da API WebPag: era esperado um objeto.',
                $response->getStatusCode(),
                $response->toArray()
            );
        }

        return $dtoClass::fromArray($data);
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
