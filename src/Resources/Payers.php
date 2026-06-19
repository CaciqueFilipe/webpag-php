<?php

namespace WebPag\Resources;

use WebPag\Http\ApiResponse;
use WebPag\Requests\Payers\CreatePayerRequest;
use WebPag\Requests\Payers\SaveCreditCardRequest;
use WebPag\Requests\Payers\UpdatePayerRequest;

class Payers extends AbstractResource
{
    /**
     * Listar os pagadores cadastrados.
     *
     * @return ApiResponse
     */
    public function list()
    {
        return $this->http->get('api/payers');
    }

    /**
     * Retorna dados de um pagador pelo ID.
     *
     * @param int|string $payerId
     *
     * @return ApiResponse
     */
    public function find($payerId)
    {
        return $this->http->get('api/payers/' . $payerId);
    }

    /**
     * Registra um pagador.
     *
     * @param CreatePayerRequest|array<string, mixed> $request
     *
     * @return ApiResponse
     */
    public function create($request)
    {
        return $this->http->post('api/payers/register', $this->resolvePayload($request));
    }

    /**
     * Atualiza um pagador pelo ID.
     *
     * @param int|string                          $payerId
     * @param UpdatePayerRequest|array<string, mixed> $request
     *
     * @return ApiResponse
     */
    public function update($payerId, $request)
    {
        return $this->http->put('api/payers/' . $payerId . '/update', $this->resolvePayload($request));
    }

    /**
     * Inativa um pagador.
     *
     * @param int|string $payerId
     *
     * @return ApiResponse
     */
    public function inactivate($payerId)
    {
        return $this->http->put('api/payers/' . $payerId . '/inactivate');
    }

    /**
     * Salvar cartão para um pagador.
     *
     * @param int|string                              $payerId
     * @param SaveCreditCardRequest|array<string, mixed> $request
     *
     * @return ApiResponse
     */
    public function saveCreditCard($payerId, $request)
    {
        return $this->http->post('api/payers/' . $payerId . '/creditcard', $this->resolvePayload($request));
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
        return $this->http->delete('api/payers/' . $payerId . '/creditcard/' . $cardId . '/remove');
    }

}
