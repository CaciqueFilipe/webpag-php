<?php

namespace WebPag\Http\Transport;

use InvalidArgumentException;

/**
 * Adaptador opcional para usar um Client do Guzzle (6.5+ ou 7.x) já existente no projeto.
 *
 * O Guzzle NÃO é dependência do SDK: este adaptador só é usado quando um Client é injetado
 * no HttpClient. Nesse caso vale a versão de Guzzle do seu projeto; mantenha-a atualizada
 * (recomendado 7.15.2+). Redirects e http_errors são sempre desligados por requisição.
 */
final class GuzzleTransport implements TransportInterface
{
    /** @var \GuzzleHttp\ClientInterface */
    private $client;

    /**
     * @param \GuzzleHttp\ClientInterface $client
     */
    public function __construct($client)
    {
        if (! interface_exists('GuzzleHttp\ClientInterface') || ! $client instanceof \GuzzleHttp\ClientInterface) {
            throw new InvalidArgumentException('GuzzleTransport espera uma instância de GuzzleHttp\ClientInterface.');
        }

        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function send($method, $uri, array $options)
    {
        // O header auth-token é próprio e o Guzzle não o remove ao redirecionar para outro host
        $options['allow_redirects'] = false;
        // Status >= 400 é tratado pelo HttpClient (ApiException com status e corpo)
        $options['http_errors'] = false;

        try {
            $response = $this->client->request($method, ltrim((string) $uri, '/'), $options);
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            // A exceção original NÃO é encadeada: sua mensagem traz a URL com a query
            // (CPF, e-mail...) e loggers como o Monolog registram toda a cadeia de "previous".
            throw new TransportException('Falha de comunicação (' . get_class($e) . '): ' . $e->getMessage());
        }

        $headers = [];
        foreach ($response->getHeaders() as $name => $values) {
            $headers[strtolower($name)] = implode(', ', $values);
        }

        return new TransportResponse($response->getStatusCode(), $headers, (string) $response->getBody());
    }
}
