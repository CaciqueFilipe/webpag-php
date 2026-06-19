<?php

namespace WebPag\Resources;

use WebPag\Http\HttpClient;

abstract class AbstractResource
{
    /** @var HttpClient */
    protected $http;

    /**
     * @param HttpClient $http
     */
    public function __construct(HttpClient $http)
    {
        $this->http = $http;
    }
}
