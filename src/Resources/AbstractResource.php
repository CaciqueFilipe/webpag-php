<?php

namespace WebPag\Resources;

use WebPag\Http\HttpClient;
use WebPag\Contracts\RequestPayload;

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

    /**
     * Resolve o payload de requisição: aceita tanto objetos RequestPayload quanto arrays.
     *
     * @param RequestPayload|array<string, mixed>|null $payload
     *
     * @return array<string, mixed>
     */
    protected function resolvePayload($payload)
    {
        if ($payload === null) {
            return array();
        }

        if ($payload instanceof RequestPayload) {
            return $payload->toArray();
        }

        return $payload;
    }
}
