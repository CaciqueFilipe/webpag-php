<?php

namespace WebPag;

use WebPag\Support\SensitiveData;

class Environment
{
    public const ENV_API_TOKEN = 'WEBPAG_API_TOKEN';
    public const ENV_BASE_URL = 'WEBPAG_BASE_URL';
    public const ENV_TIMEOUT = 'WEBPAG_TIMEOUT';

    /** @var string */
    private $apiToken = '';

    /** @var string */
    private $baseUrl = '';

    /** @var int */
    private $timeout = 30;

    /**
     * Cria uma Environment lendo das variáveis de ambiente.
     *
     * @return self
     */
    public static function fromEnv()
    {
        $env = new self();

        $token = getenv(self::ENV_API_TOKEN);
        if (is_string($token) && $token !== '') {
            $env->apiToken = $token;
        }

        $baseUrl = getenv(self::ENV_BASE_URL);
        if (is_string($baseUrl) && $baseUrl !== '') {
            $env->baseUrl = $baseUrl;
        }

        $timeout = getenv(self::ENV_TIMEOUT);
        if (is_string($timeout) && $timeout !== '') {
            $env->timeout = (int) $timeout;
        }

        return $env;
    }

    /**
     * Cria uma Environment a partir de um array associativo.
     *
     * Chaves aceitas: api_token, base_url, timeout
     *
     * @param array<string, mixed> $config
     *
     * @return self
     */
    public static function fromArray(array $config)
    {
        $env = new self();

        if (isset($config['api_token']) && is_string($config['api_token'])) {
            $env->apiToken = $config['api_token'];
        }

        if (isset($config['base_url']) && is_string($config['base_url'])) {
            $env->baseUrl = $config['base_url'];
        }

        if (isset($config['timeout']) && is_numeric($config['timeout'])) {
            $env->timeout = (int) $config['timeout'];
        }

        return $env;
    }

    /**
     * @param string $apiToken
     *
     * @return self
     */
    public function setApiToken($apiToken)
    {
        $this->apiToken = $apiToken;

        return $this;
    }

    /**
     * @param string $baseUrl
     *
     * @return self
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    /**
     * @param int $timeout
     *
     * @return self
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;

        return $this;
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
     * Bloqueia serialize()/unserialize(): contém o token (ver Configuration::__sleep).
     *
     * @return array<int, string>
     */
    public function __sleep()
    {
        throw Configuration::serializationError();
    }

    /**
     * @return void
     */
    public function __wakeup()
    {
        throw Configuration::serializationError();
    }

    /**
     * @return array<string, mixed>
     */
    public function __serialize(): array
    {
        throw Configuration::serializationError();
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return void
     */
    public function __unserialize(array $data): void
    {
        throw Configuration::serializationError();
    }

    /**
     * Converte esta Environment para uma Configuration.
     *
     * @return Configuration
     *
     * @throws \InvalidArgumentException Se a URL base for insegura ou o timeout inválido
     */
    public function toConfiguration()
    {
        $baseUrl = $this->baseUrl !== '' ? $this->baseUrl : null;

        return new Configuration($this->apiToken, $baseUrl, $this->timeout);
    }
}
