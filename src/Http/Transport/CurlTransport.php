<?php

namespace WebPag\Http\Transport;

use InvalidArgumentException;

/**
 * Transporte padrão do SDK, sobre a extensão nativa ext-curl (sem dependências de terceiros).
 *
 * Proteções sempre ativas:
 * - TLS verificado (certificado e hostname);
 * - redirecionamentos nunca seguidos (o header auth-token iria para outro host);
 * - só os protocolos HTTP/HTTPS (sem file://, ftp://, gopher:// ...);
 * - proxies de variáveis de ambiente ignorados (só proxy explícito, via opção "proxy");
 * - TLS 1.2 ou superior;
 * - headers com CR/LF recusados (header injection);
 * - tamanho máximo de resposta e de headers (evita esgotar a memória).
 */
final class CurlTransport implements TransportInterface
{
    public const DEFAULT_MAX_RESPONSE_BYTES = 10485760; // 10 MB

    /** Soma máxima dos headers da resposta (evita esgotar memória com headers infinitos). */
    public const MAX_HEADER_BYTES = 65536; // 64 KB

    public const DEFAULT_TIMEOUT = 30;

    public const DEFAULT_CONNECT_TIMEOUT = 10;

    /** @var string */
    private $baseUrl;

    /** @var int */
    private $maxResponseBytes;

    /** @var string|null */
    private $proxy;

    /** @var string|null */
    private $caBundle;

