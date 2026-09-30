<?php

namespace WebPag\Support;

/**
 * Máscaras para dados sensíveis em saídas de debug (var_dump, print_r, dd, logs).
 */
final class SensitiveData
{
    /**
     * Mascara um segredo (token, card_token) mantendo só os 4 últimos caracteres.
     *
     * @param string|null $secret
     *
     * @return string
     */
    public static function mask($secret)
    {
        $secret = (string) $secret;

        if ($secret === '') {
            return '';
        }

        return strlen($secret) <= 8 ? '****' : '****' . substr($secret, -4);
    }

    /**
     * Mascara número de cartão (PAN) mantendo só os 4 últimos dígitos.
     *
     * @param string|null $number
     *
     * @return string|null
     */
    public static function maskCardNumber($number)
    {
        if ($number === null) {
            return null;
        }

        $digits = preg_replace('/\D/', '', (string) $number);

        return strlen($digits) <= 4 ? '****' : str_repeat('*', strlen($digits) - 4) . substr($digits, -4);
    }
}
