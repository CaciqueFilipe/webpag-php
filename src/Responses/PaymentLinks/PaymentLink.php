<?php

namespace WebPag\Responses\PaymentLinks;

use WebPag\Contracts\ResponsePayload;

class PaymentLink implements ResponsePayload
{
    /** @var int|null */
    public $id;

    /** @var int|null */
    public $payerId;

    /** @var bool|null */
    public $expireAfterUsage;

    /** @var string|null */
    public $name;

    /** @var string|null */
    public $description;

    /** @var int|null */
    public $value;

    /** @var bool|null */
    public $isRecurrence;

    /** @var string|null */
    public $frequency;

    /** @var bool|null */
    public $pixEnabled;

    /** @var bool|null */
    public $creditEnabled;

    /** @var bool|null */
    public $boletoEnabled;

    /** @var string|null */
    public $dueDateBoleto;

    /** @var bool|null */
    public $acceptAfterDueDate;

    /** @var int|null */
    public $numberInstallments;

    /** @var int|null */
    public $recurrenceTime;

    /** @var string|null */
    public $url;

    /** @var string|null */
    public $returnUrl;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $instance = new self();
        $instance->id = isset($data['id']) ? (int)$data['id'] : null;
        $instance->payerId = isset($data['payer_id']) ? (int)$data['payer_id'] : null;
        $instance->expireAfterUsage = isset($data['expire_after_usage']) ? (bool)$data['expire_after_usage'] : null;
        $instance->name = $data['name'] ?? null;
        $instance->description = $data['description'] ?? null;
        $instance->value = isset($data['value']) ? (int)$data['value'] : null;
        $instance->isRecurrence = isset($data['is_recurrence']) ? (bool)$data['is_recurrence'] : null;
        $instance->frequency = $data['frequency'] ?? null;
        $instance->pixEnabled = isset($data['pix_enabled']) ? (bool)$data['pix_enabled'] : null;
        $instance->creditEnabled = isset($data['credit_enabled']) ? (bool)$data['credit_enabled'] : null;
        $instance->boletoEnabled = isset($data['boleto_enabled']) ? (bool)$data['boleto_enabled'] : null;
        $instance->dueDateBoleto = $data['due_date_boleto'] ?? null;
        $instance->acceptAfterDueDate = isset($data['accept_after_due_date']) ? (bool)$data['accept_after_due_date'] : null;
        $instance->numberInstallments = isset($data['number_installments']) ? (int)$data['number_installments'] : null;
        $instance->recurrenceTime = isset($data['recurrence_time']) ? (int)$data['recurrence_time'] : null;
        $instance->url = $data['url'] ?? null;
        $instance->returnUrl = $data['return_url'] ?? null;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'payer_id' => $this->payerId,
            'expire_after_usage' => $this->expireAfterUsage,
            'name' => $this->name,
            'description' => $this->description,
            'value' => $this->value,
            'is_recurrence' => $this->isRecurrence,
            'frequency' => $this->frequency,
            'pix_enabled' => $this->pixEnabled,
            'credit_enabled' => $this->creditEnabled,
            'boleto_enabled' => $this->boletoEnabled,
            'due_date_boleto' => $this->dueDateBoleto,
            'accept_after_due_date' => $this->acceptAfterDueDate,
            'number_installments' => $this->numberInstallments,
            'recurrence_time' => $this->recurrenceTime,
            'url' => $this->url,
            'return_url' => $this->returnUrl,
        ], function ($value) {
            return $value !== null;
        });
    }

    /**
     * Cria uma coleção de instâncias a partir de um array de dados da API.
     *
     * @param array<array<string, mixed>> $collection
     * @return self[]
     */
    public static function fromArrayCollection(array $collection): array
    {
        return array_map(function ($data) {
            return self::fromArray($data);
        }, $collection);
    }
}