<?php

namespace WebPag\Requests\Payers;

use WebPag\Support\ArrayHelper;
use WebPag\Contracts\RequestPayload;

class Address implements RequestPayload
{
    /** @var string|null */
    public $zipCode;

    /** @var string|null */
    public $street;

    /** @var string|null */
    public $number;

    /** @var string|null */
    public $district;

    /** @var string|null */
    public $city;

    /** @var string|null */
    public $state;

    /** @var string|null */
    public $country;

    /**
     * @return array<string, mixed>
     */
    public function toArray()
    {
        return ArrayHelper::filterNull([
            'zip_code' => $this->zipCode,
            'street' => $this->street,
            'number' => $this->number,
            'district' => $this->district,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
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
        $address = new self();

        $address->zipCode  = $data['zip_code'] ?? null;
        $address->street   = $data['street'] ?? null;
        $address->number   = $data['number'] ?? null;
        $address->district = $data['district'] ?? null;
        $address->city     = $data['city'] ?? null;
        $address->state    = $data['state'] ?? null;
        $address->country  = $data['country'] ?? null;

        return $address;
    }
}
