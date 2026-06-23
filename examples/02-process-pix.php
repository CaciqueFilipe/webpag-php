<?php
/**
 * Exemplo: Processar pagamento PIX
 *
 * Uso: WEBPAG_API_TOKEN=seu-token php examples/02-process-pix.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use WebPag\WebPag;
use WebPag\Enums\PaymentMethod;
use WebPag\Requests\Payments\ProcessPaymentRequest;

// Cria instância a partir de variáveis de ambiente
$webpag = WebPag::env();

$request = new ProcessPaymentRequest();
$request->payerId = 15;
$request->name = 'Pedido #1234';
$request->amount = 1500; // R$ 15,00 em centavos
$request->method = PaymentMethod::PIX;

try {
    $payment = $webpag->payments->process($request);
    echo "Pagamento criado com sucesso!" . PHP_EOL;
    echo "ID: " . ($payment->id ?? 'N/A') . PHP_EOL;
    echo "Status: " . ($payment->status ?? 'N/A') . PHP_EOL;
    echo "PIX Copia e Cola: " . ($payment->pixCode ?? 'N/A') . PHP_EOL;
} catch (\WebPag\Exceptions\ApiException $e) {
    echo "Erro ao criar link de pagamento: " . $e->getErrorMessage() . PHP_EOL;
    print_r($e->getResponseBody());
}
