<?php

namespace WebPag\Resources;

use WebPag\Http\ApiResponse;
use WebPag\Responses\Payers\Payer;
use WebPag\Responses\Card\CreditCard;
use WebPag\Responses\Pagination\PaginatedCollection;
use WebPag\Requests\Payers\ListPayerRequest;
use WebPag\Requests\Payers\CreatePayerRequest;
use WebPag\Requests\Payers\UpdatePayerRequest;
use WebPag\Requests\Payers\SaveCreditCardRequest;

class Payers extends AbstractResource
{
    /**
     * Listar os pagadores cadastrados.
     * 
     * @param ListPayerRequest|array<string, mixed>|null $filters
     *
     * @return PaginatedCollection|Payer[]
     */
    public function list($filters = null): PaginatedCollection
    {
        $response = $this->http->get(
            'api/payers',
            $this->resolvePayload($filters)
        );

        return $this->paginate($response, Payer::class);
    }

    /**
     * Retorna dados de um pagador pelo ID.
     *
     * @param int|string $payerId
     *
     * @return Payer
     */
    public function find($payerId): Payer
    {
        $response = $this->http->get($this->path('api/payers/%s', $payerId));

        return Payer::fromArray($response->getData());
    }

    /**
     * Registra um pagador.
     *
     * @param CreatePayerRequest|array<string, mixed> $request
     *
     * @return Payer
     */
    public function create($request): Payer
    {
        $response = $this->http->post(
            'api/payers/register',
            $this->resolvePayload($request)
        );

        return Payer::fromArray($response->getData());
    }

    /**
     * Atualiza um pagador pelo ID.
     *
     * @param int|string                          $payerId
     * @param UpdatePayerRequest|array<string, mixed> $request
     *
     * @return Payer
     */
    public function update($payerId, $request): Payer
    {
        $response = $this->http->put(
            $this->path('api/payers/%s/update', $payerId),
            $this->resolvePayload($request)
        );

        return Payer::fromArray($response->getData());
    }

    /**
     * Inativa um pagador.
     *
     * @param int|string $payerId
     *
     * @return Payer
     */
    public function inactivate($payerId): Payer
    {
        $response = $this->http->put($this->path('api/payers/%s/inactivate', $payerId));

        return Payer::fromArray($response->getData());
    }

    /**
     * Salvar cartão para um pagador.
     *
     * @param int|string                              $payerId
     * @param SaveCreditCardRequest|array<string, mixed> $request
     *
     * @return CreditCard
     */
    public function saveCreditCard($payerId, $request): CreditCard
    {
        $response = $this->http->post(
            $this->path('api/payers/%s/creditcard', $payerId),
            $this->resolvePayload($request)
        );

        return CreditCard::fromArray($response->getData());
    }

    /**
     * Remove um cartão de um pagador.
     *
     * @param int|string $payerId
     * @param int|string $cardId
     *
     * @return ApiResponse
     */
    public function removeCreditCard($payerId, $cardId)
    {
        return $this->http->delete(
            $this->path('api/payers/%s/creditcard/%s/remove', $payerId, $cardId)
        );
    }

}
