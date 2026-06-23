<?php
/**
 * Exemplo: Processar um webhook da WebPag
 *
 * Este script simula o endpoint que recebe as notificações (webhooks) da WebPag.
 * Ele demonstra como:
 * 1. Validar a assinatura do webhook para garantir sua autenticidade.
 * 2. Interpretar (parse) o payload para obter um objeto de evento tipado.
 * 3. Tratar diferentes tipos de eventos (pagamento, transferência, etc.).
 *
 * Para testar:
 * 1. Crie um payload JSON (ex: '{"id": 123, "status": 40, "method": "pix"}').
 * 2. Calcule a assinatura HMAC-SHA256.
 * 3. Simule uma requisição para este script com o payload e o header X-Webpag-Signature.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use WebPag\WebPag;
use WebPag\Enums\PaymentStatus;
use WebPag\Webhooks\WebhookParser;
use WebPag\Responses\Payments\Payment;
use WebPag\Responses\Transfers\Transfer;

// --- Simulação do ambiente de uma requisição HTTP ---

// 1. Obtenha o seu API Token (usado como chave secreta para a assinatura).
// Em um projeto real, isso viria do seu arquivo .env ou configuração.
$apiToken = getenv('WEBPAG_API_TOKEN') ?: 'seu-token-secreto-aqui';

// 2. Simule o corpo bruto (raw payload) da requisição.
$rawPayload = '{"id": 12345, "status": 40, "method": "pix", "amount": 2500, "payer_id": 15}';

// 3. Simule o header da assinatura enviado pela WebPag.
$signature = hash_hmac('sha256', $rawPayload, $apiToken);

// --- Fim da Simulação ---


echo "Iniciando processamento do Webhook..." . PHP_EOL;
echo "-------------------------------------" . PHP_EOL;

// Etapa 1: Validar a assinatura
// Isso é CRUCIAL para garantir que a requisição veio da WebPag e não foi adulterada.
if (!WebhookParser::verifySignature($rawPayload, $signature, $apiToken)) {
    // Em um cenário real, você retornaria um status HTTP 401 ou 403.
    die("Erro: Assinatura do Webhook inválida!");
}

echo "Assinatura validada com sucesso!" . PHP_EOL;

// Etapa 2: Iniciar o SDK e interpretar o payload
$webpag = WebPag::env();

try {
    $event = $webpag->webhooks->parse($rawPayload);

    // Etapa 3: Tratar o evento com base no seu tipo
    if ($event->isPayment()) {
        /** @var Payment $payment */
        $payment = $event->getPayload();

        echo "Evento de Pagamento recebido. ID: {$payment->id}" . PHP_EOL;

        if ($payment->status === PaymentStatus::PAID) {
            echo "Status: Pagamento confirmado! Liberando o pedido..." . PHP_EOL;
            // Aqui você colocaria a lógica para liberar o pedido, notificar o cliente, etc.
        }
    } elseif ($event->isTransfer()) {
        /** @var Transfer $transfer */
        $transfer = $event->getPayload();
        echo "Evento de Transferência recebido. ID: {$transfer->id}, Status: {$transfer->statusName}" . PHP_EOL;
        // Lógica para atualizar o status de uma transferência no seu sistema.
    } else {
        echo "Tipo de evento recebido: " . $event->getType() . PHP_EOL;
    }
} catch (\WebPag\Exceptions\WebPagException $e) {
    echo "Erro ao interpretar o webhook: " . $e->getMessage() . PHP_EOL;
}

echo "-------------------------------------" . PHP_EOL;
echo "Processamento do Webhook finalizado." . PHP_EOL;