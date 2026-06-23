<?php

namespace WebPag\Contracts;

interface ResponsePayload
{
    /**
     * Converte o objeto de resposta para um array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;

    /**
     * Cria uma instância da classe a partir de um array associativo da API.
     *
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data);
}
