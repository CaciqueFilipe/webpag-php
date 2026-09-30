<?php

namespace WebPag\Resources;

use WebPag\Requests\Business\AuthenticateRequest;
use WebPag\Requests\Business\CreateFranchiseRequest;
use WebPag\Responses\Business\Authentication;
use WebPag\Responses\Business\Business as BusinessResponse;
use WebPag\Responses\Business\Franchise;
use WebPag\Responses\Card\CardToken;

class Business extends AbstractResource
{
    /**
     * Verificar autenticação do usuário.
     *
     * @param AuthenticateRequest|array<string, mixed> $request
     *
     * @return Authentication
     */
    public function authenticate($request): Authentication
    {
        $response = $this->http->post(
            'api/authenticate',
            $this->resolvePayload($request)
        );

        return $this->item($response, Authentication::class);
    }

    /**
     * Dados da empresa logada.
     *
     * @return BusinessResponse
     */
    public function me(): BusinessResponse
    {
        $response = $this->http->get('api/me');

        return $this->item($response, BusinessResponse::class);
    }

    /**
     * Chave pública para tokenização do cartão.
     *
     * @return CardToken
     */
    public function cardTokenPublicKey(): CardToken
    {
        $response = $this->http->get('api/card-token/public-key');

        return $this->item($response, CardToken::class);
    }

    /**
     * Criar uma nova filial.
     *
     * @param CreateFranchiseRequest|array<string, mixed> $request
     *
     * @return Franchise
     */
    public function createFranchise($request): Franchise
    {
        $response = $this->http->post(
            'api/franchises',
            $this->resolvePayload($request)
        );

        return $this->item($response, Franchise::class);
    }

}
