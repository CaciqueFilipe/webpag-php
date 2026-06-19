<?php

namespace WebPag;

class Configuration
{
    const DEFAULT_BASE_URL = 'https://api.webpag.com.br';

    /** @var string */
    private $apiToken;

    /** @var string */
    private $baseUrl;

    /** @var int */
    private $timeout;

    /**
     * @param string      $apiToken
     * @param string|null $baseUrl
     * @param int         $timeout Timeout em segundos para requisições HTTP
     */
    public function __construct($apiToken, $baseUrl = null, $timeout = 30)
    {
        $this->apiToken = $apiToken;
        $this->baseUrl = rtrim($baseUrl !== null ? $baseUrl : self::DEFAULT_BASE_URL, '/');
        $this->timeout = $timeout;
    }

    /**
     * @return string
     */
    public function getApiToken()
    {
        return $this->apiToken;
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }
}
