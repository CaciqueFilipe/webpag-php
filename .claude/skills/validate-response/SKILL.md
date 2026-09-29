---
name: validate-response
description: Confere campo a campo se o DTO de resposta do SDK WebPag corresponde a um retorno real da API (colado ou salvo em response.json) e corrige tipos, campos faltantes, testes, fixtures e docs. Use quando o usuário enviar o retorno de um endpoint para verificar a tipagem.
---

# Validar DTO contra resposta real da API

Entrada: o retorno real de um endpoint, normalmente em `response.json` na raiz (arquivo **ignorado pelo git**, porque contém dados pessoais), e o nome do endpoint.

## 1. Localizar o código
- Ache o método em `src/Resources/*.php` que chama o endpoint e o DTO que ele retorna (`src/Responses/**`).
- Veja o envelope: `{"data": [...], "links", "meta"}` é paginado (o `list()` retorna `PaginatedCollection`); `{"data": {...}}` é objeto único (o `getData()` já tira o envelope); objeto sem `data` também funciona.

## 2. Comparar campo a campo
Monte uma tabela **Campo | JSON | DTO | OK** cobrindo:
- **Campos faltando no DTO**: vêm na API, mas não são lidos. São os mais comuns: listas aninhadas como `cards`, `transactions`, `splits`.
- **Tipo errado**: número vindo como string decimal (`"2.55"`) precisa de `(float)`, nunca `(int)`. ID numérico precisa de `(int)`. Documento (CPF/CNPJ/CEP) precisa de `string`, para manter os zeros à esquerda.
- **Unidade**: inteiro em centavos ou decimal em reais. Anote no docblock `(em centavos)` ou `(em reais, ex: 2.55)`.
- **Nulos**: todo campo `|null`; confira que `fromArray` aceita `null` e `[]`.
- **Estrutura duplicada**: se o formato for idêntico ao de outro DTO, prefira herança (`class X extends Payment {}`) a copiar.
- **Campos no DTO que não vêm na API**: mantenha (podem vir em outro endpoint), mas registre no resumo.

Desconfie também do *conteúdo*: por exemplo, uma lista de "recorrências" com todos os itens `is_recurrent: false` merece ser mencionada ao usuário.

## 3. Corrigir seguindo o CLAUDE.md
- `fromArray` com casts explícitos, `toArray` em snake_case filtrando `null`, sem `get_object_vars`.
- Listas aninhadas: `null` quando ausentes, `[]` quando vazias.
- Código compatível com **PHP 7.2** (sem typed properties, `fn`, `match`, `?->`, union types).

## 4. Testes
- Crie ou atualize `tests/fixtures/<endpoint>.json` com **no máximo 2-3 itens representativos** (ex: um PIX, um cartão, um com campos nulos) e **dados anonimizados**:
  - CPF `00000000191`/`00000000272`, CNPJ `00000000000191`, e-mails `@example.com`, nomes `FULANO DE TAL`, empresa `EMPRESA TESTE`, rua `Rua Exemplo`.
  - Troque URLs de webhook de clientes por `https://example.com/...`.
  - Nunca copie `response.json` direto para `tests/`.
- Em `tests/<Recurso>ResponseTest.php`: mock do `HttpClient`, chamada do método real do resource, `assertSame` em **cada campo** (tipo estrito), paginação quando houver e `toArray()`.
- Rode `php vendor/phpunit/phpunit/phpunit --no-coverage` e confirme que tudo passa.

## 5. Documentação
- Atualize a lista de DTOs no `README.md` se surgir campo aninhado relevante.
- Atualize em `CLAUDE.md` a lista "Endpoints já validados contra resposta real".

## 6. Responder
Em português, curto: o que estava errado (tabela Antes | Na API | Correção), o que já estava certo, testes adicionados e o resultado da suíte. No fim, lembre que `response.json` tem dados reais e não deve ser commitado.
