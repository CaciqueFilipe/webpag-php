<?php

namespace WebPag\Resources;

use WebPag\Responses\Installments\InstallmentPlan;
use WebPag\Requests\Installments\ListInstallmentsRequest;
use WebPag\Requests\Installments\CreateInstallmentRequest;

class Installments extends AbstractResource
{
    /**
     * Listar crediários da empresa.
     *
     * @param ListInstallmentsRequest|array<string, mixed>|null $filters
     *
     * @return InstallmentPlan[]
     */
    public function list($filters = null): array
    {
        $response = $this->http->get(
            'api/installments',
            $this->resolvePayload($filters)
        );

        return InstallmentPlan::fromArrayCollection($response->getData());
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
        $response = $this->http->get('api/installments/' . $installmentPlanId);

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
        $response = $this->http->post('api/installments/' . $installmentPlanId . '/cancel');

        return InstallmentPlan::fromArray($response->getData());
    }

}
