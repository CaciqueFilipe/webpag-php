<?php

namespace WebPag;

use InvalidArgumentException;
use WebPag\Exceptions\WebPagException;
use WebPag\Support\SensitiveData;

class Configuration
{
    public const DEFAULT_BASE_URL = 'https://api.webpag.com.br';

    /** Hosts em que HTTP sem TLS é aceito (desenvolvimento local / mocks). */
    private const LOCAL_HOSTS = ['localhost', '127.0.0.1', '::1', '[::1]'];

    /** @var string */
    private $apiToken;

    /** @var string */
    private $baseUrl;

    /** @var int */
    private $timeout;

    /**
     * @param string      $apiToken
     * @param string|null $baseUrl  Precisa ser HTTPS (HTTP só é aceito para localhost)
     * @param int         $timeout  Timeout em segundos para requisições HTTP (maior que zero)
     *
     * @throws InvalidArgumentException Se a URL base for inválida/insegura ou o timeout inválido
     */
    public function __construct($apiToken, $baseUrl = null, $timeout = 30)
    {
        $this->apiToken = (string) $apiToken;

        // O token vai num header HTTP: caracteres de controle permitiriam injetar headers
        if (preg_match('/[\x00-\x1F\x7F]/', $this->apiToken)) {
            throw new InvalidArgumentException('O token da API WebPag contém caracteres inválidos (quebra de linha ou controle).');
        }
        $this->baseUrl = self::normalizeBaseUrl($baseUrl !== null ? $baseUrl : self::DEFAULT_BASE_URL);
        $this->timeout = self::normalizeTimeout($timeout);
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

    /**
     * Evita que var_dump/print_r/dd exponham o token.
     *
     * @return array<string, mixed>
     */
    public function __debugInfo()
    {
        return [
            'apiToken' => SensitiveData::mask($this->apiToken),
            'baseUrl' => $this->baseUrl,
            'timeout' => $this->timeout,
        ];
    }

    /**
     * Bloqueia serialize(): o token iria em texto puro para fila/cache/sessão.
     * Em Jobs do Laravel, resolva o WebPag dentro do handle(), e não no construtor.
     *
     * @return array<int, string>
     */
    public function __sleep()
    {
        throw self::serializationError();
    }

    /**
     * Bloqueia unserialize(): um objeto forjado pularia a validação de URL/token do construtor.
     *
     * @return void
     */
    public function __wakeup()
    {
        throw self::serializationError();
    }

    /**
     * PHP 7.4+ (tem precedência sobre __sleep).
     *
     * @return array<string, mixed>
     */
    public function __serialize(): array
    {
        throw self::serializationError();
    }

    /**
     * PHP 7.4+ (tem precedência sobre __wakeup).
     *
     * @param array<string, mixed> $data
     *
     * @return void
     */
    public function __unserialize(array $data): void
    {
        throw self::serializationError();
    }

    /**
     * @return WebPagException
     */
    public static function serializationError()
    {
        return new WebPagException(
            'Objetos com o token da API WebPag não podem ser serializados. '
            . 'Crie ou resolva a instância onde ela for usada (ex: no handle() do Job).'
        );
    }

    /**
     * @param string $baseUrl
     *
     * @return string
     */
    private static function normalizeBaseUrl($baseUrl)
    {
        $baseUrl = rtrim(trim((string) $baseUrl), '/');
        $parts = parse_url($baseUrl);

        if ($parts === false || ! isset($parts['scheme'], $parts['host'])) {
            throw new InvalidArgumentException('URL base da API WebPag inválida: informe uma URL absoluta (ex: https://api.webpag.com.br).');
        }

        if (isset($parts['user']) || isset($parts['pass'])) {
            throw new InvalidArgumentException('A URL base da API WebPag não pode conter usuário/senha.');
        }

        if (isset($parts['query']) || isset($parts['fragment'])) {
            throw new InvalidArgumentException('A URL base da API WebPag não pode conter query string ou fragmento.');
        }

        $scheme = strtolower($parts['scheme']);
        $host = strtolower($parts['host']);

        if ($scheme === 'https') {
            return $baseUrl;
        }

        if ($scheme === 'http' && in_array($host, self::LOCAL_HOSTS, true)) {
            return $baseUrl;
        }

        throw new InvalidArgumentException(
            'A URL base da API WebPag precisa usar HTTPS: o token de autenticação seria enviado sem criptografia.'
        );
    }

    /**
     * @param mixed $timeout
     *
     * @return int
     */
    private static function normalizeTimeout($timeout)
    {
        if (! is_numeric($timeout) || (int) $timeout <= 0) {
            throw new InvalidArgumentException('O timeout da API WebPag precisa ser um número inteiro maior que zero.');
        }

        return (int) $timeout;
    }
}
