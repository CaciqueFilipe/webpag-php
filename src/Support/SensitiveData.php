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
     * Remove de um texto livre (mensagens de erro de rede) as query strings e credenciais de URLs.
     *
     * "... for https://api/x?cpf_cnpj=123" → "... for https://api/x?[query omitida]"
     * "http://user:pass@proxy:3128"        → "http://****@proxy:3128"
     *
     * @param string $text
     *
     * @return string
     */
    public static function redactUrls($text)
    {
        $text = (string) $text;
        $text = preg_replace('~\b([a-z][a-z0-9+.-]*://)[^\s/@"\'<>]+@~i', '$1****@', $text);

        return preg_replace('~\b([a-z][a-z0-9+.-]*://[^\s?#"\'<>]+)\?[^\s#"\'<>]*~i', '$1?[query omitida]', $text);
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
