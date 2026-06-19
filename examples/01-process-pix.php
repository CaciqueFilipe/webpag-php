<?php
/**
 * Exemplo: Processar pagamento PIX
 *
 * Uso: WEBPAG_API_TOKEN=seu-token php examples/01-process-pix.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use WebPag\WebPag;
use WebPag\Enums\PaymentMethod;

// Cria instância a partir de variáveis de ambiente
$webpag = WebPag::env();

// Processa pagamento PIX
$response = $webpag->payments->process([
    'payer_id' => 15,
    'name' => 'Pedido #1234',
    'amount' => 1500, // R$ 15,00 em centavos
    'method' => PaymentMethod::PIX,
]);

$payment = $response->getData();

echo "Pagamento criado com sucesso!" . PHP_EOL;
echo "ID: " . ($payment['id'] ?? 'N/A') . PHP_EOL;
echo "Status: " . ($payment['status'] ?? 'N/A') . PHP_EOL;
echo "PIX Copia e Cola: " . ($payment['pix_code'] ?? 'N/A') . PHP_EOL;
