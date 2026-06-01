# AGENTS.md

This file provides guidance for AI coding agents working with this repository. Follows the [AGENTS.md](https://agents.md/) convention.

## Project

Unofficial PHP 8.4+ SDK for the [Billomat API](https://www.billomat.com/api/). Library only — no application/runtime. Distributed as `justpilot/billomat-php-api`. Inline documentation and code comments are in German; identifiers and public API are English.

## Commands

All workflows run via Composer scripts — `composer.json` is the source of truth.

```bash
composer install                # Install dependencies
composer test                   # Unit tests only (fast, no network)
composer test:integration       # Integration tests against Billomat sandbox
composer test:all               # Unit + integration
composer test:coverage          # PHPUnit with HTML+Clover coverage in build/

composer lint                   # PHP-CS-Fixer (dry-run)
composer lint:fix               # PHP-CS-Fixer (apply)
composer analyse                # PHPStan level max (uses phpstan-baseline.neon)
composer analyse:baseline       # Regenerate the PHPStan baseline
composer refactor               # Apply Rector
composer refactor:dry           # Show Rector suggestions
composer mutate                 # Infection mutation testing (slow)
composer audit                  # Security audit of dependencies
composer ci                     # Full local CI: lint + analyse + refactor:dry + test:all
```

Single test file/method:

```bash
vendor/bin/phpunit tests/Api/ClientsApiTest.php
vendor/bin/phpunit --filter it_lists_clients_and_passes_filters
```

Integration tests under `tests/Integration/` hit the real Billomat sandbox. They `markTestSkipped` unless `BILLOMAT_ID` and `BILLOMAT_API_KEY` are set — put real credentials in `.env.test.local` (gitignored); `.env.test` is the placeholder template. Bootstrap (`tests/bootstrap.php`) loads both via `symfony/dotenv` with `usePutenv()`, so `getenv()` works in tests.

CI runs via `.github/workflows/ci.yml` on PHP 8.4 (required) and 8.5 (experimental). Three jobs: tests, quality (lint + PHPStan + Rector + audit), and mutation testing on PRs.

## Architecture

### Entry point: `BillomatClient`
`src/BillomatClient.php` is a thin façade exposing each resource as a public readonly property:

```php
$billomat = BillomatClient::create(billomatId: 'mycompany', apiKey: '…');
$billomat->clients->list();
$billomat->invoices->create($options);
$billomat->invoices->pdf($id, InvoicePdfType::SIGNED);
```

Construction wires `BillomatHttpClient` (Symfony `HttpClient` under the hood) into every `*Api` instance. A custom `HttpClientInterface` can be injected for testing or interception.

### Three-layer pattern per resource

Every Billomat resource follows the same triad:

1. **`src/Api/{Resource}Api.php`** — extends `AbstractApi`, exposes verbs (`list`, `get`, `create`, `update`, `delete`, plus resource-specific actions like `complete`, `cancel`, `pdf`). Always wraps/unwraps the Billomat response envelope (`{ "clients": { "client": [...] } }` or `{ "client": {...} }`). When listing, Billomat returns either a single object or a list under the inner key — code must normalize via `array_is_list()` check (see `ClientsApi::list`).
2. **`src/Api/{Resource}CreateOptions.php` / `{Resource}UpdateOptions.php`** — typed write models. Public nullable properties; `toArray()` snake-cases keys to Billomat's wire format and strips nulls with `array_filter`.
3. **`src/Model/{Resource}.php`** — `final readonly` read model with `fromArray()` hydration and `toArray()` for debug/log output. Snake_case ↔ camelCase mapping lives in `fromArray`/`toArray`.

When adding a new resource, register it as a public readonly property on `BillomatClient` and instantiate it in the constructor.

### HTTP layer

- `BillomatHttpClient::request()` builds the URL from `BillomatConfig::getBaseUri()` (`https://{billomatId}.billomat.net/api/`), adds auth headers (`X-BillomatApiKey`, optional `X-AppId`/`X-AppSecret`), and serializes the body as JSON.
- Query strings are built manually in `buildBillomatQuery()`, **not** via `http_build_query`. Two Billomat quirks the helper preserves:
  - Array values serialize as `key[]=v1&key[]=v2`.
  - `+` is left literal (e.g. `order_by=date+DESC`). After `rawurlencode`, `%2B` is converted back to `+` — Billomat rejects `%2B`.

### Error mapping

`AbstractApi::mapHttpException()` translates Symfony's `HttpExceptionInterface` (4xx/5xx) into SDK exceptions, all extending `BillomatException`:

| Status | Exception |
|---|---|
| 401, 403 | `AuthenticationException` |
| 404 | `NotFoundException` |
| 400, 422 | `ValidationException` |
| other | `HttpException` |

`getJsonOrNull()` swallows `NotFoundException` so `get()` can return `null` for missing resources. The raw response body is preserved on the exception for debugging.

### Binary responses

For endpoints that may return binary (e.g. `GET /invoices/{id}/pdf?format=pdf`), bypass `getJson()` and call `$this->http->request(...)` directly, then `$response->getContent()`. Pattern is in `InvoicesApi::pdf()`. Helper `AbstractApi::getRaw()` exists for read-only binary endpoints.

## Conventions

- PHP 8.4+ only. Use `final readonly` for read models, `readonly` properties on `BillomatClient`, named arguments, enums (`src/Model/Enum/`), and constructor property promotion.
- `declare(strict_types=1);` at the top of every file.
- All API classes are `final`; models are `final readonly`.
- PSR-4: `Justpilot\Billomat\` → `src/`, `Justpilot\Billomat\Tests\` → `tests/`.
- Unit tests use `Symfony\Component\HttpClient\MockHttpClient` + `MockResponse` to capture the outgoing request and assert URL/method/headers/body. Don't mock `BillomatHttpClient` itself.
- Test methods use PHPUnit 12 attributes: `#[Test]` on each method (no `test_` prefix), `#[CoversClass]` on unit-test classes, `#[CoversNothing]` on integration tests, `#[DataProvider]` for providers. Assertions are called statically (`self::assertX()`).
- Comments are in German throughout. Match the existing language when editing existing files; new files can follow the same style.
