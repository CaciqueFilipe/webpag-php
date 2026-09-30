<?php

/**
 * Exemplo: Listar pagamentos com filtros e paginação
 *
 * Este exemplo demonstra como listar os pagamentos, aplicando
 * filtros por status e data, e percorrer todas as páginas do resultado.
 *
 * O list() retorna um WebPag\Responses\Pagination\PaginatedCollection:
 * funciona como array (foreach, count, $lista[0]) e também expõe
 * total(), currentPage(), lastPage(), hasMorePages() e nextPage().
 *
 * Uso: WEBPAG_API_TOKEN=seu-token php examples/05-list-payments.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use WebPag\Enums\PaymentStatus;
use WebPag\WebPag;

// 1. Inicie o SDK
$webpag = WebPag::env();

// 2. Defina os filtros (opcional)
$filters = [
    'status' => PaymentStatus::PAID, // Apenas pagamentos confirmados
    'created_at_start' => '2024-01-01',
    'created_at_end' => '2024-12-31',
    'page' => 1,
    'per_page' => 15,
];

try {
    do {
        // 3. Busque a página atual
        $payments = $webpag->payments->list($filters);

        echo sprintf(
            "Página %d de %d (%d pagamentos no total)",
            $payments->currentPage(),
            $payments->lastPage(),
            $payments->total()
        ) . PHP_EOL;

        foreach ($payments as $payment) {
            // amount é em centavos; fee_value já vem em reais
            echo sprintf(
                "- ID: %d, Método: %s, Status: %s, Valor: R$ %.2f, Taxa: R$ %.2f",
                $payment->id,
                $payment->methodLabel,
                $payment->statusLabel,
                $payment->amount / 100,
                $payment->feeValue
            ) . PHP_EOL;

            if ($payment->pix !== null) {
                echo "  PIX txid: " . $payment->pix->txid . PHP_EOL;
            }

            if ($payment->cardFlag !== null) {
                echo "  Bandeira: " . $payment->cardFlagLabel . PHP_EOL;
            }

            foreach ((array) $payment->creditSchedule as $schedule) {
                echo sprintf(
                    "  Recebível: R$ %.2f em %s (%s)",
                    $schedule->amount / 100,
                    $schedule->dateScheduled,
                    $schedule->credited ? 'creditado' : 'pendente'
                ) . PHP_EOL;
            }
        }

        // 4. Avance para a próxima página, se houver
        $filters['page'] = $payments->nextPage();
    } while ($filters['page'] !== null);
} catch (\WebPag\Exceptions\ApiException $e) {
    echo "Erro ao listar pagamentos: " . $e->getErrorMessage() . PHP_EOL;
}
