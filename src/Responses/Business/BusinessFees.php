<?php

namespace WebPag\Responses\Business;

use WebPag\Contracts\ResponsePayload;

class BusinessFees implements ResponsePayload
{
    /** @var float|null Taxa de serviço (valor ou percentual, conforme serviceFeeType) */
    public $serviceFee;

    /** @var string|null Tipo da taxa de serviço, ex: "F" */
    public $serviceFeeType;

    /** @var float|null Taxa de transferência expressa */
    public $transferExpressFee;

    /** @var int|null Limite de parcelas do crédito */
    public $creditInstallmentLimit;

    /** @var float|null Taxa do crédito à vista (%) */
    public $credit1xFee;

    /** @var float|null Taxa do crédito até 6x (%) */
    public $credit6xFee;

    /** @var float|null Taxa do crédito até 12x (%) */
    public $credit12xFee;

    /** @var float|null Taxa do crédito até 18x (%) */
    public $credit18xFee;

    /** @var float|null Taxa do débito (%) */
    public $debitFee;

    /** @var float|null Taxa fixa do PIX */
    public $pixFee;

    /** @var float|null Taxa percentual do PIX (%) */
    public $pixFeePercentage;

    /** @var float|null Taxa fixa do PIX automático */
    public $pixAutomaticFee;

    /** @var float|null Taxa percentual do PIX automático (%) */
    public $pixAutomaticFeePercentage;

    /** @var bool|null Boleto habilitado */
    public $boletoEnabled;

    /** @var float|null Taxa do boleto */
    public $boletoFee;

    /** @var float|null Juros do boleto (%) */
    public $boletoInterest;

    /** @var float|null Multa do boleto (%) */
    public $boletoFine;

    /** @var float|null Taxa do parcelamento (%) */
    public $installmentPlanFee;

    /** @var float|null Taxa fixa do parcelamento */
    public $installmentPlanFeeFixed;

    /** @var float|null Taxa de antecipação (%) */
    public $anticipationFee;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        $instance = new self();
        $instance->serviceFee = isset($data['service_fee']) ? (float)$data['service_fee'] : null;
        $instance->serviceFeeType = $data['service_fee_type'] ?? null;
        $instance->transferExpressFee = isset($data['transfer_express_fee']) ? (float)$data['transfer_express_fee'] : null;
        $instance->creditInstallmentLimit = isset($data['credit_installment_limit']) ? (int)$data['credit_installment_limit'] : null;
        $instance->credit1xFee = isset($data['credit_1x_fee']) ? (float)$data['credit_1x_fee'] : null;
        $instance->credit6xFee = isset($data['credit_6x_fee']) ? (float)$data['credit_6x_fee'] : null;
        $instance->credit12xFee = isset($data['credit_12x_fee']) ? (float)$data['credit_12x_fee'] : null;
        $instance->credit18xFee = isset($data['credit_18x_fee']) ? (float)$data['credit_18x_fee'] : null;
        $instance->debitFee = isset($data['debit_fee']) ? (float)$data['debit_fee'] : null;
        $instance->pixFee = isset($data['pix_fee']) ? (float)$data['pix_fee'] : null;
        $instance->pixFeePercentage = isset($data['pix_fee_percentage']) ? (float)$data['pix_fee_percentage'] : null;
        $instance->pixAutomaticFee = isset($data['pix_automatic_fee']) ? (float)$data['pix_automatic_fee'] : null;
        $instance->pixAutomaticFeePercentage = isset($data['pix_automatic_fee_percentage']) ? (float)$data['pix_automatic_fee_percentage'] : null;
        $instance->boletoEnabled = isset($data['boleto_enabled']) ? (bool)$data['boleto_enabled'] : null;
        $instance->boletoFee = isset($data['boleto_fee']) ? (float)$data['boleto_fee'] : null;
        $instance->boletoInterest = isset($data['boleto_interest']) ? (float)$data['boleto_interest'] : null;
        $instance->boletoFine = isset($data['boleto_fine']) ? (float)$data['boleto_fine'] : null;
        $instance->installmentPlanFee = isset($data['installment_plan_fee']) ? (float)$data['installment_plan_fee'] : null;
        $instance->installmentPlanFeeFixed = isset($data['installment_plan_fee_fixed']) ? (float)$data['installment_plan_fee_fixed'] : null;
        $instance->anticipationFee = isset($data['anticipation_fee']) ? (float)$data['anticipation_fee'] : null;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return array_filter([
            'service_fee' => $this->serviceFee,
            'service_fee_type' => $this->serviceFeeType,
            'transfer_express_fee' => $this->transferExpressFee,
            'credit_installment_limit' => $this->creditInstallmentLimit,
            'credit_1x_fee' => $this->credit1xFee,
            'credit_6x_fee' => $this->credit6xFee,
            'credit_12x_fee' => $this->credit12xFee,
            'credit_18x_fee' => $this->credit18xFee,
            'debit_fee' => $this->debitFee,
            'pix_fee' => $this->pixFee,
            'pix_fee_percentage' => $this->pixFeePercentage,
            'pix_automatic_fee' => $this->pixAutomaticFee,
            'pix_automatic_fee_percentage' => $this->pixAutomaticFeePercentage,
            'boleto_enabled' => $this->boletoEnabled,
            'boleto_fee' => $this->boletoFee,
            'boleto_interest' => $this->boletoInterest,
            'boleto_fine' => $this->boletoFine,
            'installment_plan_fee' => $this->installmentPlanFee,
            'installment_plan_fee_fixed' => $this->installmentPlanFeeFixed,
            'anticipation_fee' => $this->anticipationFee,
        ], function ($value) {
            return $value !== null;
        });
    }
}
