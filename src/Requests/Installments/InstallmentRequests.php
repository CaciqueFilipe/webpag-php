<?php

namespace WebPag\Requests\Installments;

use WebPag\Contracts\RequestPayload;
use WebPag\Support\ArrayHelper;

class CreateInstallmentRequest implements RequestPayload
{
    /** @var int Valor total em centavos */
    public $amount;

    /** @var string Data do primeiro pagamento (Y-m-d) */
    public $firstDate;

    /** @var int Número de parcelas */
    public $numberInstallments;

    /** @var int ID do pagador */
    public $payer;

    /** @var bool|null Aceitar pagamento após vencimento */
    public $acceptAfterDueDate;

    /**
     * @return array<string, mixed>
     */
    public function toArray()
    {
        return ArrayHelper::filterNull([
            'amount' => $this->amount,
            'first_date' => $this->firstDate,
            'number_installments' => $this->numberInstallments,
            'payer' => $this->payer,
            'accept_after_due_date' => $this->acceptAfterDueDate,
        ]);
    }
}

class ListInstallmentsRequest implements RequestPayload
{
    /** @var int|null */
    public $payerId;

    /** @var int|null */
    public $page;

    /** @var int|null */
    public $perPage;

    /**
     * @return array<string, mixed>
     */
    public function toArray()
    {
        return ArrayHelper::filterNull([
            'payer_id' => $this->payerId,
            'page' => $this->page,
            'per_page' => $this->perPage,
        ]);
    }
}
