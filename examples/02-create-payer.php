<?php
/**
 * Exemplo: Criar pagador usando DTO tipado
 *
 * Uso: WEBPAG_API_TOKEN=seu-token php examples/02-create-payer.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use WebPag\WebPag;
use WebPag\Requests\Payers\CreatePayerRequest;
use WebPag\Requests\Payers\Address;
use WebPag\Enums\Gender;

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

$response = $webpag->payers->create($request);

$payer = $response->getData();

echo "Pagador criado!" . PHP_EOL;
echo "ID: " . ($payer['id'] ?? 'N/A') . PHP_EOL;
echo "Nome: " . ($payer['first_name'] ?? '') . ' ' . ($payer['last_name'] ?? '') . PHP_EOL;
