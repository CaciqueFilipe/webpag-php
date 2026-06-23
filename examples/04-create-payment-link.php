<?php

/**
 * Exemplo: Criar um link de pagamento
 *
 * Este exemplo demonstra como criar um link de pagamento simples.
 *
 * Uso: WEBPAG_API_TOKEN=seu-token php examples/04-create-payment-link.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use WebPag\Requests\PaymentLinks\CreatePaymentLinkRequest;
use WebPag\WebPag;

// 1. Inicie o SDK
$webpag = WebPag::env();

// 2. Crie o DTO de requisição
$request = new CreatePaymentLinkRequest();
$request->name = "Consultoria de Marketing Digital";
$request->description = "Pagamento referente à consultoria inicial.";
$request->value = 50000; // R$ 500,00 em centavos

try {
    // 3. Execute a criação
    $paymentLink = $webpag->paymentLinks->create($request);

    echo "Link de pagamento criado com sucesso!" . PHP_EOL;
    echo "URL: " . $paymentLink->url . PHP_EOL;
} catch (\WebPag\Exceptions\ApiException $e) {
    echo "Erro ao criar link de pagamento: " . $e->getErrorMessage() . PHP_EOL;
    print_r($e->getResponseBody());
}
