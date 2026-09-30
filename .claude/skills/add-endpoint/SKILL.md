---
name: add-endpoint
description: Adiciona um novo endpoint da API WebPag ao SDK (método no Resource, DTO de requisição e de resposta, testes e docs) seguindo as convenções e regras de segurança do projeto. Use quando o usuário pedir para suportar uma rota nova ou um recurso novo da API.
---

# Adicionar endpoint ao SDK WebPag

Antes de começar, confirme com o usuário (ou na documentação que ele enviou) o método HTTP, o caminho, os parâmetros e **um exemplo real de resposta**. Sem resposta real, marque os tipos como "não confirmados" no resumo.

## 1. Resource (`src/Resources/<Recurso>.php`)
- Um método público por endpoint, com docblock em português, `@param` e `@return`.
- IDs no caminho **sempre** via `$this->path('api/recurso/%s/acao', $id)`, nunca com concatenação.
- Corpo e filtros aceitam `RequestDto|array|null` e passam por `$this->resolvePayload($x)`.
- Listagem: `return $this->paginate($response, Dto::class);` com retorno `: PaginatedCollection`.
- Objeto único: `return $this->item($response, Dto::class);` (valida o formato; nunca `Dto::fromArray($response->getData())`). Objeto dentro de uma chave: `$this->item($response, Dto::class, 'transfer')`.
- Endpoint que só existe em sandbox: sufixo `Dev` no nome (ex: `markAsPaidDev`) e aviso no docblock.
- Recurso novo: registre a propriedade e o método acessor em `src/WebPag.php` (o acessor é necessário para a Facade) e o `@method` em `src/Laravel/Facades/WebPag.php`.

## 2. DTO de requisição (`src/Requests/<Recurso>/<Acao>Request.php`)
- `implements RequestPayload`, propriedades públicas em camelCase com `@var`.
- `toArray()` via `ArrayHelper::filterNull([...])` em snake_case; `fromArray()` com casts.
- Com dado sensível (cartão, CVV, token, senha): implemente `__debugInfo()` mascarando com `SensitiveData`.

## 3. DTO de resposta (`src/Responses/<Recurso>/<Nome>.php`)
Siga "DTOs de resposta" e "Unidades monetárias" do `CLAUDE.md`: casts explícitos, `toArray` explícito em snake_case, sem `get_object_vars`. Reutilize DTOs existentes (`Payer`, `Business`, `Pix`, `CreditCard`...) para objetos aninhados.

## 4. Segurança (checklist)
- [ ] Nenhum ID concatenado no caminho.
- [ ] Nada sensível em log, mensagem de exceção ou `toArray()` de resposta.
- [ ] Método não idempotente (cobra, estorna, transfere)? O `HttpClient` já não repete: **não** crie retry próprio.
- [ ] Dado de cartão, senha ou token no request? Então `__debugInfo()` mascarado + teste em `SecurityTest`.
- [ ] Listas aninhadas no DTO de resposta passam por `ArrayHelper::onlyArrays()`.
- [ ] Teste com resposta malformada (`data` string/null) esperando `ApiException`, em `VulnerabilityTest`.

## 5. Testes
- Resource: mock do `HttpClient` com `->with('api/caminho/esperado', [...])`, validando URI e payload.
- Resposta: fixture anonimizada em `tests/fixtures/` + `assertSame` por campo (veja a skill `validate-response`).
- Request: caso em `tests/RequestPayloadsTest.php` para `toArray()` e `fromArray()`.
- Rode a suíte no PHP 7.2 local: `php vendor/phpunit/phpunit/phpunit --no-coverage`.

## 6. Documentação
- README: tabela "Recursos disponíveis", lista de DTOs e, se útil, um exemplo.
- Se fizer sentido, um script novo em `examples/`, lendo o token só de `WEBPAG_API_TOKEN` e sem valor padrão fixo.
