<?php
/**
 * Exemplo: Processar um pagamento com cartão de crédito
 *
 * Este exemplo assume que você já tem um `payer_id` e um `card_id`
 * (obtido ao salvar um cartão para um pagador).
 * 
 * Uso: WEBPAG_API_TOKEN=seu-token php examples/03-process-credit-card-payment.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use WebPag\WebPag;
use WebPag\Enums\PaymentMethod;
use WebPag\Requests\Payments\ProcessPaymentRequest;

// 1. Inicie o SDK
$webpag = WebPag::env();

// 2. Crie o DTO de requisição do pagamento
$request = new ProcessPaymentRequest();
$request->payerId = 123; // Substitua pelo ID do seu pagador
$request->cardPayerId = 456; // Substitua pelo ID do cartão salvo
$request->name = "Assinatura Mensal - Plano Pro";
$request->amount = 2990; // R$ 29,90 em centavos
$request->method = PaymentMethod::CREDIT_CARD;
$request->installments = 1; // Número de parcelas

try {
    // 3. Execute o processamento
    $payment = $webpag->payments->process($request);

    echo "Pagamento processado com sucesso!" . PHP_EOL;
    echo "ID do Pagamento: " . $payment->id . PHP_EOL;
    echo "Status: " . $payment->statusLabel . PHP_EOL;
    echo "Valor: R$ " . number_format($payment->amount / 100, 2, ',', '.') . PHP_EOL;
    echo "Recibo: " . $payment->receiptPdfPath . PHP_EOL;

} catch (\WebPag\Exceptions\ApiException $e) {
    echo "Erro ao processar pagamento: " . $e->getErrorMessage() . PHP_EOL;
    print_r($e->getResponseBody());
}