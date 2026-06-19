<?php

namespace WebPag\Resources;

use WebPag\Contracts\RequestPayload;
use WebPag\Http\ApiResponse;
use WebPag\Requests\PaymentLinks\CreatePaymentLinkRequest;

class PaymentLinks extends AbstractResource
{
    /**
     * Listar os links de pagamento.
     *
     * @return ApiResponse
     */
    public function list()
    {
        return $this->http->get('api/payment-links');
    }

    /**
     * Criar um link de pagamento.
     *
     * @param CreatePaymentLinkRequest|array<string, mixed> $request
     *
     * @return ApiResponse
     */
    public function create($request)
    {
        return $this->http->post('api/payment-links/register', $this->resolvePayload($request));
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
