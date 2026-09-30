<?php

namespace WebPag\Resources;

use WebPag\Requests\Recurrency\CreateRecurrencyRequest;
use WebPag\Requests\Recurrency\ListRecurrencyRequest;
use WebPag\Requests\Recurrency\UpdateRecurrencyRequest;
use WebPag\Responses\Pagination\PaginatedCollection;
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

        return $this->item($response, RecurrencyResponse::class);
    }

    /**
     * Listar recorrências.
     *
     * @param ListRecurrencyRequest|array<string, mixed>|null $filters
     *
     * @return PaginatedCollection|RecurrencyResponse[]
     */
    public function list($filters = null): PaginatedCollection
    {
        $response = $this->http->get(
            'api/payments/recurrency/list',
            $this->resolvePayload($filters)
        );

        return $this->paginate($response, RecurrencyResponse::class);
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
            $this->path('api/payments/recurrency/%s/update', $recurrenceCode),
            $this->resolvePayload($request)
        );

        return $this->item($response, RecurrencyResponse::class);
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
            $this->path('api/payments/recurrency/%s/cancel', $recurrenceCode)
        );

        return $this->item($response, RecurrencyResponse::class);
    }

}
