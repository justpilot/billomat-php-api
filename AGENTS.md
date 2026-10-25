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
composer analyse                # PHPStan level max (no baseline; fix findings instead of ignoring them)
composer refactor               # Apply Rector
composer refactor:dry           # Show Rector suggestions
composer mutate                 # Infection mutation testing (~3 min, all of src/)
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

Branches: `4.x` collects the work for 4.0.0 (BC breaks allowed; feature branches target `4.x`). `main` only gets 3.x fixes until 4.0 is released and is merged into `4.x` regularly. Every BC break on `4.x` needs a CHANGELOG entry and a before/after section in `UPGRADE-4.0.md` (English). If the change is mechanical (rename, moved property, new parameter order), extend the Rector upgrade set as well: `rector/upgrade-4.0.php` plus the rules in `rector/src/` (namespace `Justpilot\Billomat\Rector`, autoloaded for users, shipped in the dist; `rector/rector` is only a dev dependency/suggest) with fixtures under `tests/Rector/`.

CI runs via `.github/workflows/ci.yml` on PHP 8.5 (required) and 8.6 (experimental). Three jobs: tests, quality (lint + PHPStan + Rector + audit), and mutation testing (changed lines on PRs with 90 %, all code on pushes to `main`/`4.x` with `minMsi`/`minCoveredMsi` 95 from `infection.json5`).

### Documentation website

`docs/` is also a VitePress site (`docs/.vitepress/config.mts`, `docs/package.json`, `cd docs && npm ci && npm run build`), deployed by `.github/workflows/docs.yml` to https://billomat-php.justpilot.io (`docs/public/CNAME`). The sidebar is built from the first `# ` heading of each page; `docs/spec/`, `docs/audit.md` and `docs/roadmap-v4.md` are excluded. Relative links to files outside `docs/` are rewritten to GitHub. Dead links fail the build.

## Architecture

### Entry point: `BillomatClient`
`src/BillomatClient.php` is a thin façade exposing each resource as a lazy property hook (`public private(set) ClientsApi $clients { get => $this->clients ??= new ClientsApi($this->http); }`):

```php
$billomat = BillomatClient::create(billomatId: 'mycompany', apiKey: '…');
$billomat->clients->list();
$billomat->invoices->create($options);
$billomat->invoices->pdf($id, PdfType::SIGNED);
$billomat->invoices->comments->list($invoiceId);
```

Construction builds `BillomatHttpClient`; each `*Api` is created on first access with it. The transport is PSR-18: any `Psr\Http\Client\ClientInterface` can be injected; without one, `BillomatClient` uses Symfony's `Psr18Client` (with `BillomatConfig::$timeout`) if `symfony/http-client` is installed, otherwise `php-http/discovery`. PSR-17 factories come from discovery unless passed in.

### Three-layer pattern per resource

Every Billomat resource follows the same triad:

1. **`src/Api/{Resource}Api.php`** — extends `AbstractApi`, exposes verbs (`list`, `get`, `create`, `update`, `delete`, plus resource-specific actions like `complete`, `cancel`, `pdf`). Always wraps/unwraps the Billomat response envelope (`{ "clients": { "client": [...] } }` or `{ "client": {...} }`). When listing, Billomat returns either a single object or a list under the inner key — code must normalize via `array_is_list()` check (see `ClientsApi::list`).
2. **`src/Api/{Resource}CreateOptions.php` / `{Resource}UpdateOptions.php`** — typed write models, `final readonly` with every field as a promoted constructor parameter (named arguments) and a `with*()` wither per field (`clone($this, [...])`). `toArray()` snake-cases keys to Billomat's wire format and strips nulls with `array_filter`; in update options `Clear::Field` is sent as `null` to empty a field. Document positions are the `items:` parameter plus `withItem()`.
3. **`src/Model/{Resource}.php`** — `final readonly` read model with `fromArray()` hydration and `toArray()` for debug/log output. Snake_case ↔ camelCase mapping lives in `fromArray`/`toArray`.

When adding a new resource, add it as a lazy property hook on `BillomatClient` (same pattern as the others); `tests/BillomatClientTest.php` counts them.

### Sub-resources

Items, payments, comments, tags, properties and property values hang off their parent `*Api` as lazy property hooks (`public private(set) CommentsApi $comments { get => $this->comments ??= new CommentsApi(...); }`), not on `BillomatClient`. They use one generic class per kind — `CommentsApi`, `TagsApi`, `ItemsApi`, `PaymentsApi`, `PropertiesApi`, `PropertyValuesApi` — extending `AbstractSubResourceApi`, which derives path, envelope keys and parent filter from the parent slug (`'credit-note'` → `/credit-note-comments`, `credit-note-comment`, `credit_note_id`). The property's `@var` carries the `@template` types; the constructor gets the hydrator, the expected `*CreateOptions` class (checked at runtime, the native type is only `WriteOptions`) and for comments the `*CommentActionKey` enum. Methods are uniform: `list(int $parentId, …)` (payments/property values: `list(?int $parentId = null, array $filters = [])`), `get()`, `create()`, `update()`, `delete(): void`. Sub-resources that don't fit (`ReminderItemsApi`, read-only; `RecurringEmailReceiversApi`) are small classes on the same base.


