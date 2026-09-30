<?php

namespace WebPag\Http\Transport;

use WebPag\Exceptions\WebPagException;
use WebPag\Support\SensitiveData;

/**
 * Falha de comunicação: DNS, conexão, TLS, timeout ou resposta acima do limite.
 *
 * A mensagem é sempre higienizada: mensagens de bibliotecas HTTP costumam incluir a URL
 * completa (com filtros como CPF/e-mail na query) e credenciais de proxy.
 */
class TransportException extends WebPagException
{
    /**
     * @param string          $message
     * @param int             $code
     * @param \Throwable|null $previous
     */
    public function __construct($message = '', $code = 0, $previous = null)
    {
        parent::__construct(SensitiveData::redactUrls($message), $code, $previous);
    }
}
