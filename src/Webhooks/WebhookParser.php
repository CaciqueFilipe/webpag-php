<?php

namespace WebPag\Webhooks;

use WebPag\Exceptions\WebPagException;

class WebhookParser
{
    /**
     * Interpreta o payload recebido via webhook da WebPag.
     *
     * @param string|array<string, mixed> $payload JSON bruto ou array decodificado
     *
     * @return WebhookEvent
     *
     * @throws WebPagException
     */
    public function parse($payload)
    {
        if (is_string($payload)) {
            $decoded = json_decode($payload, true);

            if (! is_array($decoded)) {
                throw new WebPagException('Payload de webhook inválido: JSON malformado.');
            }

            $payload = $decoded;
        }

        if (! is_array($payload)) {
            throw new WebPagException('Payload de webhook inválido.');
        }

        $type = $this->detectType($payload);
        $dto = WebhookDtoFactory::create($type, $payload);

        return new WebhookEvent($type, $dto);
    }

    /**
     * Valida a assinatura do webhook.
     *
     * A WebPag envia o header "X-Webpag-Signature" com uma assinatura HMAC-SHA256
     * do payload bruto (corpo da requisição), usando o API token como chave.
     *
     * Sempre retorna false (nunca lança) para entradas ausentes ou malformadas, e recusa
     * token vazio: com chave vazia qualquer pessoa conseguiria forjar a assinatura.
     *
     * @param string      $rawPayload Corpo bruto da requisição (JSON string), sem re-serializar
     * @param string|null $signature  Valor do header X-Webpag-Signature (null se ausente)
     * @param string      $apiToken   Seu API token (usado como chave HMAC)
     *
     * @return bool
     */
    public static function verifySignature($rawPayload, $signature, $apiToken)
    {
        if (! is_string($rawPayload) || ! is_string($signature) || ! is_string($apiToken)) {
            return false;
        }

        if ($apiToken === '' || $rawPayload === '') {
            return false;
        }

        $signature = strtolower(trim($signature));

        // Assinatura HMAC-SHA256 em hexadecimal tem sempre 64 caracteres
        if (strlen($signature) !== 64 || ! ctype_xdigit($signature)) {
            return false;
        }

        $expected = hash_hmac('sha256', $rawPayload, $apiToken);

        return hash_equals($expected, $signature);
    }

    /**
     * Valida a assinatura e só então interpreta o payload.
     *
     * Use este método no endpoint que recebe webhooks: ele garante que nenhum evento
     * não autenticado chegue à sua lógica de negócio.
     *
     * @param string      $rawPayload Corpo bruto da requisição
     * @param string|null $signature  Valor do header X-Webpag-Signature
     * @param string      $apiToken   Seu API token
     *
     * @return WebhookEvent
     *
     * @throws WebPagException Se a assinatura for inválida ou o payload malformado
     */
    public function parseVerified($rawPayload, $signature, $apiToken)
    {
        if (! self::verifySignature($rawPayload, $signature, $apiToken)) {
            throw new WebPagException('Assinatura do webhook inválida.');
        }

        return $this->parse($rawPayload);
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return string
     */
    private function detectType(array $payload)
    {
        // A detecção de 'recurrency' deve vir antes de 'payment'
        if (isset($payload['is_recurrent']) && $payload['is_recurrent'] === true) {
            return WebhookEvent::TYPE_RECURRENCY;
        }

        if (isset($payload['destination_type']) || isset($payload['destination_type_name'])) {
            return WebhookEvent::TYPE_TRANSFER;
        }

        // O payload de estorno pode vir de formas diferentes.
        if ((isset($payload['refund_amount']) && isset($payload['payment_id'])) ||
            isset($payload['refund_id']) ||
            (isset($payload['type']) && $payload['type'] === 'refund')) {
            return WebhookEvent::TYPE_REFUND;
        }

        // Um pagamento geralmente terá 'method' ou 'payer_id'.
        // Esta verificação deve ser uma das últimas para não confundir com outros tipos.
        if (isset($payload['method']) || isset($payload['method_label']) || isset($payload['payer_id'])) {
            return WebhookEvent::TYPE_PAYMENT;
        }

        return WebhookEvent::TYPE_UNKNOWN;
    }
}
