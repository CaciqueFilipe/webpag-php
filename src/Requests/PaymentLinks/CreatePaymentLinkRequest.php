<?php

namespace WebPag\Requests\PaymentLinks;

use WebPag\Contracts\RequestPayload;
use WebPag\Support\ArrayHelper;

class CreatePaymentLinkRequest implements RequestPayload
{
    /** @var string[] Métodos aceitos: credit_card, pix, bankslip */
    public $acceptedMethods;

    /** @var string */
    public $name;

    /** @var string */
    public $description;

    /** @var int Valor em centavos */
    public $value;

    /** @var string Validade (Y-m-d ou Y-m-d H:i:s) */
    public $validity;

    /** @var int Dias de validade do boleto */
    public $validityBoleto;

    /** @var int Número máximo de parcelas */
    public $numberInstallments;

    /** @var int|null */
    public $acceptAfterDueDate;

    /** @var bool|null */
    public $isRecurrence;

    /** @var string|null */
    public $frequency;

    /** @var int|null */
    public $recurrenceTime;

    /** @var int|null */
    public $payerId;

    /** @var string|null @deprecated */
    public $notificationUrl;

    /** @var string|null */
    public $returnUrl;

    /** @var bool|null */
    public $expireAfterUsage;

    /**
     * @return array<string, mixed>
     */
    public function toArray()
    {
        return ArrayHelper::filterNull([
            'accepted_methods' => $this->acceptedMethods,
            'name' => $this->name,
            'description' => $this->description,
            'value' => $this->value,
            'validity' => $this->validity,
            'validity_boleto' => $this->validityBoleto,
            'number_installments' => $this->numberInstallments,
            'accept_after_due_date' => $this->acceptAfterDueDate,
            'is_recurrence' => $this->isRecurrence,
            'frequency' => $this->frequency,
            'recurrence_time' => $this->recurrenceTime,
            'payer_id' => $this->payerId,
            'notification_url' => $this->notificationUrl,
            'return_url' => $this->returnUrl,
            'expire_after_usage' => $this->expireAfterUsage,
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

        // Garante que se vier nulo ou não definido, o tipo array seja respeitado
        $request->acceptedMethods = isset($data['accepted_methods']) &&
            is_array($data['accepted_methods'])
            ? $data['accepted_methods']
            : [];

        $request->name = $data['name'] ?? '';
        $request->description = $data['description'] ?? '';
        $request->value = isset($data['value']) ? (int) $data['value'] : 0;
        $request->validity = $data['validity'] ?? '';
        $request->validityBoleto = isset($data['validity_boleto'])
            ? (int) $data['validity_boleto']
            : 0;
        $request->numberInstallments = isset($data['number_installments'])
            ? (int) $data['number_installments']
            : 0;
        $request->acceptAfterDueDate = isset($data['accept_after_due_date'])
            ? (int) $data['accept_after_due_date']
            : null;

        // Mantém booleanos flexíveis aceitando true, false ou null
        $request->isRecurrence = isset($data['is_recurrence'])
            ? (bool) $data['is_recurrence']
            : null;
        $request->frequency = $data['frequency'] ?? null;
        $request->recurrenceTime = isset($data['recurrence_time'])
            ? (int) $data['recurrence_time']
            : null;
        $request->payerId = isset($data['payer_id'])
            ? (int) $data['payer_id']
            : null;
        $request->notificationUrl = $data['notification_url'] ?? null;
        $request->returnUrl = $data['return_url'] ?? null;
        $request->expireAfterUsage = isset($data['expire_after_usage'])
            ? (bool) $data['expire_after_usage']
            : null;

        return $request;
    }
}
