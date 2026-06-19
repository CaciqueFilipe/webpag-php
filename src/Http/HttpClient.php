<?php

namespace WebPag\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use WebPag\Configuration;
use WebPag\Exceptions\ApiException;

class HttpClient
{
    /** @var Configuration */
    private $config;

    /** @var Client */
    private $client;

    /**
     * @param Configuration $config
     * @param Client|null   $client
     */
    public function __construct(Configuration $config, Client $client = null)
    {
        $this->config = $config;
        $this->client = $client !== null ? $client : new Client([
            'base_uri' => $config->getBaseUrl() . '/',
            'timeout' => $config->getTimeout(),
            'http_errors' => false,
        ]);
    }

    /**
     * @param string               $method
     * @param string               $uri
     * @param array<string, mixed> $options
     *
     * @return ApiResponse
     *
     * @throws ApiException
     */
    public function request($method, $uri, array $options = array())
    {
        $options['headers'] = array_merge([
            'auth-token' => $this->config->getApiToken(),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ], isset($options['headers']) ? $options['headers'] : array());

        try {
            $response = $this->client->request($method, ltrim($uri, '/'), $options);
        } catch (GuzzleException $e) {
            throw new ApiException(
                'Erro de comunicação com a API WebPag: ' . $e->getMessage(),
                0,
                null,
                $e
            );
        }

        $apiResponse = ApiResponse::fromResponse($response);

        if ($response->getStatusCode() >= 400) {
            throw new ApiException(
                $this->resolveErrorMessage($apiResponse),
                $response->getStatusCode(),
                $apiResponse->toArray()
            );
        }

        return $apiResponse;
    }

    /**
     * @param string               $uri
     * @param array<string, mixed> $query
     *
     * @return ApiResponse
     */
    public function get($uri, array $query = array())
    {
        return $this->request('GET', $uri, ['query' => $query]);
    }

    /**
     * @param string               $uri
     * @param array<string, mixed> $body
     *
     * @return ApiResponse
     */
    public function post($uri, array $body = array())
    {
        return $this->request('POST', $uri, ['json' => $body]);
    }

    /**
     * @param string               $uri
     * @param array<string, mixed> $body
     *
     * @return ApiResponse
     */
    public function put($uri, array $body = array())
    {
        return $this->request('PUT', $uri, ['json' => $body]);
    }

    /**
     * @param string               $uri
     * @param array<string, mixed> $query
     *
     * @return ApiResponse
     */
    public function delete($uri, array $query = array())
    {
        $options = array();

        if (count($query) > 0) {
            $options['query'] = $query;
        }

        return $this->request('DELETE', $uri, $options);
    }

    /**
     * @param ApiResponse $response
     *
     * @return string
     */
    private function resolveErrorMessage(ApiResponse $response)
    {
        $message = $response->get('message');

        if (is_string($message) && $message !== '') {
            return $message;
        }

        if (isset($response['errors']) && is_array($response['errors'])) {
            $messages = array();

            foreach ($response['errors'] as $field => $fieldErrors) {
                if (is_array($fieldErrors)) {
                    $messages[] = $field . ': ' . implode(', ', $fieldErrors);
                } else {
                    $messages[] = (string) $fieldErrors;
                }
            }

            if (count($messages) > 0) {
                return implode(' | ', $messages);
            }
        }

        return 'Erro na requisição à API WebPag (HTTP ' . $response->getStatusCode() . ')';
    }
}
