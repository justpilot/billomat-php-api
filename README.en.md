# Billomat PHP API SDK

[![PHP Version](https://img.shields.io/badge/PHP-8.5%2B-8892BF.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-Apache_2.0-blue.svg)](LICENSE)
[![Packagist](https://img.shields.io/badge/packagist-justpilot%2Fbillomat--php--api-orange.svg)](https://packagist.org/packages/justpilot/billomat-php-api)
[![PHPStan](https://img.shields.io/badge/PHPStan-level_max-brightgreen.svg)](https://phpstan.org/)
[![codecov](https://codecov.io/gh/justpilot/billomat-php-api/branch/main/graph/badge.svg)](https://codecov.io/gh/justpilot/billomat-php-api)

**Language / Sprache:** English (this file) · [Deutsch](README.md)

Modern, fully typed PHP 8.5+ SDK for the [Billomat API](https://www.billomat.com/api/) — works with any PSR-18 HTTP client (Symfony, Guzzle, …), with `final readonly` read models, separate typed write options, and a clean exception hierarchy.

> Unofficial SDK. This project is not affiliated with Billomat GmbH and is developed and maintained independently.

## Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Quickstart](#quickstart)
- [Features](#features)
- [Resources](#resources)
- [Configuration](#configuration)
- [Pagination](#pagination)
- [Error handling](#error-handling)
- [Logging & HTTP client](#logging--http-client)
- [Tests](#tests)
- [Examples](#examples)
- [Contributing](#contributing)
- [Security](#security)
- [Changelog](#changelog)
- [License](#license)

## Requirements

- PHP 8.5 or newer
- Composer
- A PSR-18 HTTP client plus PSR-17 factories, e.g. `symfony/http-client` with `nyholm/psr7`, or `guzzlehttp/guzzle`
- A Billomat account with API access enabled
- An API key from the account settings under `https://<billomatId>.billomat.net/app/settings/api`

`billomatId` is the subdomain part of your Billomat URL. For `https://mycompany.billomat.net/` the ID is `mycompany`.

## Installation

```bash
composer require justpilot/billomat-php-api
```

The SDK does not ship its own HTTP client; it needs a PSR-18 client and PSR-17 factories. If the `php-http/discovery` Composer plugin is allowed, it installs an implementation automatically when none is present. Otherwise, install one explicitly:

```bash
composer require justpilot/billomat-php-api symfony/http-client nyholm/psr7
# or
composer require justpilot/billomat-php-api guzzlehttp/guzzle
```

## Quickstart

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\InvoiceCreateOptions;
use Justpilot\Billomat\Api\InvoiceItemCreateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\Enum\PdfType;
use Justpilot\Billomat\Model\Money;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

// 1) Create a draft invoice
$item = new InvoiceItemCreateOptions(quantity: 8.0, unitPrice: Money::of('95.00'), title: 'Conception');

$options = new InvoiceCreateOptions(clientId: 12345, title: 'Web development May 2026', items: [$item]);

$invoice = $billomat->invoices->create($options);

// 2) Complete the invoice (assigns a number and generates the PDF)
$billomat->invoices->complete($invoice->id);

// 3) Download the PDF
$pdf = $billomat->invoices->pdf($invoice->id, PdfType::SIGNED, rawPdf: true);
file_put_contents(sprintf('invoice-%d.pdf', $invoice->id), $pdf);
```

Full runnable examples live under [examples/](examples/).

## Features

- PHP 8.5+, fully typed (`final readonly` models, enums, named arguments).
- HTTP layer on top of PSR-18/PSR-17: bring any client (Symfony, Guzzle, …), or let `php-http/discovery` find one.
- Automatic retries: 429 after the wait Billomat announces (`Retry-After` or `X-Rate-Limit-Reset`), plus GET requests on 502/503/504 and network errors with exponential backoff — configurable via `RetryPolicy`.
- One `*Api` class per Billomat resource with consistent verbs (`list`, `get`, `create`, `update`, `delete`) plus resource-specific actions (`complete`, `cancel`, `pdf`, `thumb`, …).
- Auto-pagination via an `iterateAll()` generator and `listPage()` with metadata.
- Typed list filters (`InvoiceFilter`, `ClientFilter`, …) with enums, `DateTimeImmutable` and `OrderBy`; plain arrays still work.
- Monetary amounts as an exact `Money` value object (BcMath, decimal strings just like Billomat) instead of `float` — no rounding errors, with comparison, sums and locale-style formatting; see [docs/concepts/money.md](docs/concepts/money.md) (German).
- Receiving webhooks: `WebhookHeaders`, `WebhookPayload` and `WebhookEvent` (all 83 events) parse incoming requests and hydrate the matching model ([docs/concepts/webhooks.md](docs/concepts/webhooks.md), German). Webhooks themselves are set up in the Billomat UI only.
- Structured exception hierarchy with mapping to HTTP status codes (401/403, 404, 400/422, 429) and a dedicated `TransportException` for network failures.

## Resources

Every resource is reachable as a `public readonly` property on `BillomatClient`. The table groups resources by topic — one row per documentation file.

### Master data

| Resource | Access | Docs |
|---|---|---|
| Clients | `$billomat->clients`, `clientPropertyValues` | [docs/resources/clients.md](docs/resources/clients.md) |
| Client tags | `$billomat->clients->tags` | [docs/resources/client-tags.md](docs/resources/client-tags.md) |
| Contacts | `$billomat->contacts` | [docs/resources/contacts.md](docs/resources/contacts.md) |
| Suppliers | `$billomat->suppliers`, `supplierTags`, `supplierPropertyValues` | [docs/resources/suppliers.md](docs/resources/suppliers.md) |
| Articles | `$billomat->articles`, `articleTags`, `articlePropertyValues` | [docs/resources/articles.md](docs/resources/articles.md) |
| Property definitions | `$billomat->articles->properties`, `clientProperties`, `supplierProperties`, `incomingProperties`, `userProperties` | [docs/resources/properties.md](docs/resources/properties.md) |

### Outgoing documents

| Resource | Access | Docs |
|---|---|---|
| Invoices | `$billomat->invoices`, `invoiceItems`, `invoicePayments`, `invoiceComments`, `invoiceTags` | [docs/resources/invoices.md](docs/resources/invoices.md), [items](docs/resources/invoice-items.md), [payments](docs/resources/invoice-payments.md), [comments](docs/resources/invoice-comments.md), [tags](docs/resources/invoice-tags.md) |
| Recurring invoices | `$billomat->recurrings`, `recurringItems`, `recurringTags`, `recurringEmailReceivers` | [docs/resources/recurrings.md](docs/resources/recurrings.md) |
| Offers | `$billomat->offers`, `offerItems`, `offerComments`, `offerTags` | [docs/resources/offers.md](docs/resources/offers.md) |
| Confirmations | `$billomat->confirmations`, `confirmationItems`, `confirmationComments`, `confirmationTags` | [docs/resources/confirmations.md](docs/resources/confirmations.md) |
| Delivery notes | `$billomat->deliveryNotes`, `deliveryNoteItems`, `deliveryNoteComments`, `deliveryNoteTags` | [docs/resources/delivery-notes.md](docs/resources/delivery-notes.md) |
| Credit notes | `$billomat->creditNotes`, `creditNoteItems`, `creditNoteComments`, `creditNoteTags`, `creditNotePayments` | [docs/resources/credit-notes.md](docs/resources/credit-notes.md) |
| Reminders | `$billomat->reminders`, `reminderItems`, `reminderTags` | [docs/resources/reminders.md](docs/resources/reminders.md) |
| Letters | `$billomat->letters`, `letterComments`, `letterTags` | [docs/resources/letters.md](docs/resources/letters.md) |

### Incoming documents

| Resource | Access | Docs |
|---|---|---|
| Incoming invoices | `$billomat->incomings`, `incomingComments`, `incomingPayments`, `incomingTags`, `incomingPropertyValues` | [docs/resources/incomings.md](docs/resources/incomings.md) |
| Incoming categories | `$billomat->incomingCategories` | [docs/resources/incoming-categories.md](docs/resources/incoming-categories.md) |
| Inbox documents | `$billomat->inboxDocuments` | [docs/resources/inbox-documents.md](docs/resources/inbox-documents.md) |

### Account & helper resources

| Resource | Access | Docs |
|---|---|---|
| Account info | `$billomat->account` | [docs/resources/account.md](docs/resources/account.md) |
| Activity feed | `$billomat->activities` | [docs/resources/activities.md](docs/resources/activities.md) |
| Search | `$billomat->search` | [docs/resources/search.md](docs/resources/search.md) |
| Settings | `$billomat->settings` | [docs/resources/settings.md](docs/resources/settings.md) |
| Taxes | `$billomat->taxes` | [docs/resources/taxes.md](docs/resources/taxes.md) |
| Tax-free countries | `$billomat->countryTaxes` | [docs/resources/settings-tax-free-countries.md](docs/resources/settings-tax-free-countries.md) |
| Roles | `$billomat->roles` | [docs/resources/settings-roles.md](docs/resources/settings-roles.md) |
| Templates | `$billomat->templates` | [docs/resources/templates.md](docs/resources/templates.md) |
| Lookups (Countries, Currencies, Units, Users, EmailTemplates, FreeTexts, ReminderTexts) | `$billomat->countries`, `currencies`, `units`, `users`, `emailTemplates`, `freeTexts`, `reminderTexts` | [docs/resources/lookups.md](docs/resources/lookups.md) |

### Concepts

Deeper topics live under [docs/concepts/](docs/concepts/): [authentication](docs/concepts/authentication.md) (`X-AppId`/`X-AppSecret`), [API security](docs/concepts/api-security.md), [pagination & filtering](docs/concepts/pagination-and-filtering.md), [custom meta attributes](docs/concepts/custom-meta-attributes.md), [errors & rate limits](docs/concepts/errors-and-rate-limits.md), [read models](docs/concepts/models.md) (incl. `extra` for unknown fields), [money amounts](docs/concepts/money.md) (`Money`), and [webhooks](docs/concepts/webhooks.md) (subscriptions only in the Billomat UI; the SDK helps parse incoming webhooks).

## Configuration

`BillomatClient::create()` is the convenient entry point:

```php
$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: '…',
    appId: null,        // optional, only for registered third-party apps
    appSecret: null,    // optional
    timeout: 10.0,      // seconds (only for the client the SDK creates)
    httpClient: null,   // optional custom PSR-18 client
    retry: new RetryPolicy(maxRetries: 2), // RetryPolicy::none() disables retries
);
```

For full control (e.g. custom `baseUri`), use `BillomatConfig` directly:

```php
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Config\BillomatConfig;

$config = new BillomatConfig(
    billomatId: 'mycompany',
    apiKey: '…',
    timeout: 15.0,
);

$billomat = new BillomatClient($config);
```

Full options and HTTP client injection: [docs/configuration.md](docs/configuration.md).

## Pagination

In addition to `list()`, every list endpoint exposes two helpers. For the main resources (clients, suppliers, articles, users, templates and all document types) the recommended way to filter is the typed filter class (`ClientFilter`, `InvoiceFilter`, …); a plain array of Billomat query parameters still works.

```php
use Justpilot\Billomat\Api\ClientFilter;
use Justpilot\Billomat\Api\InvoiceFilter;
use Justpilot\Billomat\Api\OrderBy;
use Justpilot\Billomat\Model\Enum\InvoiceStatus;

// Auto-pagination — a lazy generator that walks every page
foreach ($billomat->clients->iterateAll(new ClientFilter(countryCode: 'DE')) as $client) {
    // …
}

// Single page with metadata
$result = $billomat->clients->listPage(new ClientFilter(page: 3, perPage: 50));
echo "Page {$result->info->page} of " . ($result->info->totalPages() ?? '?');

// Multiple values, dates and sorting, all typed
$open = $billomat->invoices->list(new InvoiceFilter(
    status: [InvoiceStatus::OPEN, InvoiceStatus::OVERDUE],
    from: new DateTimeImmutable('2026-01-01'),
    orderBy: OrderBy::desc('date'),
));
```

Details and examples: [docs/advanced/pagination.md](docs/advanced/pagination.md).

## Error handling

Every exception thrown by the SDK extends `BillomatException`. HTTP errors are mapped to specialized subclasses:

| Status code | Exception |
|---|---|
| 401, 403 | `AuthenticationException` |
| 404 | `NotFoundException` |
| 400, 422 | `ValidationException` |
| 429 | `RateLimitException` |
| other 4xx/5xx | `HttpException` |
| no response (DNS, timeout, connection reset) | `TransportException` |

`get($id)` methods return `null` on 404 instead of throwing. `$e->isTransient()` tells whether a later attempt may succeed (429, 502–504, network errors), `$e->mayHaveBeenProcessed()` whether Billomat may have processed a failed write request anyway. Every `HttpException` exposes `getStatusCode()`, `getRetryAfter()` and `getResponseBody()` — the latter contains Billomat's raw response body and is often useful when debugging.

Patterns and examples: [docs/error-handling.md](docs/error-handling.md). Background on rate limits: [docs/concepts/errors-and-rate-limits.md](docs/concepts/errors-and-rate-limits.md).

## Logging & HTTP client

With `logger:` (PSR-3) the SDK logs responses (`debug`), retries (`warning`) and failures (`notice`/`error`), without headers, query or body. With `events:` (PSR-14) you get `RequestStarted`, `ResponseReceived`, `RetryScheduled` and `RequestFailed` from `Justpilot\Billomat\Event`, e.g. for metrics or tracing. Details: [docs/configuration.md](docs/configuration.md#beispiel-request-response-logging).

`httpClient` accepts any PSR-18 client (`Psr\Http\Client\ClientInterface`). Without one, the SDK uses Symfony's `Psr18Client` with the configured timeout if `symfony/http-client` is installed, otherwise the client found by `php-http/discovery`. A custom client configures its own timeout. Wrap a Symfony `HttpClientInterface` — for example one decorated with logging or tracing — in `Psr18Client`:

```php
use Justpilot\Billomat\BillomatClient;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\TraceableHttpClient;

$inner = HttpClient::create(['timeout' => 10.0, 'max_duration' => 30.0]);
$decorated = new TraceableHttpClient($inner);

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
    httpClient: new Psr18Client($decorated),
);
```

The SDK retries 429 and transient errors itself (`RetryPolicy`); an extra `RetryableHttpClient` is not needed.

For unit tests, use `Symfony\Component\HttpClient\MockHttpClient` wrapped in `Psr18Client`. HTTP layer specifics (query encoding, `+` vs. `%2B`, array filters) live in [docs/advanced/http-layer.md](docs/advanced/http-layer.md).

## Tests

```bash
composer install
composer test            # unit tests (fast, offline)
composer test:all        # incl. integration against the sandbox (with .env.test.local)
composer ci              # full quality pipeline: lint + PHPStan + Rector + tests
```

The unit suite uses `MockHttpClient` and needs no credentials. Integration tests under `tests/Integration/` are skipped unless `BILLOMAT_ID` and `BILLOMAT_API_KEY` are set. More in [docs/testing.md](docs/testing.md).

## Examples

Runnable, commented scripts live under [examples/](examples/) — from creating a client to downloading a PDF. Each file pulls credentials from environment variables so it runs without code changes.

Moving from `phobetor/billomat` or `vrok/billomat-client`? See [docs/migration-from-phobetor.md](docs/migration-from-phobetor.md).

## Contributing

Pull requests are welcome. The contribution guide — coding standards, test expectations, the three-layer pattern per resource — lives in [CONTRIBUTING.md](CONTRIBUTING.md). AI coding agents find project-specific context in [AGENTS.md](AGENTS.md) following the [agents.md](https://agents.md/) convention.

## Security

Notes on handling API keys and responsibly disclosing vulnerabilities live in [SECURITY.md](SECURITY.md).

## Changelog

Version history and release notes follow [Keep a Changelog](https://keepachangelog.com/) in [CHANGELOG.md](CHANGELOG.md). The project follows [Semantic Versioning](https://semver.org/). Upgrading from 3.x to 4.0: [UPGRADE-4.0.md](UPGRADE-4.0.md).

## License

Apache License 2.0 — see [LICENSE](LICENSE).
