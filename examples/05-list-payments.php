<?php
/**
 * Exemplo: Listar pagamentos com filtros
 *
 * Este exemplo demonstra como listar os pagamentos, aplicando
 * filtros por status e data.
 * 
 * Uso: WEBPAG_API_TOKEN=seu-token php examples/05-list-payments.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use WebPag\WebPag;
use WebPag\Enums\PaymentStatus;

// 1. Inicie o SDK
$webpag = WebPag::env();

// 2. Defina os filtros (opcional)
$filters = [
    'status' => PaymentStatus::PAID, // Apenas pagamentos confirmados
    'start_date' => '2024-01-01',
    'end_date' => '2024-12-31',
];

try {
    // 3. Execute a listagem
    $payments = $webpag->payments->list($filters);

    echo "Encontrados " . count($payments) . " pagamentos." . PHP_EOL;
    foreach ($payments as $payment) {
        echo sprintf(
            "- ID: %d, Status: %s, Valor: %.2f",
            $payment->id, $payment->statusLabel,
            $payment->amount / 100
        ) . PHP_EOL;
    }
} catch (\WebPag\Exceptions\ApiException $e) {
    echo "Erro ao listar pagamentos: " . $e->getErrorMessage() . PHP_EOL;
}