<?php

namespace WebPag\Http\Transport;

/**
 * Envia uma requisição HTTP e devolve a resposta crua.
 *
 * O HttpClient cuida de autenticação, retry, logs e erros; o transporte só faz a chamada.
 * Implementações NUNCA devem seguir redirecionamentos nem lançar exceção por status >= 400.
 */
interface TransportInterface
{
    /**
     * @param string               $method  Método HTTP em maiúsculas
     * @param string               $uri     Caminho relativo à URL base (ex: "api/payments")
     * @param array<string, mixed> $options Chaves aceitas:
     *                                      - headers (array<string, string>)
     *                                      - query (array<string, mixed>)
     *                                      - json (mixed; ausente = sem corpo)
     *                                      - timeout (int, segundos)
     *                                      - connect_timeout (int, segundos)
     *
     * @return TransportResponse
     *
     * @throws TransportException Em falha de comunicação (DNS, conexão, TLS, timeout...)
     */
    public function send($method, $uri, array $options);
}
