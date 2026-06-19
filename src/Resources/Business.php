<?php

namespace WebPag\Resources;

use WebPag\Contracts\RequestPayload;
use WebPag\Http\ApiResponse;
use WebPag\Requests\Business\AuthenticateRequest;
use WebPag\Requests\Business\CreateFranchiseRequest;

class Business extends AbstractResource
{
    /**
     * Verificar autenticação do usuário.
     *
     * @param AuthenticateRequest|array<string, mixed> $request
     *
     * @return ApiResponse
     */
    public function authenticate($request)
    {
        return $this->http->post('api/authenticate', $this->resolvePayload($request));
    }

    /**
     * Dados da empresa logada.
     *
     * @return ApiResponse
     */
    public function me()
    {
        return $this->http->get('api/me');
    }

    /**
     * Chave pública para tokenização do cartão.
     *
     * @return ApiResponse
     */
    public function cardTokenPublicKey()
    {
        return $this->http->get('api/card-token/public-key');
    }

    /**
     * Criar uma nova filial.
     *
     * @param CreateFranchiseRequest|array<string, mixed> $request
     *
     * @return ApiResponse
     */
    public function createFranchise($request)
    {
        return $this->http->post('api/franchises', $this->resolvePayload($request));
    }

    /**
     * @param RequestPayload|array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    private function resolvePayload($payload)
    {
        if ($payload instanceof RequestPayload) {
            return $payload->toArray();
        }

        return $payload;
    }
}
