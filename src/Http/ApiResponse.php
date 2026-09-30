<?php

namespace WebPag\Http;

use ArrayAccess;
use JsonSerializable;
use Psr\Http\Message\ResponseInterface;

class ApiResponse implements ArrayAccess, JsonSerializable
{
    /** @var array<string, mixed> */
    private $body;

    /** @var int */
    private $statusCode;

    /**
     * @param array<string, mixed> $body
     * @param int                  $statusCode
     */
    public function __construct(array $body, $statusCode)
    {
        $this->body = $body;
        $this->statusCode = $statusCode;
    }

    /**
     * Cria a partir do corpo cru (JSON) e do status HTTP.
     *
     * @param string $contents
     * @param int    $statusCode
     *
     * @return self
     */
    public static function fromRaw($contents, $statusCode)
    {
        $contents = (string) $contents;
        $decoded = json_decode($contents, true);

        if (! is_array($decoded)) {
            $decoded = ['raw' => $contents];
        }

        return new self($decoded, (int) $statusCode);
    }

    /**
     * Cria a partir de uma resposta PSR-7 (exige psr/http-message instalado no projeto).
     *
     * @param ResponseInterface $response
     *
     * @return self
     */
    public static function fromResponse(ResponseInterface $response)
    {
        return self::fromRaw((string) $response->getBody(), $response->getStatusCode());
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray()
    {
        return $this->body;
    }

    /**
     * Retorna o conteúdo da chave "data" quando presente, ou o corpo completo.
     *
     * @return mixed
     */
    public function getData()
    {
        return array_key_exists('data', $this->body) ? $this->body['data'] : $this->body;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return array_key_exists($key, $this->body) ? $this->body[$key] : $default;
    }

    /**
     * @param mixed $offset
     *
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->body);
    }

    /**
     * @param mixed $offset
     *
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return array_key_exists($offset, $this->body) ? $this->body[$offset] : null;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        if ($offset === null) {
            $this->body[] = $value;
        } else {
            $this->body[$offset] = $value;
        }
    }

    /**
     * @param mixed $offset
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        unset($this->body[$offset]);
    }

    /**
     * @return array<string, mixed>
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->body;
    }
}
