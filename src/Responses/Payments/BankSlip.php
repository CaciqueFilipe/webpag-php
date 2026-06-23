<?php

namespace WebPag\Responses\Payments;

use WebPag\Contracts\ResponsePayload;

class BankSlip implements ResponsePayload
{
    /** @var int|null */
    public $id;

    /** @var string|null */
    public $digitableLine;

    /** @var int|null */
    public $sequentialCode;

    /** @var string|null */
    public $ourNumber;

    /** @var string|null */
    public $barCode;

    /** @var string|null (Y-m-d) */
    public $dueDate;

    /** @var string|null (Y-m-d) */
    public $issueDate;

    /** @var int|null (em centavos) */
    public $amount;

    /** @var int|null (em centavos) */
    public $interest;

    /** @var int|null (em centavos) */
    public $fine;

    /** @var bool|null */
    public $acceptAfterDueDate;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        $instance = new self();
        $instance->id                 = isset($data['id']) ? (int)$data['id'] : null;
        $instance->digitableLine      = $data['linha_digitavel'] ?? null;
        $instance->sequentialCode     = isset($data['sequencial_code'])
            ? (int)$data['sequencial_code']
            : null;
        $instance->ourNumber          = $data['nosso_numero'] ?? null;
        $instance->barCode            = $data['codigo_barras'] ?? null;
        $instance->dueDate            = $data['due_date'] ?? null;
        $instance->issueDate          = $data['issue_date'] ?? null;
        $instance->amount             = isset($data['amount']) ? (int)$data['amount'] : null;
        $instance->interest           = isset($data['interest']) ? (int)$data['interest'] : null;
        $instance->fine               = isset($data['fine']) ? (int)$data['fine'] : null;
        $instance->acceptAfterDueDate = isset($data['accept_after_due_date'])
            ? (bool)$data['accept_after_due_date']
            : null;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return [
            'id'                      => $this->id,
            'linha_digitavel'         => $this->digitableLine,
            'sequencial_code'         => $this->sequentialCode,
            'nosso_numero'            => $this->ourNumber,
            'codigo_barras'           => $this->barCode,
            'due_date'                => $this->dueDate,
            'issue_date'              => $this->issueDate,
            'amount'                  => $this->amount,
            'interest'                => $this->interest,
            'fine'                    => $this->fine,
            'accept_after_due_date'   => $this->acceptAfterDueDate,
        ];
    }
}