# CLAUDE.md

SDK PHP (com integração opcional ao Laravel) para a API de pagamentos WebPag (PIX, cartão, boleto, recorrência, saques). Pacote Composer `filipecacique/webpag-php`, namespace `WebPag\` → `src/`.

## Comandos

```bash
composer update                          # sem composer.lock versionado (biblioteca)
vendor/bin/phpunit --no-coverage         # ou: composer test
php -l arquivo.php                       # lint rápido
```

- A máquina de desenvolvimento roda **PHP 7.2** (`php -v`). Rode a suíte nela antes de concluir qualquer mudança.
- O CI (`.github/workflows/ci.yml`) roda PHP 7.2 → 8.4 com `composer update` (+ 8.4 `--prefer-lowest`) e parallel-lint no 8.2.

## Compatibilidade: PHP 7.2 é o mínimo

Dependências de runtime: só `ext-curl`, `ext-json` e `psr/log`. **Não adicione dependência HTTP de terceiros no `require`** (Guzzle, Symfony HttpClient, PSR-7...). Elas geram conflito de versão nos projetos que usam o SDK, e foi por isso que o Guzzle foi removido. O Guzzle fica só em `require-dev` (testes com `MockHandler`) e em `suggest`.

Proibido em `src/`, `tests/` e `examples/`: typed properties, arrow functions `fn`, `match`, `?->`, union types, `mixed`/`static` como tipo declarado, named arguments, enums nativos, `readonly`, atributos com efeito (exceto `#[\ReturnTypeWillChange]` em linha própria, que o 7.2 lê como comentário).

Permitido: `??`, `?Tipo` (nullable), `: void`, `: self`, `: array`, `: bool`, variadics `...$x`.

- Parâmetro tipado com default `null` precisa de `?`: escreva `?Client $c = null`, e não `Client $c = null` (gera deprecation no PHP 8.4).
- Implementações de `ArrayAccess`, `IteratorAggregate`, `Countable` e `JsonSerializable` precisam de tipo de retorno compatível com o PHP 8.1+, ou de `#[\ReturnTypeWillChange]`.

## Arquitetura

```
WebPag (fachada)             → recursos públicos: $webpag->payments / ->payments() (Facade Laravel)
  Resources/*                → um método por endpoint; estendem AbstractResource
    HttpClient               → auth-token, timeouts, retry, logs, erros → ApiException
      Transport/*            → TransportInterface: CurlTransport (padrão, ext-curl) ou
                               GuzzleTransport (adaptador quando um Client do Guzzle é injetado)
      ApiResponse            → getData() devolve "data" se existir, senão o corpo inteiro
  Requests/*  (RequestPayload)  → DTOs de entrada, toArray() em snake_case sem nulls
  Responses/* (ResponsePayload) → DTOs de saída, fromArray() + toArray()
  Responses/Pagination/*     → PaginatedCollection (retorno de TODOS os list())
  Webhooks/*                 → verifySignature, parseVerified, detecção do tipo de evento
```

## Convenções

**Resources**
- IDs no caminho **sempre** via `$this->path('api/x/%s/acao', $id)`. Nunca concatene `'api/x/' . $id`, porque isso abre path traversal.
- Listagens retornam `$this->paginate($response, Dto::class)`, com tipo de retorno `PaginatedCollection`.
- Filtros e corpos aceitam `RequestDto|array|null` via `$this->resolvePayload()`.

**DTOs de resposta** (`src/Responses/`)
- Propriedades públicas em camelCase com docblock `/** @var tipo|null (unidade/formato) */`. Chaves da API em snake_case.
- `fromArray()`: `isset($d['x']) ? (int) $d['x'] : null` para números e bool; `$d['x'] ?? null` para strings; objetos aninhados só se `is_array`.
- `toArray()`: array explícito em snake_case, filtrando `null`. **Não use `get_object_vars`**, que gera chaves em camelCase.
- Listas aninhadas: `null` quando a chave não veio, `[]` quando veio vazia.
- Estruturas iguais reutilizam classe via herança (`Recurrency extends Payment`), com `new static()` / `static::fromArray()`.

**Unidades monetárias** (confirmadas com respostas reais da API)
- Centavos (`int`): `Payment.amount`, `amountRefunded`, `refundedFee`, `Split.amount`, `CreditSchedule.amount`.
- Reais (`float`, a API manda string decimal como `"2.55"`): `Payment.feeValue`, `Pix.amount`.
- Nunca faça `(int)` em valor decimal: `(int) "2.55"` vira `2`. Esse bug já aconteceu.

Endpoints já validados contra resposta real: `GET api/payments`, `api/me`, `api/payers`, `api/payers/{id}`, `api/payments/recurrency/list`. Os demais DTOs (Transfer, PaymentLink, InstallmentPlan, Refund, BankSlip...) ainda não foram conferidos. Use `/validate-response` quando chegar um retorno real.

