<?php

namespace WebPag\Resources;

use WebPag\Http\ApiResponse;
use WebPag\Responses\Payers\Payer;
use WebPag\Responses\Card\CreditCard;
use WebPag\Requests\Payers\CreatePayerRequest;
use WebPag\Requests\Payers\UpdatePayerRequest;
use WebPag\Requests\Payers\SaveCreditCardRequest;

class Payers extends AbstractResource
{
    /**
     * Listar os pagadores cadastrados.
     *
     * @return Payer[]
     */
    public function list(): array
    {
        $response = $this->http->get('api/payers');

        return Payer::fromArrayCollection($response->getData());
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
        $response = $this->http->get('api/payers/' . $payerId);

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
            'api/payers/' . $payerId . '/update',
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
        $response = $this->http->put('api/payers/' . $payerId . '/inactivate');

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
            'api/payers/' . $payerId . '/creditcard',
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
            'api/payers/' . $payerId . '/creditcard/' . $cardId . '/remove'
        );
    }

}
