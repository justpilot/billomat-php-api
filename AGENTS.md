# AGENTS.md

This file provides guidance for AI coding agents working with this repository. Follows the [AGENTS.md](https://agents.md/) convention.

## Project

Unofficial PHP 8.5+ SDK for the [Billomat API](https://www.billomat.com/api/). Library only — no application/runtime. Distributed as `justpilot/billomat-php-api`. Inline documentation and code comments are in German; identifiers and public API are English.

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
composer generate               # Regenerate models/options from docs/spec/billomat.json + tools/codegen/definitions/
composer generate:check         # Fail if generated files under src/ are out of date
composer ci                     # Full local CI: lint + analyse + refactor:dry + generate:check + test:all
```

Single test file/method:

```bash
vendor/bin/phpunit tests/Api/ClientsApiTest.php
vendor/bin/phpunit --filter it_lists_clients_and_passes_filters
```

Integration tests under `tests/Integration/` hit the real Billomat sandbox. They `markTestSkipped` unless `BILLOMAT_ID` and `BILLOMAT_API_KEY` are set — put real credentials in `.env.test.local` (gitignored); `.env.test` is the placeholder template. Bootstrap (`tests/bootstrap.php`) loads both via `symfony/dotenv` with `usePutenv()`, so `getenv()` works in tests.

Branches: `4.x` collects the work for 4.0.0 (BC breaks allowed; feature branches target `4.x`). `main` only gets 3.x fixes until 4.0 is released and is merged into `4.x` regularly. Every BC break on `4.x` needs a CHANGELOG entry and a before/after section in `UPGRADE-4.0.md` (English).

CI runs via `.github/workflows/ci.yml` on PHP 8.5 (required) and 8.6 (experimental). Three jobs: tests, quality (lint + PHPStan + Rector + audit), and mutation testing on PRs.

## Architecture

### Entry point: `BillomatClient`
`src/BillomatClient.php` is a thin façade exposing each resource as a public readonly property:

```php
$billomat = BillomatClient::create(billomatId: 'mycompany', apiKey: '…');
$billomat->clients->list();
$billomat->invoices->create($options);
$billomat->invoices->pdf($id, InvoicePdfType::SIGNED);
$billomat->invoices->comments->list($invoiceId);
```

Construction wires `BillomatHttpClient` into every `*Api` instance. The transport is PSR-18: any `Psr\Http\Client\ClientInterface` can be injected; without one, `BillomatClient` uses Symfony's `Psr18Client` (with `BillomatConfig::$timeout`) if `symfony/http-client` is installed, otherwise `php-http/discovery`. PSR-17 factories come from discovery unless passed in.

### Three-layer pattern per resource

Every Billomat resource follows the same triad:

1. **`src/Api/{Resource}Api.php`** — extends `AbstractApi`, exposes verbs (`list`, `get`, `create`, `update`, `delete`, plus resource-specific actions like `complete`, `cancel`, `pdf`). Always wraps/unwraps the Billomat response envelope (`{ "clients": { "client": [...] } }` or `{ "client": {...} }`). When listing, Billomat returns either a single object or a list under the inner key — code must normalize via `array_is_list()` check (see `ClientsApi::list`).
2. **`src/Api/{Resource}CreateOptions.php` / `{Resource}UpdateOptions.php`** — typed write models, `final readonly` with every field as a promoted constructor parameter (named arguments) and a `with*()` wither per field (`clone($this, [...])`). `toArray()` snake-cases keys to Billomat's wire format and strips nulls with `array_filter`; in update options `Clear::Field` is sent as `null` to empty a field. Document positions are the `items:` parameter plus `withItem()`.
3. **`src/Model/{Resource}.php`** — `final readonly` read model with `fromArray()` hydration and `toArray()` for debug/log output. Snake_case ↔ camelCase mapping lives in `fromArray`/`toArray`.

When adding a new resource, register it as a public readonly property on `BillomatClient` and instantiate it in the constructor.

### Sub-resources

Items, payments, comments, tags, properties and property values hang off their parent `*Api` as lazy property hooks (`public private(set) CommentsApi $comments { get => $this->comments ??= new CommentsApi(...); }`), not on `BillomatClient`. They use one generic class per kind — `CommentsApi`, `TagsApi`, `ItemsApi`, `PaymentsApi`, `PropertiesApi`, `PropertyValuesApi` — extending `AbstractSubResourceApi`, which derives path, envelope keys and parent filter from the parent slug (`'credit-note'` → `/credit-note-comments`, `credit-note-comment`, `credit_note_id`). The property's `@var` carries the `@template` types; the constructor gets the hydrator, the expected `*CreateOptions` class (checked at runtime, the native type is only `WriteOptions`) and for comments the `*CommentActionKey` enum. Methods are uniform: `list(int $parentId, …)` (payments/property values: `list(?int $parentId = null, array $filters = [])`), `get()`, `create()`, `update()`, `delete(): void`. Sub-resources that don't fit (`ReminderItemsApi`, read-only; `RecurringEmailReceiversApi`) are small classes on the same base.


### Generated code

Some model/options classes are generated by `tools/generate.php` from `docs/spec/billomat.json` plus a per-resource definition in `tools/codegen/definitions/` (currently Clients, Contacts, Suppliers, Articles, Taxes, Templates, Invoices, Offers, Credit Notes, Recurrings, Letters, Reminders, Delivery Notes, Confirmations and Incomings → `Client`, `Contact`, `Supplier`, `Article`, `TaxRate`, `Template`, `Invoice`, `Offer`, `CreditNote`, `Recurring`, `Letter`, `Reminder`, `DeliveryNote`, `Confirmation`, `Incoming` plus their `*CreateOptions`/`*UpdateOptions`; the five `*Property` definitions with the shared `PropertyCreateOptions`; `*Tag`, `*TagCloudEntry` and `*TagCreateOptions` for all twelve tag resources; `*Comment`, `*Payment`, `*PropertyValue` and the document `*Item` classes with their `*CreateOptions`; `RecurringEmailReceiver` with `RecurringEmailReceiverCreateOptions`; the typed list filters `ClientFilter`, `SupplierFilter`, `ArticleFilter`, `UserFilter`, `InvoiceFilter`, `OfferFilter`, `ConfirmationFilter`, `CreditNoteFilter`, `DeliveryNoteFilter`, `LetterFilter`, `ReminderFilter`, `IncomingFilter`, `RecurringFilter` and `TemplateFilter` (definition key `filter`; field types are derived from the spec's filter names and descriptions, enums come from the definition); and the read-only models `Unit`, `FreeText`, `EmailTemplate`, `ReminderText`, `CountryTax`, `Country`, `Currency`, `User` and `IncomingCategory`). Their class docblock says "Generiert aus …". **Never edit these files by hand** — change the definition and run `composer generate`; CI runs `composer generate:check`. The definition holds field order, German docs, read-only response fields and BC overrides; the spec supplies types, defaults and enum values. A spec field missing from the definition aborts generation. `*Api` classes stay handwritten.

### HTTP layer

- `BillomatHttpClient::request()` builds the URL from `BillomatConfig::getBaseUri()` (`https://{billomatId}.billomat.net/api/`), adds auth headers (`X-BillomatApiKey`, optional `X-AppId`/`X-AppSecret`), and serializes the body as JSON (`JSON_PRESERVE_ZERO_FRACTION`). It returns a PSR-7 response with status < 400 and throws SDK exceptions for everything else.
- Retries follow `BillomatConfig::$retry` (`RetryPolicy`): 429 for every method (wait from `Retry-After`/`X-Rate-Limit-Reset`, `RateLimitException` when it exceeds `maxDelayMs`), 502/503/504 and network errors for GET only. Never retry POST/PUT/DELETE otherwise — Billomat's write endpoints aren't idempotent. The request is rebuilt per attempt.
- Query strings are built manually in `buildBillomatQuery()`, **not** via `http_build_query`. Two Billomat quirks the helper preserves:
  - Array values serialize as `key[]=v1&key[]=v2` (the PSR-7 URI percent-encodes the brackets to `%5B%5D`).
  - `+` is left literal (e.g. `order_by=date+DESC`). After `rawurlencode`, `%2B` is converted back to `+` — Billomat rejects `%2B`.

### Error mapping

`BillomatHttpClient` translates 4xx/5xx responses into SDK exceptions, all extending `BillomatException`. The message contains method, path and Billomat's error text (`{"errors":{"error":…}}` or `<error>…</error>`):

| Status | Exception |
|---|---|
| 401, 403 | `AuthenticationException` |
| 404 | `NotFoundException` |
| 400, 422 | `ValidationException` |
| 429 | `RateLimitException` (`getRetryAfter()`) |
| other | `HttpException` |
| no response | `TransportException` (wraps the PSR-18 exception) |

A 2xx response whose body isn't a JSON object, or doesn't match the expected envelope (`unwrapEnvelope()`), throws `UnexpectedResponseException`, never a plain `RuntimeException` or `JsonException`.

`getJsonOrNull()` swallows `NotFoundException` so `get()` can return `null` for missing resources. The raw response body is preserved on the exception for debugging.

### Binary responses

For endpoints that may return binary (e.g. `GET /invoices/{id}/pdf?format=pdf`), use `AbstractApi::getRaw($path, $query)` instead of `getJson()`. Pattern is in `InvoicesApi::pdf()`.

## Conventions

- PHP 8.5+ only. Use `final readonly` for read models, `readonly` properties on `BillomatClient`, named arguments, enums (`src/Model/Enum/`), and constructor property promotion.
- Runtime dependencies are PSR interfaces only (`psr/http-client`, `psr/http-factory`, `psr/http-message`) plus `php-http/discovery`; never add a concrete HTTP client or framework package to `require`. Symfony packages are dev-only, **Symfony 8 only** — currently `^8.1` on every `symfony/*` package; don't add `symfony/http-foundation` just for status-code constants. Raise the lower bound when a security fix requires it or an older minor leaves maintenance. CI exercises the floor (Symfony 8.1 pinned via `extra.symfony.require` + Flex) and the ceiling (latest Symfony 8.x).
- `declare(strict_types=1);` at the top of every file.
- Public `*Api` methods without side effects (`get`, `list*`, `iterateAll`, `pdf`, …) carry `#[NoDiscard]`; write methods don't. In tests, cast intentionally ignored results to `(void)`.
- Methods without a meaningful result (`delete`, `complete`, `cancel`, `email`, …) return `void`, never an always-`true` bool. Errors are exceptions.
- List methods take `XFilter|array $filters`; `AbstractApi::filterQuery()` turns a `ListFilter` into query parameters. Sorting goes through `OrderBy`, never a hand-written `'order_by' => 'date+DESC'` in SDK code.
- All write options implement `WriteOptions` and are immutable: no property assignment, use named arguments or `with*()`. PHP-CS-Fixer is configured to write `clone($this, [...])` without a space.
- `new Foo()->bar` needs no parentheses (PHP 8.4+); don't write `(new Foo())->bar`.
- All API classes are `final`; models are `final readonly`.
- PSR-4: `Justpilot\Billomat\` → `src/`, `Justpilot\Billomat\Tests\` → `tests/`.
- Unit tests use `Symfony\Component\HttpClient\MockHttpClient` + `MockResponse`, wrapped in `Psr18Client` (`new BillomatHttpClient(new Psr18Client($mock), $config)`), to capture the outgoing request and assert URL/method/headers/body (`$options['body']` is the JSON string). Don't mock `BillomatHttpClient` itself. Retry tests pass a `sleep:` closure instead of waiting.
- Test methods use PHPUnit 13 attributes: `#[Test]` on each method (no `test_` prefix), `#[CoversClass]` on unit-test classes, `#[CoversNothing]` on integration tests, `#[DataProvider]` for providers. Assertions are called statically (`self::assertX()`).
- Comments are in German throughout. Match the existing language when editing existing files; new files can follow the same style.
