<?php

namespace WebPag\Http;

use InvalidArgumentException;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use WebPag\Configuration;
use WebPag\Exceptions\ApiException;
use WebPag\Http\Transport\CurlTransport;
use WebPag\Http\Transport\GuzzleTransport;
use WebPag\Http\Transport\TransportException;
use WebPag\Http\Transport\TransportInterface;
use WebPag\Http\Transport\TransportResponse;

class HttpClient
{
    use LoggerAwareTrait;

    public const DEFAULT_MAX_RETRIES = 3;

    /** Tempo máximo (s) para estabelecer a conexão, independente do timeout total. */
    public const DEFAULT_CONNECT_TIMEOUT = 10;

    /** Maior espera (s) aceita a partir do header Retry-After. */
    public const MAX_RETRY_AFTER = 30;

    /**
     * Métodos que podem ser repetidos com segurança após falha de rede ou 5xx.
     * POST/PUT/DELETE criam cobranças, estornos e saques: repetir pode duplicar a operação.
     */
    private const IDEMPOTENT_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    /** @var Configuration */
    private $config;

    /** @var TransportInterface */
    private $transport;

    /** @var int */
    private $maxRetries;

    /**
     * @param Configuration                                        $config
     * @param TransportInterface|\GuzzleHttp\ClientInterface|null $transport Padrão: CurlTransport (ext-curl).
     *                                                                       Um Client do Guzzle do seu projeto também é aceito.
     * @param LoggerInterface|null                                 $logger
     * @param int                                                  $maxRetries Número máximo de retentativas em caso de falha
     *
     * @throws InvalidArgumentException Se $transport não for de um tipo suportado
     */
    public function __construct(Configuration $config, $transport = null, ?LoggerInterface $logger = null, $maxRetries = self::DEFAULT_MAX_RETRIES)
    {
        $this->config = $config;
        $this->transport = self::resolveTransport($config, $transport);
        $this->setLogger($logger !== null ? $logger : new NullLogger());
        $this->maxRetries = max(0, (int) $maxRetries);
    }

    /**
     * @return TransportInterface
     */
    public function getTransport()
    {
        return $this->transport;
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
        $method = strtoupper($method);

        $options['headers'] = array_merge([
            'auth-token' => $this->config->getApiToken(),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ], isset($options['headers']) ? $options['headers'] : []);

        // Redirects desligados e http_errors => false são garantidos pelos transportes
        // (o auth-token nunca pode seguir um redirect para outro host).
        $options['timeout'] = $this->config->getTimeout();
        $options['connect_timeout'] = min(self::DEFAULT_CONNECT_TIMEOUT, $this->config->getTimeout());

        // Nunca registrar valores da query: filtros podem conter CPF/CNPJ, e-mail etc. (LGPD).
        $this->logger->info('WebPag API request', [
            'method' => $method,
            'uri' => $uri,
            'has_body' => isset($options['json']) || isset($options['form_params']),
            'query_keys' => isset($options['query']) && is_array($options['query']) ? array_keys($options['query']) : [],
        ]);

        $idempotent = in_array($method, self::IDEMPOTENT_METHODS, true);
        $attempts = $this->maxRetries + 1;

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            try {
                $startTime = microtime(true);
                $response = $this->transport->send($method, ltrim($uri, '/'), $options);
                $elapsed = (microtime(true) - $startTime) * 1000;
            } catch (TransportException $e) {
                $this->logger->error('WebPag API communication error', [
                    'uri' => $uri,
                    'method' => $method,
                    'attempt' => $attempt,
                    'error' => $e->getMessage(),
                ]);

                if (! $idempotent) {
                    // A requisição pode ter chegado à API antes da falha (ex: timeout de leitura).
                    // Repetir poderia duplicar cobrança/estorno/saque: o chamador deve consultar antes.
                    throw new ApiException(
                        'Erro de comunicação com a API WebPag em ' . $method . ' ' . $uri
                        . '. A operação pode ter sido processada: consulte o recurso antes de repetir. '
                        . 'Detalhe: ' . $e->getMessage(),
                        0,
                        null,
                        $e
                    );
                }

                if ($attempt < $attempts) {
                    $this->sleepBeforeRetry($attempt, null, $uri);

                    continue;
                }

                throw new ApiException(
                    'Erro de comunicação com a API WebPag após ' . $attempts . ' tentativa(s): ' . $e->getMessage(),
                    0,
                    null,
                    $e
                );
            }

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

            $apiResponse = ApiResponse::fromRaw($response->getBody(), $response->getStatusCode());
            $status = $response->getStatusCode();

            if ($status < 400) {
                return $apiResponse;
            }

            if ($attempt < $attempts && $this->shouldRetryStatus($status, $idempotent)) {
                $this->sleepBeforeRetry($attempt, $response, $uri);

                continue;
            }

            throw new ApiException(
                $this->resolveErrorMessage($apiResponse),
                $status,
                $apiResponse->toArray()
            );
        }

        // Inalcançável: o laço sempre retorna ou lança.
        throw new ApiException('Erro inesperado ao chamar a API WebPag.');
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
     * Evita que var_dump/print_r/dd exponham o token via Configuration.
     *
     * @return array<string, mixed>
     */
    public function __debugInfo()
    {
        return [
            'config' => $this->config,
            'transport' => $this->transport,
            'maxRetries' => $this->maxRetries,
        ];
    }

    /**
     * @param Configuration $config
     * @param mixed         $transport
     *
     * @return TransportInterface
     */
    private static function resolveTransport(Configuration $config, $transport)
    {
        if ($transport === null) {
            return new CurlTransport($config->getBaseUrl());
        }

        if ($transport instanceof TransportInterface) {
            return $transport;
        }

        if (interface_exists('GuzzleHttp\ClientInterface') && $transport instanceof \GuzzleHttp\ClientInterface) {
            return new GuzzleTransport($transport);
        }

        throw new InvalidArgumentException(
            'Transporte inválido: use um WebPag\Http\Transport\TransportInterface, um GuzzleHttp\ClientInterface ou null.'
        );
    }

    /**
     * 429 significa que a API recusou a requisição sem processá-la, então é seguro
     * repetir qualquer método. 5xx só é repetido para métodos idempotentes.
     *
     * @param int  $statusCode
     * @param bool $idempotent
     *
     * @return bool
     */
    private function shouldRetryStatus($statusCode, $idempotent)
    {
        if ($statusCode === 429) {
            return true;
        }

        return $idempotent && $statusCode >= 500 && $statusCode < 600;
    }

    /**
     * @param int                    $attempt
     * @param TransportResponse|null $response
     * @param string                 $uri
     *
     * @return void
     */
    private function sleepBeforeRetry($attempt, $response, $uri)
    {
        $delay = $this->getBackoffDelay($attempt);

        if ($response !== null) {
            $retryAfter = $response->getHeaderLine('Retry-After');
            if ($retryAfter !== '' && ctype_digit($retryAfter)) {
                $delay = min((int) $retryAfter, self::MAX_RETRY_AFTER);
            }
        }

        $this->logger->warning('WebPag API retrying', [
            'status' => $response !== null ? $response->getStatusCode() : null,
            'uri' => $uri,
            'attempt' => $attempt,
            'next_delay_ms' => $delay * 1000,
        ]);

        usleep((int) ($delay * 1000000));
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
                    $messages[] = $field . ': ' . implode(', ', array_map('strval', array_filter($fieldErrors, 'is_scalar')));
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
