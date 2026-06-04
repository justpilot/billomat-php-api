# Billomat PHP API SDK

[![PHP Version](https://img.shields.io/badge/PHP-8.4%2B-8892BF.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-Apache_2.0-blue.svg)](LICENSE)
[![Packagist](https://img.shields.io/badge/packagist-justpilot%2Fbillomat--php--api-orange.svg)](https://packagist.org/packages/justpilot/billomat-php-api)
[![PHPStan](https://img.shields.io/badge/PHPStan-level_max-brightgreen.svg)](https://phpstan.org/)
[![codecov](https://codecov.io/gh/justpilot/billomat-php-api/branch/main/graph/badge.svg)](https://codecov.io/gh/justpilot/billomat-php-api)

**Language / Sprache:** English (this file) · [Deutsch](README.md)

Modern, fully typed PHP 8.4+ SDK for the [Billomat API](https://www.billomat.com/api/) — built on Symfony components, with `final readonly` read models, separate typed write options, and a clean exception hierarchy.

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

- PHP 8.4 or newer
- Composer
- Symfony components: current LTS (`^7.4`) or latest stable (`^8.0`). Older Symfony versions are not supported.
- A Billomat account with API access enabled
- An API key from the account settings under `https://<billomatId>.billomat.net/app/settings/api`

`billomatId` is the subdomain part of your Billomat URL. For `https://mycompany.billomat.net/` the ID is `mycompany`.

## Installation

```bash
composer require justpilot/billomat-php-api
```

## Quickstart

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\InvoiceCreateOptions;
use Justpilot\Billomat\Api\InvoiceItemCreateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\Enum\InvoicePdfType;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

// 1) Create a draft invoice
$item = new InvoiceItemCreateOptions(quantity: 8.0, unitPrice: 95.00);
$item->title = 'Conception';

$options = new InvoiceCreateOptions(clientId: 12345);
$options->title = 'Web development May 2026';
$options->addItem($item);

$invoice = $billomat->invoices->create($options);

// 2) Complete the invoice (assigns a number and generates the PDF)
$billomat->invoices->complete($invoice->id);

// 3) Download the PDF
$pdf = $billomat->invoices->pdf($invoice->id, InvoicePdfType::SIGNED, rawPdf: true);
file_put_contents(sprintf('invoice-%d.pdf', $invoice->id), $pdf);
```

Full runnable examples live under [examples/](examples/).

## Features

- PHP 8.4+, fully typed (`final readonly` models, enums, named arguments).
- HTTP layer on top of `symfony/http-client`; bring your own `HttpClientInterface`.
- One `*Api` class per Billomat resource with consistent verbs (`list`, `get`, `create`, `update`, `delete`) plus resource-specific actions (`complete`, `cancel`, `pdf`, `thumb`, …).
- Auto-pagination via an `iterateAll()` generator and `listPage()` with metadata.
- Structured exception hierarchy with mapping to HTTP status codes (401/403, 404, 400/422).

## Resources

Every resource is reachable as a `public readonly` property on `BillomatClient`. The table groups resources by topic — one row per documentation file.

### Master data

| Resource | Access | Docs |
|---|---|---|
| Clients | `$billomat->clients` | [docs/resources/clients.md](docs/resources/clients.md) |
| Client tags | `$billomat->clientTags` | [docs/resources/client-tags.md](docs/resources/client-tags.md) |
| Contacts | `$billomat->contacts` | [docs/resources/contacts.md](docs/resources/contacts.md) |
| Suppliers | `$billomat->suppliers`, `supplierTags`, `supplierPropertyValues` | [docs/resources/suppliers.md](docs/resources/suppliers.md) |
| Articles | `$billomat->articles`, `articleTags`, `articlePropertyValues` | [docs/resources/articles.md](docs/resources/articles.md) |
| Property definitions | `$billomat->articleProperties`, `clientProperties`, `supplierProperties`, `incomingProperties`, `userProperties` | [docs/resources/properties.md](docs/resources/properties.md) |

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
| Lookups (Countries, Currencies, Units, DunningLevels, Users, EmailTemplates, FreeTexts, ReminderTexts) | `$billomat->countries`, `currencies`, `units`, `dunningLevels`, `users`, `emailTemplates`, `freeTexts`, `reminderTexts` | [docs/resources/lookups.md](docs/resources/lookups.md) |

### Concepts

Deeper topics live under [docs/concepts/](docs/concepts/): [authentication](docs/concepts/authentication.md) (`X-AppId`/`X-AppSecret`), [API security](docs/concepts/api-security.md), [pagination & filtering](docs/concepts/pagination-and-filtering.md), [custom meta attributes](docs/concepts/custom-meta-attributes.md), [errors & rate limits](docs/concepts/errors-and-rate-limits.md), and [webhooks](docs/concepts/webhooks.md) (UI-only — Billomat has no REST endpoint for webhook subscriptions, so the SDK does not expose one either).

## Configuration

`BillomatClient::create()` is the convenient entry point:

```php
$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: '…',
    appId: null,        // optional, only for registered third-party apps
    appSecret: null,    // optional
    timeout: 10.0,      // seconds
    httpClient: null,   // optional custom HttpClientInterface
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

In addition to `list()`, every list endpoint exposes two helpers:

```php
// Auto-pagination — a lazy generator that walks every page
foreach ($billomat->clients->iterateAll(['country_code' => 'DE']) as $client) {
    // …
}

// Single page with metadata
$result = $billomat->clients->listPage(['per_page' => 50, 'page' => 3]);
echo "Page {$result->info->page} of " . ($result->info->totalPages() ?? '?');
```

Details and examples: [docs/advanced/pagination.md](docs/advanced/pagination.md).

## Error handling

Every exception thrown by the SDK extends `BillomatException`. HTTP errors are mapped to specialized subclasses:

| Status code | Exception |
|---|---|
| 401, 403 | `AuthenticationException` |
| 404 | `NotFoundException` |
| 400, 422 | `ValidationException` |
| other 4xx/5xx | `HttpException` |

`get($id)` methods return `null` on 404 instead of throwing. Every `HttpException` exposes `getStatusCode()` and `getResponseBody()` — the latter contains Billomat's raw response body and is often useful when debugging.

Patterns and examples: [docs/error-handling.md](docs/error-handling.md). Background on rate limits: [docs/concepts/errors-and-rate-limits.md](docs/concepts/errors-and-rate-limits.md).

## Logging & HTTP client

The Symfony HTTP client can be decorated before being handed to `BillomatClient` — the standard Symfony toolbox covers logging, tracing, retry, and tests:

```php
use Justpilot\Billomat\BillomatClient;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\RetryableHttpClient;
use Symfony\Component\HttpClient\TraceableHttpClient;

$inner = HttpClient::create(['max_duration' => 30.0]);
$decorated = new TraceableHttpClient(new RetryableHttpClient($inner));

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
    httpClient: $decorated,
);
```

For unit tests, `Symfony\Component\HttpClient\MockHttpClient` is a drop-in replacement. HTTP layer specifics (query encoding, `+` vs. `%2B`, array filters) live in [docs/advanced/http-layer.md](docs/advanced/http-layer.md).

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

Version history and release notes follow [Keep a Changelog](https://keepachangelog.com/) in [CHANGELOG.md](CHANGELOG.md). The project follows [Semantic Versioning](https://semver.org/).

## License

Apache License 2.0 — see [LICENSE](LICENSE).
