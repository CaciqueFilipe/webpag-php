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
}
