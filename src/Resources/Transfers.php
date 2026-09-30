<?php

namespace WebPag\Resources;

use WebPag\Requests\Transfers\ChangeTransferStatusDevRequest;
use WebPag\Requests\Transfers\CreateTransferRequest;
use WebPag\Requests\Transfers\ListTransfersRequest;
use WebPag\Responses\Pagination\PaginatedCollection;
use WebPag\Responses\Transfers\Transfer;

class Transfers extends AbstractResource
{
    /**
     * Listar saques/transferências.
     *
     * @param ListTransfersRequest|array<string, mixed>|null $filters
     *
     * @return PaginatedCollection|Transfer[]
     */
    public function list($filters = null): PaginatedCollection
    {
        $response = $this->http->get('api/transfers', $this->resolvePayload($filters));

        return $this->paginate($response, Transfer::class);
    }

    /**
     * Solicitar um saque (transferência).
     *
     * @param CreateTransferRequest|array<string, mixed> $request
     *
     * @return Transfer
     */
    public function create($request): Transfer
    {
        $response = $this->http->post('api/transfers', $this->resolvePayload($request));

        return $this->item($response, Transfer::class);
    }

    /**
     * Obter detalhes de um saque/transferência.
     *
     * @param int|string $transferId
     *
     * @return Transfer
     */
    public function find($transferId): Transfer
    {
        $response = $this->http->get($this->path('api/transfers/%s', $transferId));

        return $this->item($response, Transfer::class);
    }

    /**
     * Cancelar um saque/transferência.
     *
     * @param int|string $transferId
     *
     * @return Transfer
     */
    public function cancel($transferId): Transfer
    {
        $response = $this->http->delete($this->path('api/transfers/%s', $transferId));

        // O endpoint de cancelamento retorna { "message": "...", "transfer": { ... } }
        return $this->item($response, Transfer::class, 'transfer');
    }

    /**
     * Alterar status da transferência (apenas Sandbox/Desenvolvimento).
     *
     * @param int|string $transferId
     * @param ChangeTransferStatusDevRequest|array<string, mixed>  $request
     *
     * @return Transfer
     */
    public function changeStatusDev($transferId, $request): Transfer
    {
        $response = $this->http->post(
            $this->path('api/transfers/%s/change-status-dev', $transferId),
            $this->resolvePayload($request)
        );

        return $this->item($response, Transfer::class);
    }

}
