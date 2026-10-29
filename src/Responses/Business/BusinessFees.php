<?php

namespace WebPag\Responses\Business;

use WebPag\Contracts\ResponsePayload;

class BusinessFees implements ResponsePayload
{
    /** @var float|null Taxa de serviço (valor ou percentual, conforme serviceFeeType) */
    public $serviceFee;

    /** @var string|null Tipo da taxa de serviço, ex: "F" */
    public $serviceFeeType;

    /** @var float|null Taxa do cartão de crédito à vista (%) */
    public $credit1xFee;

    /** @var float|null Taxa do PIX */
    public $pixFee;

    /** @var float|null Taxa do boleto */
    public $boletoFee;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        $instance = new self();
        $instance->serviceFee = isset($data['service_fee']) ? (float)$data['service_fee'] : null;
        $instance->serviceFeeType = $data['service_fee_type'] ?? null;
        $instance->credit1xFee = isset($data['credit_1x_fee']) ? (float)$data['credit_1x_fee'] : null;
        $instance->pixFee = isset($data['pix_fee']) ? (float)$data['pix_fee'] : null;
        $instance->boletoFee = isset($data['boleto_fee']) ? (float)$data['boleto_fee'] : null;

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
            'credit_1x_fee' => $this->credit1xFee,
            'pix_fee' => $this->pixFee,
            'boleto_fee' => $this->boletoFee,
        ], function ($value) {
            return $value !== null;
        });
    }
}
