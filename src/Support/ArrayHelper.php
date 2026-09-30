<?php

namespace WebPag\Support;

final class ArrayHelper
{
    /**
     * Remove chaves com valor null (preserva false e 0).
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public static function filterNull(array $data)
    {
        return array_filter($data, function ($value) {
            return $value !== null;
        });
    }

    /**
     * Mantém só os itens que são arrays (objetos JSON), reindexando.
     *
     * Protege os DTOs contra listas malformadas vindas da API ou de webhooks, como
     * "transactions": [1, "x", null], que causariam TypeError em fromArray().
     *
     * @param array<mixed> $items
     *
     * @return array<int, array<string, mixed>>
     */
    public static function onlyArrays(array $items)
    {
        return array_values(array_filter($items, 'is_array'));
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public static function filterEmpty(array $data)
    {
        return array_filter($data, function ($value) {
            if ($value === null) {
                return false;
            }

            if (is_array($value)) {
                return count($value) > 0;
            }

            return true;
        });
    }
}
