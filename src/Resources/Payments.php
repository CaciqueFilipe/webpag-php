<?php

namespace WebPag\Resources;

use WebPag\Responses\Payments\Refund;
use WebPag\Responses\Payments\Payment;
use WebPag\Requests\Payments\ListPaymentsRequest;
use WebPag\Requests\Payments\RefundPaymentRequest;
use WebPag\Requests\Payments\ProcessPaymentRequest;

class Payments extends AbstractResource
{
    /**
     * Listar os pagamentos cadastrados.
     *
     * @param ListPaymentsRequest|array<string, mixed>|null $filters
     *
     * @return Payment[]
     */
    public function list($filters = null): array
    {
        $response = $this->http->get(
            'api/payments',
            $this->resolvePayload($filters)
        );

        return Payment::fromArrayCollection($response->getData());
    }

    /**
     * Processar ou criar um pagamento.
     *
     * @param ProcessPaymentRequest|array<string, mixed> $request
     *
     * @return Payment
     */
    public function process($request): Payment
    {
        $response = $this->http->post(
            'api/payments/process',
            $this->resolvePayload($request)
        );

        return Payment::fromArray($response->getData());
    }

    /**
     * Consultar um pagamento pelo ID.
     *
     * @param int|string $paymentId
     *
     * @return Payment
     */
    public function find($paymentId): Payment
    {
        $response = $this->http->get('api/payments/' . $paymentId);

        return Payment::fromArray($response->getData());
    }

    /**
     * Cancelar um pagamento.
     *
     * @param int|string $paymentId
     *
     * @return Payment
     */
    public function cancel($paymentId): Payment
    {
        $response = $this->http->delete('api/payments/' . $paymentId);

        return Payment::fromArray($response->getData());
    }

    /**
     * Estornar um pagamento.
     *
     * @param int|string $paymentId
     * @param RefundPaymentRequest|array<string, mixed>|null $request
     *
     * @return Refund
     */
    public function refund($paymentId, $request = null): Refund
    {
        $response = $this->http->put(
            'api/payments/' . $paymentId . '/refund',
            $this->resolvePayload($request !== null ? $request : array())
        );

        return Refund::fromArray($response->getData());
    }

    /**
     * Consultar estorno pelo ID.
     *
     * @param string $refundId
     *
     * @return Refund
     */
    public function findRefund($refundId): Refund
    {
        $response = $this->http->get('api/payments/refunds/' . $refundId);

        return Refund::fromArray($response->getData());
    }

    /**
     * Marcar pagamento como pago (apenas Sandbox/Desenvolvimento).
     *
     * @param int|string $paymentId
     *
     * @return Payment
     */
    public function markAsPaidDev($paymentId): Payment
    {
        $response = $this->http->post(
            'api/payments/' . $paymentId . '/mark-as-paid-dev'
        );

        return Payment::fromArray($response->getData());
    }

}
