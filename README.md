# WebPag PHP SDK

SDK PHP para integração com a [API WebPag](https://api.webpag.com.br/docs). Compatível com **PHP puro (7.2+)** e **Laravel (5.8+)** — o Laravel é opcional.

## Instalação

```bash
composer require webpag/webpag-php
```

A dependência principal é apenas o **Guzzle**. O suporte a Laravel (`Service Provider` e `Facade`) é carregado automaticamente só quando o pacote é instalado em um projeto Laravel — em PHP puro, nada disso é necessário.

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
    return WebPag::payers->list()->getData();
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

$response = $webpag->payments->process([
    'payer_id' => 15,
    'name' => 'Pedido #1234',
    'amount' => 1500, // R$ 15,00 em centavos
    'method' => PaymentMethod::PIX,
]);

$payment = $response->getData();
```

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

## Webhooks

Para processar notificações recebidas da WebPag:

```php
$event = $webpag->webhooks->parse($request->getContent());

if ($event->isPayment() && $event->getStatus() === 40) {
    // Pagamento confirmado
    $paymentId = $event->get('id');
}

// Valide o business.id para garantir autenticidade
$businessId = $event->getBusinessId();
```

## Tratamento de erros

```php
use WebPag\Exceptions\ApiException;

try {
    $webpag->payments->find(99999);
} catch (ApiException $e) {
    echo $e->getStatusCode();      // 404
    echo $e->getErrorMessage();    // Mensagem da API
    print_r($e->getResponseBody()); // Corpo completo
}
```

## Resposta da API

Todas as chamadas retornam `WebPag\Http\ApiResponse`:

```php
$response = $webpag->payments->find(123);

$response->getStatusCode(); // HTTP status
$response->getData();      // Conteúdo de "data" ou corpo completo
$response->toArray();      // Corpo JSON decodificado
$response['message'];      // ArrayAccess
```

## Licença

MIT

## Outras Informações
> "Este é um SDK independente. Para suporte customizado ou implementações complexas, entre em contato comigo."
