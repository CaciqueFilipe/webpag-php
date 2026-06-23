<?php

namespace WebPag\Resources;

use WebPag\Responses\Card\CardToken;
use WebPag\Responses\Business\Franchise;
use WebPag\Responses\Business\Authentication;
use WebPag\Requests\Business\AuthenticateRequest;
use WebPag\Requests\Business\CreateFranchiseRequest;
use WebPag\Responses\Business\Business as BusinessResponse;

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

        return Authentication::fromArray($response->getData());
    }

    /**
     * Dados da empresa logada.
     *
     * @return BusinessResponse
     */
    public function me(): BusinessResponse
    {
        $response = $this->http->get('api/me');

        return BusinessResponse::fromArray($response->getData());
    }

    /**
     * Chave pública para tokenização do cartão.
     *
     * @return CardToken
     */
    public function cardTokenPublicKey(): CardToken
    {
        $response = $this->http->get('api/card-token/public-key');

        return CardToken::fromArray($response->getData());
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

        return Franchise::fromArray($response->getData());
    }

}
