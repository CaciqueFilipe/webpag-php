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
}
