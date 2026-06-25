<?php

namespace WebPag\Resources;

use WebPag\Responses\PaymentLinks\PaymentLink;
use WebPag\Requests\PaymentLinks\ListPaymentLinkRequest;
use WebPag\Requests\PaymentLinks\CreatePaymentLinkRequest;

class PaymentLinks extends AbstractResource
{
    /**
     * Listar os links de pagamento.
     * 
     * @param ListPaymentLinkRequest|array<string, mixed>|null $filters
     *
     * @return PaymentLink[]
     */
    public function list($filters): array
    {
        $response = $this->http->get(
            'api/payment-links',
            $this->resolvePayload($filters)
        );

        return PaymentLink::fromArrayCollection($response->getData());
    }

    /**
     * Criar um link de pagamento.
     *
     * @param CreatePaymentLinkRequest|array<string, mixed> $request
     *
     * @return PaymentLink
     */
    public function create($request): PaymentLink
    {
        $response = $this->http->post(
            'api/payment-links/register',
            $this->resolvePayload($request)
        );

        return PaymentLink::fromArray($response->getData());
    }

}
