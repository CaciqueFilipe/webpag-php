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
     * @param string $rawPayload     Corpo bruto da requisição (JSON string)
     * @param string $signature      Valor do header X-Webpag-Signature
     * @param string $apiToken       Seu API token (usado como chave HMAC)
     *
     * @return bool
     */
    public static function verifySignature($rawPayload, $signature, $apiToken)
    {
        $expected = hash_hmac('sha256', $rawPayload, $apiToken);

        return hash_equals($expected, $signature);
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