### Generated code

Some model/options classes are generated by `tools/generate.php` from `docs/spec/billomat.json` plus a per-resource definition in `tools/codegen/definitions/` (currently Clients, Contacts, Suppliers, Articles, Taxes, Templates, Invoices, Offers, Credit Notes, Recurrings, Letters, Reminders, Delivery Notes, Confirmations and Incomings → `Client`, `Contact`, `Supplier`, `Article`, `TaxRate`, `Template`, `Invoice`, `Offer`, `CreditNote`, `Recurring`, `Letter`, `Reminder`, `DeliveryNote`, `Confirmation`, `Incoming` plus their `*CreateOptions`/`*UpdateOptions`; the five `*Property` definitions with the shared `PropertyCreateOptions`; `*Tag`, `*TagCloudEntry` and `*TagCreateOptions` for all twelve tag resources; `*Comment`, `*Payment`, `*PropertyValue` and the document `*Item` classes with their `*CreateOptions`; `RecurringEmailReceiver` with `RecurringEmailReceiverCreateOptions`; the typed list filters `ClientFilter`, `SupplierFilter`, `ArticleFilter`, `UserFilter`, `InvoiceFilter`, `OfferFilter`, `ConfirmationFilter`, `CreditNoteFilter`, `DeliveryNoteFilter`, `LetterFilter`, `ReminderFilter`, `IncomingFilter`, `RecurringFilter` and `TemplateFilter` (definition key `filter`; field types are derived from the spec's filter names and descriptions, enums come from the definition); and the read-only models `Unit`, `FreeText`, `EmailTemplate`, `ReminderText`, `CountryTax`, `Country`, `Currency`, `User` and `IncomingCategory`). Their class docblock says "Generiert aus …". **Never edit these files by hand** — change the definition and run `composer generate`; CI runs `composer generate:check`. The definition holds field order, German docs, read-only response fields and BC overrides; the spec supplies types, defaults and enum values. Every generated model ends with `public array $extra = []`: `fromArray()` puts all keys not in its `FIELDS` constant there, `toArray()` spreads them first. A spec field missing from the definition aborts generation, and so does a spec field with a fixed value set (`enum` in the spec) whose definition has no `'enum' => 'SomeEnum'` (or `'enum' => false` to keep it a string on purpose). Comma-separated value lists (payment types) use `'enumList' => true` and become `list<Enum>`. `*Api` classes stay handwritten.

### HTTP layer

- `BillomatHttpClient::request()` builds the URL from `BillomatConfig::getBaseUri()` (`https://{billomatId}.billomat.net/api/`), adds auth headers (`X-BillomatApiKey`, optional `X-AppId`/`X-AppSecret`) and `Accept-Language` from `BillomatConfig::$language`, and serializes the body as JSON (`JSON_PRESERVE_ZERO_FRACTION`). It returns a PSR-7 response with status < 400 and throws SDK exceptions for everything else.
- Optional PSR-3 `logger` and PSR-14 `events` (constructor of `BillomatClient`/`BillomatHttpClient`): every attempt dispatches `RequestStarted`/`ResponseReceived`, retries `RetryScheduled`, final errors `RequestFailed` (`src/Event/`). Log context is method, path, attempt, status, duration, never headers, query or body (API key, personal data). Test helpers: `tests/Support/RecordingEventDispatcher`, `RecordingLogger`.
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
| 429 | `RateLimitException` |
| other | `HttpException` |
| no response | `TransportException` (wraps the PSR-18 exception) |

`HttpException::getRetryAfter()` carries the announced wait for 429 (`Retry-After`/`X-Rate-Limit-Reset`) and 502/503/504 (`Retry-After` only). `BillomatException::isTransient()` (429/502/503/504, network errors) and `mayHaveBeenProcessed()` (`false` for 4xx, `true` for 5xx and network errors; `TransportException` wrapping a PSR-18 `RequestExceptionInterface` is neither) classify every error — keep them in sync when adding exceptions.

A 2xx response whose body isn't a JSON object, or doesn't match the expected envelope (`unwrapEnvelope()`), throws `UnexpectedResponseException`, never a plain `RuntimeException` or `JsonException`.

`getJsonOrNull()` swallows `NotFoundException` so `get()` can return `null` for missing resources. The raw response body is preserved on the exception for debugging.

### Binary responses

For endpoints that may return binary (e.g. `GET /invoices/{id}/pdf?format=pdf`), use `AbstractApi::getRaw($path, $query)` instead of `getJson()`; for PDFs use `getPdf()`, which throws `UnexpectedResponseException` on an empty body or a missing `%PDF-` header. Pattern is in `InvoicesApi::pdf()`.

### Webhooks

Billomat has no REST endpoint for webhook subscriptions (UI only) — never add a `WebhooksApi`. `src/Webhook/` only parses incoming requests: `WebhookEvent` (83 cases from the catalog in `docs/concepts/webhooks.md`; `modelClass()` maps the resource to its read model), `WebhookHeaders`, `WebhookPayload` (unwraps the REST-style envelope, key = resource with `-`) and `InvalidWebhookException`. Payloads are JSON with string values and often stale; the docs tell users to reload via `get()`. Fixtures in `tests/Fixtures/Webhook/<event>.json` are anonymized real bodies.

## Conventions

- PHP 8.5+ only. Use `final readonly` for read models, `readonly` properties on `BillomatClient`, named arguments, enums (`src/Model/Enum/`), and constructor property promotion.
- Runtime dependencies are PSR interfaces only (`psr/http-client`, `psr/http-factory`, `psr/http-message`, `psr/log`, `psr/event-dispatcher`) plus `php-http/discovery`; never add a concrete HTTP client or framework package to `require`. Symfony packages are dev-only, **Symfony 8 only** — currently `^8.1` on every `symfony/*` package; don't add `symfony/http-foundation` just for status-code constants. Raise the lower bound when a security fix requires it or an older minor leaves maintenance. CI exercises the floor (Symfony 8.1 pinned via `extra.symfony.require` + Flex) and the ceiling (latest Symfony 8.x).
- `declare(strict_types=1);` at the top of every file.
- Public `*Api` methods without side effects (`get`, `getOrFail`, `list*`, `iterateAll`, `pdf`, …) carry `#[NoDiscard]`; write methods don't. Every `get()` returning `?Model` has a `getOrFail()` next to it (`$this->get($id) ?? throw $this->notFound($path)`); `tests/Api/GetOrFailTest.php` checks all of them. In tests, cast intentionally ignored results to `(void)`.
- Methods without a meaningful result (`delete`, `complete`, `cancel`, `email`, …) return `void`, never an always-`true` bool. Errors are exceptions.
- List methods take `XFilter|array $filters`; `AbstractApi::filterQuery()` turns a `ListFilter` into query parameters. Sorting goes through `OrderBy`, never a hand-written `'order_by' => 'date+DESC'` in SDK code.
- Amounts are `Model\Money` (exact decimal on `BcMath\Number`, `ext-bcmath`), never `float`. The generator maps `FLOAT` fields listed in `MONEY_FIELDS` (`tools/generate.php`) to `money`; add new amount fields there. Quantities, tax/discount rates and `quote` stay `float`. Document tax rows are `Model\TaxSummary` (`taxes` field type in the generator). Payloads and `toArray()` carry amounts as strings (`Money::toString()`). Fields with a fixed value set are enums from `src/Model/Enum/`; every enum uses the `FromApi` trait (`fromApi(?string): ?self`, `null`/`""`/unknown → `null`); generated models hydrate with native `tryFrom()` (`model.fromApi` switches to `fromApi()` for enums with their own tolerance), and enum fields are never `nonNull` (no silent fallback case); keep `string` only for open sets (salutation, locale, currency code).
- All write options implement `WriteOptions` and are immutable: no property assignment, use named arguments or `with*()`. PHP-CS-Fixer is configured to write `clone($this, [...])` without a space.
- `new Foo()->bar` needs no parentheses (PHP 8.4+); don't write `(new Foo())->bar`.
- All API classes are `final`; models are `final readonly`.
- PSR-4: `Justpilot\Billomat\` → `src/`, `Justpilot\Billomat\Tests\` → `tests/`.
- Unit tests use `Symfony\Component\HttpClient\MockHttpClient` + `MockResponse`, wrapped in `Psr18Client` (`new BillomatHttpClient(new Psr18Client($mock), $config)`), to capture the outgoing request and assert URL/method/headers/body. Capture it with `Tests\Support\CapturedRequest` (`record()` in the callback; `->method`, `->url`, `->query()`, `->headers()`, `->body()`, `->jsonAt(…)`, `->jsonArray(…)`), never with an untyped `$captured` array; `Tests\Support\Payload::at()`/`arrayAt()` read nested `toArray()` results. PHPStan runs level max with bleeding edge, no baseline and no `ignoreErrors` — tests included. Don't mock `BillomatHttpClient` itself. Retry tests pass a `sleep:` closure instead of waiting.
- Generated code needs no per-class tests for its field mapping: `tests/Model/ModelRoundTripTest.php` and `tests/Api/Options/GeneratedOptionsTest.php` cover every model with `fromArray()`/`toArray()` and every generated `*Options` class via reflection (wire names from the `Billomat-Feld:` docblock line). New field types need a sample value there. Infection runs with strict coverage: a test touching a class outside its `CoversClass`/`UsesClass` is risky and aborts the run, so add the attribute (or `UsesNamespace`).
- Test methods use PHPUnit 13 attributes: `#[Test]` on each method (no `test_` prefix), `#[CoversClass]` on unit-test classes, `#[CoversNothing]` on integration tests, `#[DataProvider]` for providers. Assertions are called statically (`self::assertX()`).
- Comments are in German throughout. Match the existing language when editing existing files; new files can follow the same style.