**Testes**
- Um `tests/<Recurso>ResponseTest.php` por endpoint validado, com `assertSame` (tipo estrito) para cada campo.
- Fixtures em `tests/fixtures/*.json`, **sempre anonimizadas**: CPF `00000000191`, e-mail `@example.com`, nomes fictícios.
- HTTP simulado com `$this->createMock(HttpClient::class)` ou com `MockHandler` + `Middleware::history` do Guzzle (via `GuzzleTransport`).
- O `CurlTransport` é testado de verdade em `tests/CurlTransportTest.php`, contra o servidor embutido do PHP (`tests/Support/curl_server.php`) em `127.0.0.1`. Novas rotas de teste vão nesse roteador.

## Segurança: regras obrigatórias

Este SDK lida com token de API, dados pessoais (CPF/CNPJ, e-mail, endereço) e dados de cartão. Não afrouxe nenhuma destas regras sem pedido explícito:

1. **Dados reais nunca entram no git.** Arquivos `response*.json` e `resposta*.json` na raiz são ignorados. Ao criar fixture a partir deles, anonimize tudo. Não cole CPF, e-mail, CNPJ, token ou `txid` reais em testes, docs ou commits.
2. **Token:** só é enviado no header `auth-token`. Nunca em query, log, mensagem de exceção ou `toArray()`. Classes que guardam o token implementam `__debugInfo()` com `SensitiveData::mask()`.
3. **HTTPS obrigatório** (`Configuration` recusa `http://`, exceto localhost, e token com caracteres de controle). Os dois transportes garantem **redirects desligados** e tratamento de status >= 400 pelo `HttpClient`. O `CurlTransport` também garante TLS verificado (`VERIFYPEER` + `VERIFYHOST = 2`), só HTTP/HTTPS, **`CURLOPT_PROXY = ''`** (ignora proxy do ambiente, só proxy explícito), headers sem CR/LF/NUL e limite de 10 MB por resposta. Nunca adicione opção para desligar a verificação TLS.
4. **Retry:** só `GET/HEAD/OPTIONS` são repetidos em 5xx ou erro de rede. `POST/PUT/DELETE` só em `429`. Repetir cobrança, estorno ou saque pode duplicar dinheiro.
5. **Logs:** nunca registre valores de query, body, headers ou resposta. Só método, URI, status, tempo e **nomes** dos filtros.
6. **Cartão:** DTOs com número, CVV ou `card_token` mascaram em `__debugInfo()` (`SensitiveData::maskCardNumber`). A documentação deve sempre recomendar `card_token`.
7. **Webhook:** `verifySignature` nunca lança exceção, recusa token vazio e assinatura malformada, e usa `hash_equals`. Exemplos usam `parseVerified()` e nunca um token padrão fixo no código.
8. **Dependências:** o runtime do SDK é só `psr/log` + extensões nativas. Ao mexer no `composer.json`, rode `composer audit --no-dev` com `--prefer-lowest` **e** com as versões mais novas. As dependências de dev não vão para quem instala. O `GuzzleTransport` usa o Guzzle do projeto que integra, então a documentação recomenda 7.15.2+.

9. **Respostas não confiáveis:** Resources montam objetos só via `$this->item($response, Dto::class)`, que lança `ApiException` se `data` não for objeto; nunca `Dto::fromArray($response->getData())` direto. Todo `fromArrayCollection()` e toda lista aninhada passam por `ArrayHelper::onlyArrays()`.
10. **Mensagens de erro:** exceções de transporte são sempre `TransportException`, que remove query strings e credenciais de URLs (`SensitiveData::redactUrls`). Nunca encadeie como `previous` a exceção original do Guzzle/cURL, porque ela traz a URL com dados pessoais.
11. **Serialização:** `Configuration` e `Environment` bloqueiam `__sleep`/`__wakeup`/`__serialize`/`__unserialize`. Novas classes que guardem segredo fazem o mesmo e implementam `__debugInfo()`.
12. **Limites:** respostas de 10 MB (corpo) e 64 KB (headers), TLS 1.2+, webhook de 1 MB e profundidade de JSON 64.

Cada regra tem teste em `tests/SecurityTest.php` (proteções) ou `tests/VulnerabilityTest.php` (ataques reproduzidos). Mantenha e amplie esses testes: para cada falha nova, escreva primeiro o teste que a reproduz.

## Pacote distribuído (Packagist)

O `.gitattributes` (`export-ignore`) define o que vai para quem instala: **só `src/`, `config/`, `composer.json`, `README.md` e `LICENSE`**. Testes, fixtures, exemplos, skills, CI e este arquivo ficam só no repositório. Ao criar pasta ou arquivo novo na raiz, adicione-o ao `.gitattributes`, senão o job `dist-contents` do CI falha. Para conferir localmente: `git archive --worktree-attributes --format=zip -o dist.zip HEAD`.

## Documentação

Mudanças de comportamento público atualizam o `README.md` (DTOs, paginação, segurança) e os `examples/`. Nome do pacote em docs: sempre `filipecacique/webpag-php`.

## Skills do projeto

- `/validate-response`: conferir a tipagem de um DTO contra uma resposta real da API.
- `/add-endpoint`: adicionar um endpoint novo seguindo as convenções acima.
