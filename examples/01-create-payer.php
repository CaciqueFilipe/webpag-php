<?php

/**
 * Exemplo: Criar pagador usando DTO tipado
 *
 * Uso: WEBPAG_API_TOKEN=seu-token php examples/01-create-payer.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use WebPag\Enums\Gender;
use WebPag\Requests\Payers\Address;
use WebPag\Requests\Payers\CreatePayerRequest;
use WebPag\WebPag;

$webpag = WebPag::env();

// Usando DTO tipado
$request = new CreatePayerRequest();
$request->firstName = 'João';
$request->lastName = 'Silva';
$request->email = 'joao@example.com';
$request->isBusiness = false;
$request->cpfCnpj = '11122233344';
$request->phoneNumber = '11999999999';
$request->gender = Gender::MALE;
$request->birthDate = '1990-01-15';

$address = new Address();
$address->zipCode = '01001-000';
$address->street = 'Rua Exemplo';
$address->number = '123';
$address->district = 'Centro';
$address->city = 'São Paulo';
$address->state = 'SP';
$request->address = $address;

try {
    // 3. Execute a criação
    $payer = $webpag->payers->create($request);

    echo "Pagador criado com sucesso!" . PHP_EOL;
    echo "ID: " . ($payer->id ?? 'N/A') . PHP_EOL;
    echo "Nome: " . ($payer->firstName ?? '') . ' ' .
        ($payer->lastName ?? '') . PHP_EOL;
    echo "Email: " . ($payer->email ?? '') . PHP_EOL;

} catch (\WebPag\Exceptions\ApiException $e) {
    echo "Erro ao criar pagador: " . $e->getErrorMessage() . PHP_EOL;
    // O getResponseBody() contém os detalhes dos erros de validação
    print_r($e->getResponseBody());
}
