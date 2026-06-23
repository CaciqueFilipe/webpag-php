<?php

namespace WebPag\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use WebPag\Configuration;
use WebPag\Exceptions\ApiException;

class HttpClient
{
    use LoggerAwareTrait;

    public const DEFAULT_MAX_RETRIES = 3;

    /** @var Configuration */
    private $config;

    /** @var Client */
    private $client;

    /** @var int */
    private $maxRetries;

    /**
     * @param Configuration        $config
     * @param Client|null          $client
     * @param LoggerInterface|null $logger
     * @param int                  $maxRetries Número máximo de retentativas em caso de falha
     */
    public function __construct(Configuration $config, Client $client = null, LoggerInterface $logger = null, $maxRetries = self::DEFAULT_MAX_RETRIES)
    {
        $this->config = $config;
        $this->client = $client !== null ? $client : new Client([
            'base_uri' => $config->getBaseUrl() . '/',
            'timeout' => $config->getTimeout(),
            'http_errors' => false,
        ]);
        $this->setLogger($logger !== null ? $logger : new NullLogger());
        $this->maxRetries = $maxRetries;
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
    public function request($method, $uri, array $options = [])
    {
        $options['headers'] = array_merge([
            'auth-token' => $this->config->getApiToken(),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ], isset($options['headers']) ? $options['headers'] : []);

        $this->logger->info('WebPag API request', [
            'method' => $method,
            'uri' => $uri,
            'has_body' => isset($options['json']) || isset($options['form_params']),
            'query' => isset($options['query']) ? $options['query'] : null,
        ]);

        $lastException = null;
        $attempts = $this->maxRetries + 1;

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            try {
                $startTime = microtime(true);
                $response = $this->client->request($method, ltrim($uri, '/'), $options);
                $elapsed = (microtime(true) - $startTime) * 1000;

                $this->logger->info('WebPag API response', [
                    'status' => $response->getStatusCode(),
                    'uri' => $uri,
                    'method' => $method,
                    'elapsed_ms' => round($elapsed, 2),
                ]);

                if ($elapsed > $this->config->getTimeout() * 500) {
                    $this->logger->warning('WebPag API slow response', [
                        'uri' => $uri,
                        'method' => $method,
                        'elapsed_ms' => round($elapsed, 2),
                    ]);
                }

                $apiResponse = ApiResponse::fromResponse($response);

                if ($response->getStatusCode() >= 400) {
                    $shouldRetry = $this->isRetryableStatusCode($response->getStatusCode());

                    if ($shouldRetry && $attempt < $attempts) {
                        $delay = $this->getBackoffDelay($attempt);
                        $this->logger->warning('WebPag API retryable error', [
                            'status' => $response->getStatusCode(),
                            'uri' => $uri,
                            'attempt' => $attempt,
                            'next_delay_ms' => $delay * 1000,
                        ]);
                        usleep($delay * 1000000);

                        continue;
                    }

                    throw new ApiException(
                        $this->resolveErrorMessage($apiResponse),
                        $response->getStatusCode(),
                        $apiResponse->toArray()
                    );
                }

                return $apiResponse;
            } catch (ApiException $e) {
                throw $e;
            } catch (GuzzleException $e) {
                $lastException = $e;

                $this->logger->error('WebPag API communication error', [
                    'uri' => $uri,
                    'method' => $method,
                    'attempt' => $attempt,
                    'error' => $e->getMessage(),
                ]);

                if ($attempt < $attempts) {
                    $delay = $this->getBackoffDelay($attempt);
                    $this->logger->info('WebPag API retrying', [
                        'attempt' => $attempt,
                        'next_delay_ms' => $delay * 1000,
                    ]);
                    usleep($delay * 1000000);
                }
            }
        }

        throw new ApiException(
            'Erro de comunicação com a API WebPag após ' . $this->maxRetries . ' tentativas: ' . $lastException->getMessage(),
            0,
            null,
            $lastException
        );
    }

    /**
     * Define o LoggerInterface.
     *
     * @param LoggerInterface $logger
     *
     * @return void
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Retorna o número máximo de retentativas configurado.
     *
     * @return int
     */
    public function getMaxRetries()
    {
        return $this->maxRetries;
    }

    /**
     * @param int $statusCode
     *
     * @return bool
     */
    private function isRetryableStatusCode($statusCode)
    {
        // 429 = Too Many Requests, 5xx = Server errors
        return $statusCode === 429 || ($statusCode >= 500 && $statusCode < 600);
    }

    /**
     * Exponential backoff com jitter: 1s, 2s, 4s, 8s, ...
     *
     * @param int $attempt Tentativa atual (1-based)
     *
     * @return float Delay em segundos
     */
    private function getBackoffDelay($attempt)
    {
        $baseDelay = pow(2, $attempt - 1);
        $jitter = mt_rand(0, (int) ($baseDelay * 1000)) / 1000;

        return $baseDelay + $jitter;
    }

    /**
     * @param string               $uri
     * @param array<string, mixed> $query
     *
     * @return ApiResponse
     */
    public function get($uri, array $query = [])
    {
        return $this->request('GET', $uri, ['query' => $query]);
    }

    /**
     * @param string               $uri
     * @param array<string, mixed> $body
     *
     * @return ApiResponse
     */
    public function post($uri, array $body = [])
    {
        return $this->request('POST', $uri, ['json' => $body]);
    }

    /**
     * @param string               $uri
     * @param array<string, mixed> $body
     *
     * @return ApiResponse
     */
    public function put($uri, array $body = [])
    {
        return $this->request('PUT', $uri, ['json' => $body]);
    }

    /**
     * @param string               $uri
     * @param array<string, mixed> $query
     *
     * @return ApiResponse
     */
    public function delete($uri, array $query = [])
    {
        $options = [];

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
            $messages = [];

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
