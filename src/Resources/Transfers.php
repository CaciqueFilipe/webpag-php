<?php

namespace WebPag\Resources;

use WebPag\Contracts\RequestPayload;
use WebPag\Http\ApiResponse;
use WebPag\Requests\Transfers\ChangeTransferStatusDevRequest;
use WebPag\Requests\Transfers\CreateTransferRequest;
use WebPag\Requests\Transfers\ListTransfersRequest;

class Transfers extends AbstractResource
{
    /**
     * Listar saques/transferências.
     *
     * @param ListTransfersRequest|array<string, mixed>|null $filters
     *
     * @return ApiResponse
     */
    public function list($filters = null)
    {
        return $this->http->get('api/transfers', $this->resolvePayload($filters));
    }

    /**
     * Solicitar um saque (transferência).
     *
     * @param CreateTransferRequest|array<string, mixed> $request
     *
     * @return ApiResponse
     */
    public function create($request)
    {
        return $this->http->post('api/transfers', $this->resolvePayload($request));
    }

    /**
     * Obter detalhes de um saque/transferência.
     *
     * @param int|string $transferId
     *
     * @return ApiResponse
     */
    public function find($transferId)
    {
        return $this->http->get('api/transfers/' . $transferId);
    }

    /**
     * Cancelar um saque/transferência.
     *
     * @param int|string $transferId
     *
     * @return ApiResponse
     */
    public function cancel($transferId)
    {
        return $this->http->delete('api/transfers/' . $transferId);
    }

    /**
     * Alterar status da transferência (apenas Sandbox/Desenvolvimento).
     *
     * @param int|string                                           $transferId
     * @param ChangeTransferStatusDevRequest|array<string, mixed>  $request
     *
     * @return ApiResponse
     */
    public function changeStatusDev($transferId, $request)
    {
        return $this->http->post(
            'api/transfers/' . $transferId . '/change-status-dev',
            $this->resolvePayload($request)
        );
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
