<?php

namespace WebPag\Requests\Business;

use WebPag\Contracts\RequestPayload;

class AuthenticateRequest implements RequestPayload
{
    /** @var string */
    public $email;

    /** @var string */
    public $password;

    /**
     * @return array<string, mixed>
     */
    public function toArray()
    {
        return [
            'email' => $this->email,
            'password' => $this->password,
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
        $request->email    = $data['email'] ?? '';
        $request->password = $data['password'] ?? '';

        return $request;
    }
}
