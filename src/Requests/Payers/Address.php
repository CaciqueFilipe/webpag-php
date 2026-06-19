<?php

namespace WebPag\Requests\Payers;

use WebPag\Contracts\RequestPayload;
use WebPag\Support\ArrayHelper;

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
}
