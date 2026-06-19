<?php

namespace WebPag\Resources;

use WebPag\Contracts\RequestPayload;
use WebPag\Http\ApiResponse;
use WebPag\Requests\Installments\CreateInstallmentRequest;
use WebPag\Requests\Installments\ListInstallmentsRequest;

class Installments extends AbstractResource
{
    /**
     * Listar crediários da empresa.
     *
     * @param ListInstallmentsRequest|array<string, mixed>|null $filters
     *
     * @return ApiResponse
     */
    public function list($filters = null)
    {
        return $this->http->get('api/installments', $this->resolvePayload($filters));
    }

    /**
     * Criar um novo crediário.
     *
     * @param CreateInstallmentRequest|array<string, mixed> $request
     *
     * @return ApiResponse
     */
    public function create($request)
    {
        return $this->http->post('api/installments/register', $this->resolvePayload($request));
    }

    /**
     * Consultar um crediário pelo ID.
     *
     * @param int|string $installmentPlanId
     *
     * @return ApiResponse
     */
    public function find($installmentPlanId)
    {
        return $this->http->get('api/installments/' . $installmentPlanId);
    }

    /**
     * Cancelar um crediário.
     *
     * @param int|string $installmentPlanId
     *
     * @return ApiResponse
     */
    public function cancel($installmentPlanId)
    {
        return $this->http->post('api/installments/' . $installmentPlanId . '/cancel');
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
