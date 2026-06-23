<?php

namespace WebPag\Requests\Payments;

use WebPag\Contracts\RequestPayload;
use WebPag\Support\ArrayHelper;

class ListPaymentsRequest implements RequestPayload
{
    /** @var int|null */
    public $paymentId;

    /** @var int|null */
    public $payerId;

    /** @var int|null */
    public $page;

    /** @var int|null */
    public $perPage;

    /** @var string|null Y-m-d */
    public $createdAtStart;

    /** @var string|null Y-m-d */
    public $createdAtEnd;

    /** @var string|null Y-m-d */
    public $paidAtStart;

    /** @var string|null Y-m-d */
    public $paidAtEnd;

    /** @var int|null */
    public $status;

    /** @var string|null credit, debit, pix */
    public $method;

    /** @var bool|null */
    public $active;

    /** @var int|null */
    public $recurrenceCode;

    /** @var bool|null */
    public $isRecurrent;

    /** @var string|null */
    public $orderId;

    /** @var string|null */
    public $txid;

    /**
     * @return array<string, mixed>
     */
    public function toArray()
    {
        return ArrayHelper::filterNull([
            'payment_id' => $this->paymentId,
            'payer_id' => $this->payerId,
            'page' => $this->page,
            'per_page' => $this->perPage,
            'created_at_start' => $this->createdAtStart,
            'created_at_end' => $this->createdAtEnd,
            'paid_at_start' => $this->paidAtStart,
            'paid_at_end' => $this->paidAtEnd,
            'status' => $this->status,
            'method' => $this->method,
            'active' => $this->active,
            'recurrence_code' => $this->recurrenceCode,
            'is_recurrent' => $this->isRecurrent,
            'order_id' => $this->orderId,
            'txid' => $this->txid,
        ]);
    }

    /**
     * Cria uma instância a partir de um array associativo.
     *
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        $request = new self();

        $request->paymentId = isset($data['payment_id'])
            ? (int) $data['payment_id']
            : null;
        $request->payerId = isset($data['payer_id'])
            ? (int) $data['payer_id']
            : null;
        $request->page = isset($data['page'])
            ? (int) $data['page']
            : null;
        $request->perPage = isset($data['per_page'])
            ? (int) $data['per_page']
            : null;
        $request->createdAtStart = $data['created_at_start'] ?? null;
        $request->createdAtEnd = $data['created_at_end'] ?? null;
        $request->paidAtStart = $data['paid_at_start'] ?? null;
        $request->paidAtEnd = $data['paid_at_end'] ?? null;
        $request->status = isset($data['status'])
            ? (int) $data['status']
            : null;
        $request->method = $data['method'] ?? null;
        $request->active = isset($data['active'])
            ? (bool) $data['active']
            : null;
        $request->recurrenceCode = isset($data['recurrence_code'])
            ? (int) $data['recurrence_code']
            : null;
        $request->isRecurrent = isset($data['is_recurrent'])
            ? (bool) $data['is_recurrent']
            : null;
        $request->orderId = $data['order_id'] ?? null;
        $request->txid = $data['txid'] ?? null;

        return $request;
    }
}