    /**
     * @param string               $baseUrl URL base já validada (ex: Configuration::getBaseUrl())
     * @param array<string, mixed> $options Opcionais:
     *                                      - proxy (string): proxy explícito, ex "http://proxy.interno:3128"
     *                                      - ca_bundle (string): caminho de um bundle de CAs (.pem)
     *                                      - max_response_bytes (int): padrão 10 MB
     *
     * @throws TransportException       Se a extensão ext-curl não estiver disponível
     * @throws InvalidArgumentException Se alguma opção for inválida
     */
    public function __construct($baseUrl, array $options = [])
    {
        if (! extension_loaded('curl')) {
            throw new TransportException('A extensão ext-curl é necessária para o SDK WebPag.');
        }

        $this->baseUrl = rtrim((string) $baseUrl, '/');

        $this->maxResponseBytes = isset($options['max_response_bytes'])
            ? (int) $options['max_response_bytes']
            : self::DEFAULT_MAX_RESPONSE_BYTES;

        if ($this->maxResponseBytes <= 0) {
            throw new InvalidArgumentException('max_response_bytes precisa ser maior que zero.');
        }

        $this->proxy = isset($options['proxy']) ? self::validateProxy($options['proxy']) : null;

        if (isset($options['ca_bundle'])) {
            if (! is_string($options['ca_bundle']) || ! is_file($options['ca_bundle']) || ! is_readable($options['ca_bundle'])) {
                throw new InvalidArgumentException('ca_bundle precisa ser o caminho de um arquivo legível.');
            }

            $this->caBundle = $options['ca_bundle'];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function send($method, $uri, array $options)
    {
        $curlOptions = $this->buildCurlOptions($method, $uri, $options);

        $responseHeaders = [];
        $responseBody = '';
        $tooLarge = false;
        $headersTooLarge = false;
        $headerBytes = 0;
        $maxBytes = $this->maxResponseBytes;
        $maxHeaderBytes = self::MAX_HEADER_BYTES;

        $curlOptions[CURLOPT_HEADERFUNCTION] = function ($handle, $line) use (&$responseHeaders, &$headerBytes, &$headersTooLarge, $maxHeaderBytes) {
            $headerBytes += strlen($line);

            if ($headerBytes > $maxHeaderBytes) {
                $headersTooLarge = true;

                return 0; // interrompe a transferência
            }

            $trimmed = trim($line);

            if (stripos($trimmed, 'HTTP/') === 0) {
                // Nova linha de status (ex: após "100 Continue"): descarta headers anteriores
                $responseHeaders = [];
            } elseif (strpos($trimmed, ':') !== false) {
                list($name, $value) = explode(':', $trimmed, 2);
                $name = strtolower(trim($name));
                $value = trim($value);
                $responseHeaders[$name] = isset($responseHeaders[$name]) ? $responseHeaders[$name] . ', ' . $value : $value;
            }

            return strlen($line);
        };

        $curlOptions[CURLOPT_WRITEFUNCTION] = function ($handle, $chunk) use (&$responseBody, &$tooLarge, $maxBytes) {
            if (strlen($responseBody) + strlen($chunk) > $maxBytes) {
                $tooLarge = true;

                return 0; // interrompe a transferência
            }

            $responseBody .= $chunk;

            return strlen($chunk);
        };

        $handle = curl_init();

        if ($handle === false) {
            throw new TransportException('Não foi possível iniciar o cURL.');
        }

        curl_setopt_array($handle, $curlOptions);
        $ok = curl_exec($handle);
        $errno = curl_errno($handle);
        $error = curl_error($handle);
        $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        curl_close($handle);

        if ($headersTooLarge) {
            throw new TransportException('Resposta da API com headers maiores que o limite de ' . $maxHeaderBytes . ' bytes.');
        }

        if ($tooLarge) {
            throw new TransportException('Resposta da API maior que o limite de ' . $maxBytes . ' bytes.');
        }

        if ($ok === false || $errno !== 0) {
            throw new TransportException('Falha de comunicação (cURL ' . $errno . '): ' . $error);
        }

        return new TransportResponse($status, $responseHeaders, $responseBody);
    }

    /**
     * Monta as opções do cURL. Público para permitir verificar as proteções em testes.
     *
     * @param string               $method
     * @param string               $uri
     * @param array<string, mixed> $options Mesmas chaves de TransportInterface::send()
     *
     * @return array<int, mixed>
     */
    public function buildCurlOptions($method, $uri, array $options)
    {
        $method = strtoupper((string) $method);

        if (! preg_match('/^[A-Z]+$/', $method)) {
            throw new InvalidArgumentException('Método HTTP inválido.');
        }

        $headers = isset($options['headers']) && is_array($options['headers']) ? $options['headers'] : [];
        // Evita a espera do "Expect: 100-continue" em corpos grandes
        $headers['Expect'] = '';

        $timeout = isset($options['timeout']) ? max(1, (int) $options['timeout']) : self::DEFAULT_TIMEOUT;
        $connectTimeout = isset($options['connect_timeout']) ? max(1, (int) $options['connect_timeout']) : self::DEFAULT_CONNECT_TIMEOUT;

        $curlOptions = [
            CURLOPT_URL => $this->buildUrl($uri, isset($options['query']) && is_array($options['query']) ? $options['query'] : []),
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => self::formatHeaders($headers),
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => min($connectTimeout, $timeout),
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            // Impede downgrade para SSLv3/TLS 1.0/1.1 em ambientes com OpenSSL antigo
            CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS | CURLPROTO_HTTP,
            // String vazia desativa explicitamente proxies vindos de http_proxy/HTTPS_PROXY/ALL_PROXY
            CURLOPT_PROXY => $this->proxy !== null ? $this->proxy : '',
            CURLOPT_ENCODING => '',
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_NOSIGNAL => true,
        ];

        if ($this->caBundle !== null) {
            $curlOptions[CURLOPT_CAINFO] = $this->caBundle;
        }

        if ($method === 'HEAD') {
            $curlOptions[CURLOPT_NOBODY] = true;
        } elseif (array_key_exists('json', $options)) {
            $curlOptions[CURLOPT_POSTFIELDS] = self::encodeJson($options['json']);
        }

        return $curlOptions;
    }

    /**
     * @return array<string, mixed>
     */
    public function __debugInfo()
    {
        return [
            'baseUrl' => $this->baseUrl,
            'maxResponseBytes' => $this->maxResponseBytes,
            'proxy' => $this->proxy !== null ? preg_replace('#//[^@/]*@#', '//****@', $this->proxy) : null,
            'caBundle' => $this->caBundle,
        ];
    }

    /**
     * @param string               $uri
     * @param array<string, mixed> $query
     *
     * @return string
     */
    private function buildUrl($uri, array $query)
    {
        $url = $this->baseUrl . '/' . ltrim((string) $uri, '/');

        if (count($query) > 0) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        }

        return $url;
    }

    /**
     * @param array<string, mixed> $headers
     *
     * @return string[]
     */
    private static function formatHeaders(array $headers)
    {
        $lines = [];

        foreach ($headers as $name => $value) {
            if (! is_string($name) || ! preg_match('/^[A-Za-z0-9!#$%&\'*+.^_`|~-]+$/', $name)) {
                throw new InvalidArgumentException('Nome de header HTTP inválido.');
            }

            if (! is_scalar($value)) {
                throw new InvalidArgumentException('Valor do header "' . $name . '" precisa ser escalar.');
            }

            $value = (string) $value;

            // CR/LF/NUL permitiriam injetar headers ou dividir a requisição
            if (preg_match('/[\r\n\0]/', $value)) {
                throw new InvalidArgumentException('Valor do header "' . $name . '" contém caracteres de controle.');
            }

            if ($value === '') {
                // Para o cURL, "Expect:" remove o header; "Nome;" envia um header vazio
                $lines[] = strcasecmp($name, 'Expect') === 0 ? 'Expect:' : $name . ';';
            } else {
                $lines[] = $name . ': ' . $value;
            }
        }

        return $lines;
    }

    /**
     * @param mixed $data
     *
     * @return string
     */
    private static function encodeJson($data)
    {
        $json = json_encode($data);

        if ($json === false || json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Não foi possível serializar o corpo em JSON: ' . json_last_error_msg());
        }

        return $json;
    }

    /**
     * @param mixed $proxy
     *
     * @return string
     */
    private static function validateProxy($proxy)
    {
        $parts = is_string($proxy) ? parse_url($proxy) : false;

        if ($parts === false || ! isset($parts['scheme'], $parts['host']) || ! in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
            throw new InvalidArgumentException('proxy precisa ser uma URL http:// ou https://.');
        }

        return $proxy;
    }
}
