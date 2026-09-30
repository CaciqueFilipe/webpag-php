# WebPag PHP SDK

[![Latest Stable Version](https://poser.pugx.org/filipecacique/webpag-php/v/stable)](https://packagist.org/packages/filipecacique/webpag-php)
[![Total Downloads](https://poser.pugx.org/filipecacique/webpag-php/downloads)](https://packagist.org/packages/filipecacique/webpag-php)
[![License](https://poser.pugx.org/filipecacique/webpag-php/license)](https://packagist.org/packages/filipecacique/webpag-php)
[![PHP Version Require](https://poser.pugx.org/filipecacique/webpag-php/require/php)](https://packagist.org/packages/filipecacique/webpag-php)

SDK PHP para integração com a [API WebPag](https://api.webpag.com.br/docs). Compatível com **PHP puro (7.2.5+)** e **Laravel (5.8+)** — o Laravel é opcional.

## Instalação

```bash
composer require filipecacique/webpag-php
```

> Confira o nome do pacote: `filipecacique/webpag-php`. Pacotes com nomes parecidos não são deste projeto.

A dependência principal é apenas o **Guzzle 7.15.2+**. Versões anteriores, incluindo toda a série 6.x, têm vulnerabilidades conhecidas e não são aceitas. O suporte a Laravel (`Service Provider` e `Facade`) é carregado automaticamente só quando o pacote é instalado em um projeto Laravel — em PHP puro, nada disso é necessário.

## Configuração

Obtenha sua chave API (`auth-token`) com o suporte WebPag.

### Configuração via variáveis de ambiente (recomendado)

Defina as variáveis no seu ambiente ou arquivo `.env`:

```env
WEBPAG_API_TOKEN=seu-token-aqui
WEBPAG_BASE_URL=https://api.webpag.com.br
WEBPAG_TIMEOUT=30
```

Depois é só usar:

```php
use WebPag\WebPag;

$webpag = WebPag::env();
```

### Configuração via Environment (PHP puro)

```php
use WebPag\WebPag;
use WebPag\Environment;

// A partir de um array
$webpag = WebPag::fromEnvironment(
    Environment::fromArray([
        'api_token' => 'seu-token-aqui',
        'base_url' => 'https://api.webpag.com.br',
        'timeout' => 30,
    ])
);

// Ou programaticamente
$env = new Environment();
$env->setApiToken('seu-token-aqui')
    ->setBaseUrl('https://api.webpag.com.br')
    ->setTimeout(30);

$webpag = WebPag::fromEnvironment($env);
```

### Configuração direta

```php
use WebPag\WebPag;

$webpag = WebPag::create('seu-token-aqui');
// ou com URL personalizada
$webpag = WebPag::create('seu-token-aqui', 'https://api.webpag.com.br');
```

### Uso em Laravel

1. Publique a configuração:

```bash
php artisan vendor:publish --tag=webpag-config
```

2. Configure no `.env`:

```env
WEBPAG_API_TOKEN=sua-chave-api
WEBPAG_BASE_URL=https://api.webpag.com.br
```

3. Use via Facade ou injeção de dependência:

```php
use WebPag\Laravel\Facades\WebPag;

Route::get('/pagadores', function () {
    // O método list() retorna uma PaginatedCollection de DTOs `Payer`.
    // Ela implementa JsonSerializable: gera {"data": [...], "links": {...}, "meta": {...}}.
    $payers = WebPag::payers()->list(['page' => (int) request('page', 1)]);
    return response()->json($payers);
});
```

```php
use WebPag\WebPag;

class PaymentController extends Controller
{
    /** @var WebPag */
    private $webpag;

    public function __construct(WebPag $webpag)
    {
        $this->webpag = $webpag;
    }
}
```

## Recursos disponíveis

| Recurso | Propriedade | Endpoints |
|---------|-------------|-----------|
| Crediário | `$webpag->installments` | list, create, find, cancel |
| Empresa | `$webpag->business` | authenticate, me, cardTokenPublicKey, createFranchise |
| Links de pagamento | `$webpag->paymentLinks` | list, create |
| Pagadores | `$webpag->payers` | list, find, create, update, inactivate, saveCreditCard, removeCreditCard |
| Pagamentos | `$webpag->payments` | list, process, find, cancel, refund, findRefund, markAsPaidDev |
| Recorrência | `$webpag->recurrency` | create, list, update, cancel |
| Transferências | `$webpag->transfers` | list, create, find, cancel, changeStatusDev |
| Webhooks | `$webpag->webhooks` | parse |

## Exemplos de uso

### Processar pagamento via PIX

```php
use WebPag\WebPag;
use WebPag\Enums\PaymentMethod;

$webpag = WebPag::env();

// O retorno já é um DTO de resposta, pronto para uso.
$payment = $webpag->payments->process([
    'payer_id' => 15,
    'name' => 'Pedido #1234',
    'amount' => 1500, // R$ 15,00 em centavos
    'method' => PaymentMethod::PIX,
]);

// $payment é um objeto WebPag\Responses\Payments\Payment
echo "Pagamento criado com ID: " . $payment->id . PHP_EOL;
echo "Status: " . $payment->statusLabel . PHP_EOL;
echo "PIX Copia e Cola: " . $payment->pix->qrcodeData . PHP_EOL;
```

### Listar com paginação

Todos os métodos `list()` retornam um `WebPag\Responses\Pagination\PaginatedCollection`. Ele se comporta como um array somente leitura (`foreach`, `count()`, `$lista[0]`) e também expõe os dados de paginação da API:

```php
$filters = ['page' => 1, 'per_page' => 15];

do {
    $payments = $webpag->payments->list($filters);

    foreach ($payments as $payment) {
        echo $payment->id . ' - ' . $payment->statusLabel . PHP_EOL;
    }

    $filters['page'] = $payments->nextPage(); // null na última página
} while ($filters['page'] !== null);
```

| Método | Retorno |
|--------|---------|
| `all()` | `array` com os DTOs da página (use com `array_map`, `array_filter` etc.) |
| `first()` / `isEmpty()` | primeiro item ou `null` / se a página está vazia |
| `total()`, `perPage()`, `currentPage()`, `lastPage()` | `int` ou `null` quando a API não envia paginação |
| `hasMorePages()` / `nextPage()` | se existe próxima página / número dela ou `null` |
| `getMeta()` / `getLinks()` | `PaginationMeta` (chave `meta`) / `PaginationLinks` (chave `links`) |
| `toArray()` | `['data' => [...], 'links' => [...], 'meta' => [...]]` em snake_case |

> **Migração:** antes o `list()` retornava `array`. `foreach`, `count()` e acesso por índice continuam funcionando; para funções nativas de array (`array_map`, `is_array`...), use `$lista->all()`.

### Usando DTOs tipados

```php
use WebPag\Requests\Payments\ProcessPaymentRequest;
use WebPag\Enums\PaymentMethod;

$request = new ProcessPaymentRequest();
$request->payerId = 15;
$request->name = 'Pedido #1234';
$request->amount = 1500;
$request->method = PaymentMethod::PIX;

$response = $webpag->payments->process($request);
```

## DTOs de requisição

Classes tipadas em `WebPag\Requests\*` implementam `RequestPayload` e possuem método `toArray()`:

- `WebPag\Requests\Installments\CreateInstallmentRequest`
- `WebPag\Requests\Business\AuthenticateRequest`
- `WebPag\Requests\Business\CreateFranchiseRequest`
- `WebPag\Requests\PaymentLinks\CreatePaymentLinkRequest`
- `WebPag\Requests\Payers\CreatePayerRequest`
- `WebPag\Requests\Payers\UpdatePayerRequest`
- `WebPag\Requests\Payers\SaveCreditCardRequest`
- `WebPag\Requests\Payers\Address`
- `WebPag\Requests\Payments\ProcessPaymentRequest`
- `WebPag\Requests\Payments\RefundPaymentRequest`
- `WebPag\Requests\Payments\ListPaymentsRequest`
- `WebPag\Requests\Recurrency\CreateRecurrencyRequest`
- `WebPag\Requests\Transfers\CreateTransferRequest`
- e outros...

## Constantes

Enums disponíveis em `WebPag\Enums\`:

- `PaymentMethod` — `credit_card`, `pix`, `bank_slip`
- `RecurrencyFrequency` — `monthly`, `bimonthly`, `quarterly`, `semiannual`, `yearly`
- `PaymentStatus` — status numéricos de pagamento (10 a 90)
- `TransferDestinationType`, `TransferType`, `PixKeyType`, etc.

## DTOs de Resposta

Assim como as requisições, as respostas dos endpoints também são encapsuladas em DTOs tipados, localizados em `WebPag\Responses\*`. Todas implementam `ResponsePayload` e são criadas a partir do método estático `fromArray()`.

As propriedades são públicas para fácil acesso aos dados:

```php
$payment = $webpag->payments->find(123);

echo $payment->id;
echo $payment->statusLabel;
echo $payment->amount;      // int, em centavos (156 = R$ 1,56)
echo $payment->feeValue;    // float, em reais (0.36)
echo $payment->pix->amount; // float, em reais (2.55)
```

Alguns dos principais DTOs de resposta são:

- `WebPag\Responses\Business\Business`
- `WebPag\Responses\Card\CardToken`
- `WebPag\Responses\Card\CreditCard`
- `WebPag\Responses\Installments\InstallmentPlan`
- `WebPag\Responses\Installments\Installment`
- `WebPag\Responses\PaymentLinks\PaymentLink`
- `WebPag\Responses\Payers\Payer` — inclui `address` (`Address`) e `cards` (`CreditCard[]`)
- `WebPag\Responses\Payments\Payment` — inclui `pix` (`Pix`), `boleto` (`BankSlip`), `transactions` (`Transaction[]`), `splits` (`Split[]`), `refunds` (`Refund[]`) e `creditSchedule` (`CreditSchedule[]`)
- `WebPag\Responses\Payments\Refund`
- `WebPag\Responses\Pagination\PaginatedCollection` — retorno de todos os `list()`
- `WebPag\Responses\Recurrency\Recurrency` — estende `Payment` (a API retorna a mesma estrutura)
- `WebPag\Responses\Transfers\Transfer`
- e outros...

## Webhooks

Sempre **valide a assinatura** antes de processar a notificação. `parseVerified()` valida e interpreta em uma única chamada, e lança `WebPagException` se a assinatura for inválida:

```php
use WebPag\Enums\PaymentStatus;
use WebPag\Exceptions\WebPagException;

// Corpo BRUTO da requisição: não faça json_decode/json_encode antes de validar
$rawPayload = $request->getContent();
$signature = $request->header('X-Webpag-Signature'); // null se ausente
$apiToken = config('webpag.api_token');

try {
    $event = $webpag->webhooks->parseVerified($rawPayload, $signature, $apiToken);
} catch (WebPagException $e) {
    abort(401); // resposta genérica, sem detalhar o motivo
}

if ($event->isPayment()) {
    $payment = $event->getPayload(); // WebPag\Responses\Payments\Payment

    if ($payment->status === PaymentStatus::PAID) {
        // Libere o pedido $payment->orderId uma única vez (a WebPag pode reenviar o evento)
    }
}
```

`verifySignature()` continua disponível para validar separadamente. Ela retorna `false`, sem lançar exceção, quando o header está ausente ou malformado ou quando o token está vazio.

## Tratamento de erros

```php
use WebPag\Exceptions\ApiException;

try {
    $webpag->payments->find(99999);
} catch (ApiException $e) {
    $body = $e->getResponseBody(); // Corpo completo: pode conter dados pessoais, não exiba ao usuário final
    echo "HTTP Status: " . $e->getStatusCode();      // 404
    echo "Mensagem: " . $e->getErrorMessage();    // Mensagem da API
    echo "Código Erro: " . $e->getErrorCode();   // Erro WebPag identificador

    // Detalhe especifico de falha:
    $detalhes = $e->getErrorPrevious();
    if (isset($detalhes['erros'][0]['mensagem'])) {
        echo "Causa real: " . $detalhes['erros'][0]['mensagem'];
    }
}
```

## Resposta da API

Os métodos dos recursos (ex: `$webpag->payments->find(123)`) retornam **DTOs de resposta** (como `WebPag\Responses\Payments\Payment`), que encapsulam os dados da API de forma tipada. Veja a seção "DTOs de Resposta" para uma lista.

Para casos onde você precise de acesso ao objeto de resposta HTTP completo (status, headers), você pode interagir diretamente com o `HttpClient`. A maioria dos usuários, no entanto, irá preferir a simplicidade dos DTOs.

O `HttpClient` interno retorna um objeto `WebPag\Http\ApiResponse` que oferece métodos como `getStatusCode()`, `getData()`, `toArray()`, e acesso `ArrayAccess` ao corpo da resposta.


## Segurança

O SDK já vem com estas proteções ativas:

| Proteção | Comportamento |
|----------|---------------|
| HTTPS obrigatório | `base_url` com `http://` gera `InvalidArgumentException` (exceto `localhost`/`127.0.0.1`), para que o token nunca trafegue sem criptografia |
| Sem redirecionamentos | Redirects nunca são seguidos, porque o header `auth-token` iria junto para outro host |
| TLS verificado | O certificado do servidor é sempre validado (`verify => true`) |
| Retry sem duplicar operações | `POST`/`PUT`/`DELETE` (cobrança, estorno, saque) **não** são repetidos após erro 5xx ou falha de rede; só após `429`. `GET` é repetido com backoff |
| IDs validados | IDs de caminho são validados e codificados (`rawurlencode`), bloqueando path traversal como `"1/../../transfers"` |
| Logs sem dados pessoais | O logger PSR-3 recebe só os **nomes** dos filtros, nunca os valores (CPF, e-mail...) nem o token |
| Debug mascarado | `var_dump`/`print_r`/`dd` mostram token e cartão mascarados (`****cdef`, `************1111`) |
| Webhook | `verifySignature` usa comparação em tempo constante e recusa token vazio ou assinatura malformada |

### Boas práticas para quem integra

- **Token:** guarde em variável de ambiente ou num cofre de segredos. Nunca versione e nunca envie ao frontend. Se vazar, gere outro com a WebPag.
- **Cartão:** prefira `card_token` (tokenização no frontend com `business->cardTokenPublicKey()`). Enviar número e CVV pelo seu servidor coloca a sua aplicação no escopo PCI-DSS.
- **Falha de rede em POST:** se `process()`, `create()` ou `refund()` lançar erro de comunicação, **consulte** o recurso (ex: por `order_id`) antes de tentar de novo, para não duplicar a operação.
- **Webhooks:** use `parseVerified()`, responda rápido e trate eventos repetidos (idempotência pelo ID).
- **Erros:** `ApiException::getResponseBody()` e `getErrorTrace()` podem conter dados pessoais e detalhes internos. Registre nos seus logs, mas não exiba ao usuário final.

## Desenvolvimento

O `composer.lock` não é versionado (pacote de biblioteca), para que as dependências sejam resolvidas de acordo com a versão de PHP de cada ambiente (7.2 a 8.4):

```bash
composer update
composer test
```

## Licença

MIT

## Outras Informações
> "Este é um SDK independente. Para suporte customizado ou implementações complexas, entre em contato comigo."
