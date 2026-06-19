<?php
/**
 * Exemplo: Processar webhook com verificação de assinatura
 *
 * Este é um exemplo de como processar um webhook recebido da WebPag
 * em um framework como Laravel, Symfony ou PHP puro.
 *
 * Uso: php examples/03-webhook.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use WebPag\WebPag;
use WebPag\Webhooks\WebhookParser;

// 1. Obter o payload bruto e headers da requisição
// Em um framework real, você obteria $request->getContent() e $request->header()
$rawPayload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_WEBPAG_SIGNATURE'] ?? '';

// 2. Verificar a assinatura (recomendado)
$apiToken = getenv('WEBPAG_API_TOKEN') ?: '';
if ($signature !== '' && $apiToken !== '') {
    $isValid = WebhookParser::verifySignature($rawPayload, $signature, $apiToken);
    if (!$isValid) {
        http_response_code(401);
        echo json_encode(['error' => 'Assinatura inválida']);
        exit;
    }
}

// 3. Interpretar o evento
$webpag = WebPag::env();
$event = $webpag->webhooks->parse($rawPayload);

// 4. Agir conforme o tipo de evento
if ($event->isPayment()) {
    $paymentId = $event->get('id');
    $status = $event->getStatus();

    echo "Webhook de pagamento recebido!" . PHP_EOL;
    echo "Payment ID: " . $paymentId . PHP_EOL;
    echo "Status: " . $status . PHP_EOL;

    // Status 40 = pago/confirmado
    if ($status === 40) {
        // Liberar pedido, atualizar banco, etc.
        echo "Pagamento confirmado! Liberando pedido..." . PHP_EOL;
    }
} elseif ($event->isTransfer()) {
    echo "Webhook de transferência recebido." . PHP_EOL;
} elseif ($event->isRefund()) {
    echo "Webhook de estorno recebido." . PHP_EOL;
} else {
    echo "Webhook de tipo desconhecido." . PHP_EOL;
}

// 5. Sempre retornar 200 para a WebPag
http_response_code(200);
echo json_encode(['received' => true]);
