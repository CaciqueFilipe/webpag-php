<?php

namespace WebPag\Http\Transport;

/**
 * Resposta HTTP crua devolvida por um TransportInterface.
 */
final class TransportResponse
{
    /** @var int */
    private $statusCode;

    /** @var array<string, string> Nomes em minúsculas */
    private $headers;

    /** @var string */
    private $body;

    /**
     * @param int                   $statusCode
     * @param array<string, string> $headers
     * @param string                $body
     */
    public function __construct($statusCode, array $headers, $body)
    {
        $this->statusCode = (int) $statusCode;
        $this->headers = array_change_key_case($headers, CASE_LOWER);
        $this->body = (string) $body;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param string $name Case-insensitive
     *
     * @return string Vazio quando o header não existe
     */
    public function getHeaderLine($name)
    {
        $name = strtolower($name);

        return isset($this->headers[$name]) ? $this->headers[$name] : '';
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }
}
