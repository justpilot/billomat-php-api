# Billomat PHP SDK

[![CI](https://github.com/justpilot/billomat-php-api/actions/workflows/ci.yml/badge.svg?branch=4.x)](https://github.com/justpilot/billomat-php-api/actions/workflows/ci.yml)
[![Packagist Version](https://img.shields.io/packagist/v/justpilot/billomat-php-api?include_prereleases)](https://packagist.org/packages/justpilot/billomat-php-api)
[![Downloads](https://img.shields.io/packagist/dt/justpilot/billomat-php-api)](https://packagist.org/packages/justpilot/billomat-php-api)
[![PHP](https://img.shields.io/packagist/dependency-v/justpilot/billomat-php-api/php)](https://www.php.net/)
[![PHPStan](https://img.shields.io/badge/PHPStan-level_max-brightgreen.svg)](https://phpstan.org/)
[![Mutation testing](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fjustpilot%2Fbillomat-php-api%2F4.x)](https://dashboard.stryker-mutator.io/reports/github.com/justpilot/billomat-php-api/4.x)
[![codecov](https://codecov.io/gh/justpilot/billomat-php-api/branch/4.x/graph/badge.svg)](https://codecov.io/gh/justpilot/billomat-php-api)
[![License](https://img.shields.io/badge/license-Apache_2.0-blue.svg)](LICENSE)

A modern, fully typed PHP 8.5 SDK for the [Billomat API](https://www.billomat.com/api/): invoices, offers, clients, payments, PDFs and incoming webhooks, on top of any PSR-18 HTTP client.

> **Unofficial.** This project is not affiliated with Billomat GmbH.
>
> **Deutsch:** Die Dokumentation ist auf Englisch; Code-Kommentare und Docblocks im SDK sind deutsch. Fragen gern auch auf Deutsch in den [Issues](https://github.com/justpilot/billomat-php-api/issues).

## Installation

```bash
composer require justpilot/billomat-php-api
```

The SDK needs a PSR-18 client and PSR-17 factories. `php-http/discovery` installs one if none is present; otherwise pick one:

```bash
composer require symfony/http-client nyholm/psr7   # or: composer require guzzlehttp/guzzle
```

Requirements: PHP 8.5+, `ext-bcmath`, a Billomat account with API access and an API key from `https://<billomatId>.billomat.net/app/settings/api`.

## Quickstart

```php
use Justpilot\Billomat\Api\InvoiceCreateOptions;
use Justpilot\Billomat\Api\InvoiceItemCreateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\Enum\PdfType;
use Justpilot\Billomat\Model\Money;

$billomat = BillomatClient::create(billomatId: 'mycompany', apiKey: getenv('BILLOMAT_API_KEY'));

$invoice = $billomat->invoices->create(new InvoiceCreateOptions(
    clientId: 12345,
    title: 'Web development May 2026',
    items: [new InvoiceItemCreateOptions(quantity: 8.0, unitPrice: Money::of('95.00'), title: 'Concept')],
));

$billomat->invoices->complete($invoice->id);                             // assigns the number
$pdf = $billomat->invoices->pdf($invoice->id, PdfType::SIGNED, rawPdf: true);
file_put_contents("invoice-{$invoice->id}.pdf", $pdf);

echo $billomat->invoices->getOrFail($invoice->id)->totalGross?->format(2, ',', '.'); // "904,40" with 19 % VAT
```

More runnable scripts in [examples/](examples/).

## Features

- **Typed end to end:** `final readonly` models, enums for every fixed value set, exact `Money` amounts instead of `float`, `DateTimeImmutable` dates. Fields the SDK doesn't know yet are kept in `$extra`.
- **Immutable write options** with named arguments and `with*()` withers; `Clear::Field` empties a field on update.
- **Typed list filters** (`InvoiceFilter`, `ClientFilter`, …) with `OrderBy`, plus `iterateAll()` auto-pagination and `listPage()` metadata.
- **Nested sub-resources:** `$billomat->invoices->items`, `->payments`, `->comments`, `->tags`, …
- **Any PSR-18 client** (Symfony, Guzzle, …); runtime dependencies are PSR interfaces only.
- **Resilient:** automatic retries for 429 (honouring `Retry-After`) and, for GET, 502/503/504 and network errors; errors are classified with `isTransient()` and `mayHaveBeenProcessed()`.
- **Observable:** optional PSR-3 logging (never headers or bodies) and PSR-14 events per request, retry and failure.
- **Webhooks:** `WebhookEvent` (all 83 events), `WebhookHeaders` and `WebhookPayload` parse incoming requests into the matching model.
- **Quality:** PHPStan level max, ~3,000 unit tests, mutation testing (Infection) and integration tests against the Billomat sandbox.

## Documentation

| Topic | |
|---|---|
| Configuration, HTTP client, logging and events | [docs/configuration.md](docs/configuration.md) |
| Errors, retries and rate limits | [docs/error-handling.md](docs/error-handling.md), [docs/concepts/errors-and-rate-limits.md](docs/concepts/errors-and-rate-limits.md) |
| Pagination and filters | [docs/advanced/pagination.md](docs/advanced/pagination.md) |
| Read models, money, webhooks | [docs/concepts/models.md](docs/concepts/models.md), [money.md](docs/concepts/money.md), [webhooks.md](docs/concepts/webhooks.md) |
| Resources (invoices, offers, clients, …) | [docs/resources/](docs/resources/README.md) |
| Testing your integration | [docs/testing.md](docs/testing.md) |
| Upgrading from 3.x | [UPGRADE-4.0.md](UPGRADE-4.0.md) |
| Coming from `phobetor/billomat` | [docs/migration-from-phobetor.md](docs/migration-from-phobetor.md) |

The full documentation is also published at [billomat-php.justpilot.io](https://billomat-php.justpilot.io).

## Supported versions

| SDK | PHP | HTTP client | Status |
|---|---|---|---|
| 4.x | 8.5+ | any PSR-18 client | in development (pre-releases) |
| 3.x | 8.4+ | Symfony HttpClient 8.1+ | security fixes until 4.0.0 |

## Contributing

Pull requests are welcome; please read [CONTRIBUTING.md](CONTRIBUTING.md) first. AI coding agents find the project conventions in [AGENTS.md](AGENTS.md). Security issues: see [SECURITY.md](SECURITY.md). Changes are listed in [CHANGELOG.md](CHANGELOG.md) ([Keep a Changelog](https://keepachangelog.com/), [Semantic Versioning](https://semver.org/)).

## License

Apache License 2.0, see [LICENSE](LICENSE).
