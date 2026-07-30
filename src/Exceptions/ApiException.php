<?php

namespace WebPag\Exceptions;

class ApiException extends WebPagException
{
    /** @var int */
    private $statusCode;

    /** @var array<string, mixed>|null */
    private $responseBody;

    /**
     * @param string                        $message
     * @param int                           $statusCode
     * @param array<string, mixed>|null     $responseBody
     * @param \Throwable|null               $previous
     */
    public function __construct($message, $statusCode = 0, $responseBody = null, $previous = null)
    {
        parent::__construct($message, $statusCode, $previous);
        $this->statusCode = $statusCode;
        $this->responseBody = $responseBody;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getResponseBody()
    {
        return $this->responseBody;
    }

    /**
     * @return string|null
     */
    public function getErrorMessage()
    {
        if (! is_array($this->responseBody)) {
            return null;
        }

        if (isset($this->responseBody['message'])) {
            return (string) $this->responseBody['message'];
        }

        if (isset($this->responseBody['error'])) {
            return is_string($this->responseBody['error'])
                ? $this->responseBody['error']
                : json_encode($this->responseBody['error']);
        }

        return null;
    }

    /**
     * Retorna o código de erro interno retornado pela API WebPag.
     *
     * @return int|null
     */
    public function getErrorCode()
    {
        if (is_array($this->responseBody) && isset($this->responseBody['error_code'])) {
            return (int) $this->responseBody['error_code'];
        }

        return null;
    }

    /**
     * Retorna o erro anterior/detalhado (error_previous).
     * Se for uma string JSON válida (ex: erros da adquirente/banco),
     * decodifica automaticamente para array/object.
     *
     * @param bool $asArray Define se retorna como array associativo (true) ou stdClass (false)
     * @return array|string|null
     */
    public function getErrorPrevious($asArray = true)
    {
        if (! is_array($this->responseBody) || ! isset($this->responseBody['error_previous'])) {
            return null;
        }

        $previous = $this->responseBody['error_previous'];

        if (is_string($previous)) {
            $decoded = json_decode($previous, $asArray);
            return json_last_error() === JSON_ERROR_NONE ? $decoded : $previous;
        }

        return $previous;
    }

    /**
     * Retorna o trace do erro retornado pela API, se houver.
     *
     * @return array|null
     */
    public function getErrorTrace()
    {
        if (is_array($this->responseBody) && isset($this->responseBody['error_trace'])) {
            return (array) $this->responseBody['error_trace'];
        }

        return null;
    }
}
