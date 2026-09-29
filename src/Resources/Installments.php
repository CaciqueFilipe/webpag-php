<?php

namespace WebPag\Resources;

use WebPag\Requests\Installments\CreateInstallmentRequest;
use WebPag\Requests\Installments\ListInstallmentsRequest;
use WebPag\Responses\Installments\InstallmentPlan;
use WebPag\Responses\Pagination\PaginatedCollection;

class Installments extends AbstractResource
{
    /**
     * Listar crediários da empresa.
     *
     * @param ListInstallmentsRequest|array<string, mixed>|null $filters
     *
     * @return PaginatedCollection|InstallmentPlan[]
     */
    public function list($filters = null): PaginatedCollection
    {
        $response = $this->http->get(
            'api/installments',
            $this->resolvePayload($filters)
        );

        return $this->paginate($response, InstallmentPlan::class);
    }

    /**
     * Criar um novo crediário.
     *
     * @param CreateInstallmentRequest|array<string, mixed> $request
     *
     * @return InstallmentPlan
     */
    public function create($request): InstallmentPlan
    {
        $response = $this->http->post(
            'api/installments/register',
            $this->resolvePayload($request)
        );

        return InstallmentPlan::fromArray($response->getData());
    }

    /**
     * Consultar um crediário pelo ID.
     *
     * @param int|string $installmentPlanId
     *
     * @return InstallmentPlan
     */
    public function find($installmentPlanId): InstallmentPlan
    {
        $response = $this->http->get($this->path('api/installments/%s', $installmentPlanId));

        return InstallmentPlan::fromArray($response->getData());
    }

    /**
     * Cancelar um crediário.
     *
     * @param int|string $installmentPlanId
     *
     * @return InstallmentPlan
     */
    public function cancel($installmentPlanId): InstallmentPlan
    {
        $response = $this->http->post($this->path('api/installments/%s/cancel', $installmentPlanId));

        return InstallmentPlan::fromArray($response->getData());
    }

}
