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
