<?php

namespace WebPag\Requests\Payers;

use WebPag\Contracts\RequestPayload;
use WebPag\Support\ArrayHelper;
use WebPag\Support\SensitiveData;

class SaveCreditCardRequest implements RequestPayload
{
    /** @var string|null Token gerado no frontend */
    public $cardToken;

    /** @var string|null */
    public $number;

    /** @var string|null */
    public $name;

    /** @var string|null MM */
    public $expirationMonth;

    /** @var string|null YYYY */
    public $expirationYear;

    /** @var string|null */
    public $securityCode;

    /**
     * @return array<string, mixed>
     */
    public function toArray()
    {
        return ArrayHelper::filterNull([
            'card_token' => $this->cardToken,
            'number' => $this->number,
            'name' => $this->name,
            'expiration_month' => $this->expirationMonth,
            'expiration_year' => $this->expirationYear,
            'security_code' => $this->securityCode,
        ]);
    }

    /**
     * Evita que var_dump/print_r/dd exponham número completo, CVV e token (PCI-DSS).
     *
     * @return array<string, mixed>
     */
    public function __debugInfo()
    {
        return [
            'cardToken' => $this->cardToken !== null ? SensitiveData::mask($this->cardToken) : null,
            'number' => SensitiveData::maskCardNumber($this->number),
            'name' => $this->name,
            'expirationMonth' => $this->expirationMonth,
            'expirationYear' => $this->expirationYear,
            'securityCode' => $this->securityCode !== null ? '***' : null,
        ];
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

        $request->cardToken = $data['card_token'] ?? null;
        $request->number = $data['number'] ?? null;
        $request->name = $data['name'] ?? null;
        $request->expirationMonth = $data['expiration_month'] ?? null;
        $request->expirationYear = $data['expiration_year'] ?? null;
        $request->securityCode = $data['security_code'] ?? null;

        return $request;
    }
}
