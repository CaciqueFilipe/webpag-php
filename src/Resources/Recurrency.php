<?php

namespace WebPag\Resources;

use WebPag\Contracts\RequestPayload;
use WebPag\Http\ApiResponse;
use WebPag\Requests\Recurrency\CreateRecurrencyRequest;
use WebPag\Requests\Recurrency\ListRecurrencyRequest;
use WebPag\Requests\Recurrency\UpdateRecurrencyRequest;

class Recurrency extends AbstractResource
{
    /**
     * Cria recorrência no cartão.
     *
     * @param CreateRecurrencyRequest|array<string, mixed> $request
     *
     * @return ApiResponse
     */
    public function create($request)
    {
        return $this->http->post('api/payments/recurrency/register', $this->resolvePayload($request));
    }

    /**
     * Listar recorrências.
     *
     * @param ListRecurrencyRequest|array<string, mixed>|null $filters
     *
     * @return ApiResponse
     */
    public function list($filters = null)
    {
        return $this->http->get('api/payments/recurrency/list', $this->resolvePayload($filters));
    }

    /**
     * Atualizar recorrência.
     *
     * @param string                                           $recurrenceCode
     * @param UpdateRecurrencyRequest|array<string, mixed>     $request
     *
     * @return ApiResponse
     */
    public function update($recurrenceCode, $request)
    {
        return $this->http->put(
            'api/payments/recurrency/' . $recurrenceCode . '/update',
            $this->resolvePayload($request)
        );
    }

    /**
     * Cancelar recorrência.
     *
     * @param string $recurrenceCode
     *
     * @return ApiResponse
     */
    public function cancel($recurrenceCode)
    {
        return $this->http->put('api/payments/recurrency/' . $recurrenceCode . '/cancel');
    }

    /**
     * @param RequestPayload|array<string, mixed>|null $payload
     *
     * @return array<string, mixed>
     */
    private function resolvePayload($payload)
    {
        if ($payload === null) {
            return array();
        }

        if ($payload instanceof RequestPayload) {
            return $payload->toArray();
        }

        return $payload;
    }
}
