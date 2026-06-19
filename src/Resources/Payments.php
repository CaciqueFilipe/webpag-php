<?php

namespace WebPag\Resources;

use WebPag\Http\ApiResponse;
use WebPag\Requests\Payments\ListPaymentsRequest;
use WebPag\Requests\Payments\ProcessPaymentRequest;
use WebPag\Requests\Payments\RefundPaymentRequest;

class Payments extends AbstractResource
{
    /**
     * Listar os pagamentos cadastrados.
     *
     * @param ListPaymentsRequest|array<string, mixed>|null $filters
     *
     * @return ApiResponse
     */
    public function list($filters = null)
    {
        return $this->http->get('api/payments', $this->resolvePayload($filters));
    }

    /**
     * Processar ou criar um pagamento.
     *
     * @param ProcessPaymentRequest|array<string, mixed> $request
     *
     * @return ApiResponse
     */
    public function process($request)
    {
        return $this->http->post('api/payments/process', $this->resolvePayload($request));
    }

    /**
     * Consultar um pagamento pelo ID.
     *
     * @param int|string $paymentId
     *
     * @return ApiResponse
     */
    public function find($paymentId)
    {
        return $this->http->get('api/payments/' . $paymentId);
    }

    /**
     * Cancelar um pagamento.
     *
     * @param int|string $paymentId
     *
     * @return ApiResponse
     */
    public function cancel($paymentId)
    {
        return $this->http->delete('api/payments/' . $paymentId);
    }

    /**
     * Estornar um pagamento.
     *
     * @param int|string                                     $paymentId
     * @param RefundPaymentRequest|array<string, mixed>|null $request
     *
     * @return ApiResponse
     */
    public function refund($paymentId, $request = null)
    {
        return $this->http->put(
            'api/payments/' . $paymentId . '/refund',
            $this->resolvePayload($request !== null ? $request : array())
        );
    }

    /**
     * Consultar estorno pelo ID.
     *
     * @param string $refundId
     *
     * @return ApiResponse
     */
    public function findRefund($refundId)
    {
        return $this->http->get('api/payments/refunds/' . $refundId);
    }

    /**
     * Marcar pagamento como pago (apenas Sandbox/Desenvolvimento).
     *
     * @param int|string $paymentId
     *
     * @return ApiResponse
     */
    public function markAsPaidDev($paymentId)
    {
        return $this->http->post('api/payments/' . $paymentId . '/mark-as-paid-dev');
    }

}
