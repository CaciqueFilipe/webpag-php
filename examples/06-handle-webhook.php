<?php

/**
 * Exemplo: Endpoint que recebe webhooks da WebPag
 *
 * Demonstra como:
 * 1. Ler o corpo BRUTO da requisição (a assinatura é calculada sobre ele).
 * 2. Validar a assinatura antes de qualquer processamento (parseVerified).
 * 3. Tratar os tipos de evento (pagamento, transferência, estorno...).
 * 4. Responder rápido e sem vazar detalhes de erro para quem chamou.
 *
 * Para testar localmente:
 *   WEBPAG_API_TOKEN=seu-token php -S localhost:8000 examples/06-handle-webhook.php
 *   BODY='{"id":123,"status":40,"method":"pix","payer_id":15}'
 *   SIG=$(printf '%s' "$BODY" | openssl dgst -sha256 -hmac "seu-token" | cut -d' ' -f2)
 *   curl -X POST localhost:8000 -H "X-Webpag-Signature: $SIG" -d "$BODY"
 */

require_once __DIR__ . '/../vendor/autoload.php';

use WebPag\Enums\PaymentStatus;
use WebPag\Exceptions\WebPagException;
use WebPag\Responses\Payments\Payment;
use WebPag\Responses\Transfers\Transfer;
use WebPag\Webhooks\WebhookParser;

// 1. O token vem SEMPRE da configuração. Nunca use um valor padrão fixo no código:
//    com um token conhecido qualquer pessoa consegue forjar a assinatura.
$apiToken = getenv('WEBPAG_API_TOKEN');
if (! is_string($apiToken) || $apiToken === '') {
    http_response_code(500);
    error_log('WEBPAG_API_TOKEN não configurado: webhook recusado.');
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    exit;
}

// 2. Corpo bruto, exatamente como chegou (não use json_decode + json_encode antes de validar).
$rawPayload = (string) file_get_contents('php://input');
$signature = isset($_SERVER['HTTP_X_WEBPAG_SIGNATURE']) ? $_SERVER['HTTP_X_WEBPAG_SIGNATURE'] : null;

try {
    // 3. Valida a assinatura e só então interpreta o payload.
    $event = (new WebhookParser())->parseVerified($rawPayload, $signature, $apiToken);
} catch (WebPagException $e) {
    // Resposta genérica: não informe ao chamador o motivo exato da recusa.
    http_response_code(401);
    error_log('Webhook WebPag recusado: ' . $e->getMessage());
    exit;
}

// 4. Trate o evento. Dica: use o ID como chave de idempotência, porque a WebPag pode reenviar
//    o mesmo evento, e consulte a API (find) antes de liberar algo de alto valor.
if ($event->isPayment()) {
    /** @var Payment $payment */
    $payment = $event->getPayload();

    if ($payment->status === PaymentStatus::PAID) {
        // Libere o pedido associado a $payment->orderId (uma única vez).
        error_log('Pagamento confirmado: ' . $payment->id);
    }
} elseif ($event->isTransfer()) {
    /** @var Transfer $transfer */
    $transfer = $event->getPayload();
    error_log('Transferência ' . $transfer->id . ' com status ' . $transfer->statusName);
} else {
    error_log('Evento WebPag do tipo ' . $event->getType() . ' recebido.');
}

http_response_code(200);
