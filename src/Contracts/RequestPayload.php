<?php

namespace WebPag\Contracts;

interface RequestPayload
{
    /**
     * @return array<string, mixed>
     */
    public function toArray();

    /**
     * Cria uma instância da classe a partir de um array associativo.
     *
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data);
}
