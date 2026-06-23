# WebPag PHP SDK

SDK PHP para integração com a [API WebPag](https://api.webpag.com.br/docs). Compatível com **PHP puro (7.4+)** e **Laravel (5.8+)** — o Laravel é opcional.

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
    // O método list() retorna um array de DTOs `Payer`.
    // O Laravel se encarrega de serializar para JSON.
    $payers = WebPag::payers->list();
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
echo "PIX Copia e Cola: " . $payment->pix['qr_code_text'] . PHP_EOL;
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

## DTOs de Resposta

Assim como as requisições, as respostas dos endpoints também são encapsuladas em DTOs tipados, localizados em `WebPag\Responses\*`. Todas implementam `ResponsePayload` e são criadas a partir do método estático `fromArray()`.

As propriedades são públicas para fácil acesso aos dados:

```php
$payment = $webpag->payments->find(123);

echo $payment->id;
echo $payment->statusLabel;
echo $payment->amount; // em centavos
```

Alguns dos principais DTOs de resposta são:

- `WebPag\Responses\Business\Business`
- `WebPag\Responses\Business\CardTokenPublicKey`
- `WebPag\Responses\Installments\Installment`
- `WebPag\Responses\PaymentLinks\PaymentLink`
- `WebPag\Responses\Payers\Payer`
- `WebPag\Responses\Payers\SavedCreditCard`
- `WebPag\Responses\Payments\Payment`
- `WebPag\Responses\Payments\Refund`
- `WebPag\Responses\Recurrency\Recurrency`
- `WebPag\Responses\Transfers\Transfer`
- e outros...

## Webhooks

Para processar notificações recebidas da WebPag, é crucial primeiro **validar a assinatura** para garantir a autenticidade da requisição.

```php
// 1. Obtenha os dados brutos e a assinatura do header
$rawPayload = $request->getContent();
$signature = $request->header('X-Webpag-Signature');
$apiToken = config('webpag.api_token'); // ou getenv('WEBPAG_API_TOKEN')

// 2. Valide a assinatura
if (!\WebPag\Webhooks\WebhookParser::verifySignature($rawPayload, $signature, $apiToken)) {
    abort(403, 'Invalid signature.');
}

// 3. Interprete o evento
$event = $webpag->webhooks->parse($rawPayload);

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

Os métodos dos recursos (ex: `$webpag->payments->find(123)`) retornam **DTOs de resposta** (como `WebPag\Responses\Payments\Payment`), que encapsulam os dados da API de forma tipada. Veja a seção "DTOs de Resposta" para uma lista.

Para casos onde você precise de acesso ao objeto de resposta HTTP completo (status, headers), você pode interagir diretamente com o `HttpClient`. A maioria dos usuários, no entanto, irá preferir a simplicidade dos DTOs.

O `HttpClient` interno retorna um objeto `WebPag\Http\ApiResponse` que oferece métodos como `getStatusCode()`, `getData()`, `toArray()`, e acesso `ArrayAccess` ao corpo da resposta.


## Licença

MIT

## Outras Informações
> "Este é um SDK independente. Para suporte customizado ou implementações complexas, entre em contato comigo."
