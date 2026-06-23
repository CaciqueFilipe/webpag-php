<?php

namespace WebPag\Resources;

use WebPag\Requests\Recurrency\CreateRecurrencyRequest;
use WebPag\Requests\Recurrency\ListRecurrencyRequest;
use WebPag\Requests\Recurrency\UpdateRecurrencyRequest;
use WebPag\Responses\Recurrency\Recurrency as RecurrencyResponse;

class Recurrency extends AbstractResource
{
    /**
     * Cria recorrência no cartão.
     *
     * @param CreateRecurrencyRequest|array<string, mixed> $request
     *
     * @return RecurrencyResponse
     */
    public function create($request): RecurrencyResponse
    {
        $response = $this->http->post(
            'api/payments/recurrency/register',
            $this->resolvePayload($request)
        );

        return RecurrencyResponse::fromArray($response->getData());
    }

    /**
     * Listar recorrências.
     *
     * @param ListRecurrencyRequest|array<string, mixed>|null $filters
     *
     * @return RecurrencyResponse[]
     */
    public function list($filters = null): array
    {
        $response = $this->http->get(
            'api/payments/recurrency/list',
            $this->resolvePayload($filters)
        );

        return RecurrencyResponse::fromArrayCollection($response->getData());
    }

    /**
     * Atualizar recorrência.
     *
     * @param string                                           $recurrenceCode
     * @param UpdateRecurrencyRequest|array<string, mixed>     $request
     *
     * @return RecurrencyResponse
     */
    public function update($recurrenceCode, $request): RecurrencyResponse
    {
        $response = $this->http->put(
            'api/payments/recurrency/' . $recurrenceCode . '/update',
            $this->resolvePayload($request)
        );

        return RecurrencyResponse::fromArray($response->getData());
    }

    /**
     * Cancelar recorrência.
     *
     * @param string $recurrenceCode
     *
     * @return RecurrencyResponse
     */
    public function cancel($recurrenceCode): RecurrencyResponse
    {
        $response = $this->http->put(
            'api/payments/recurrency/' . $recurrenceCode . '/cancel'
        );

        return RecurrencyResponse::fromArray($response->getData());
    }

}
