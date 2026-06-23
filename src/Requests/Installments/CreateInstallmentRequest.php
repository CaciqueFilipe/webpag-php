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

    /**
     * Cria uma instância a partir de um array associativo.
     *
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        $request = new self();

        $request->amount = isset($data['amount'])
            ? (int) $data['amount']
            : 0;
        $request->firstDate = $data['first_date'] ?? '';
        $request->numberInstallments = isset($data['number_installments'])
            ? (int) $data['number_installments']
            : 0;
        $request->payer = isset($data['payer'])
            ? (int) $data['payer']
            : 0;

        // Garante que mantém true, false ou null sem forçar um cast incorreto
        $request->acceptAfterDueDate = isset($data['accept_after_due_date'])
            ? (bool) $data['accept_after_due_date']
            : null;

        return $request;
    }
}
