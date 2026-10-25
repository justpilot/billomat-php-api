<!-- Source: https://www.billomat.com/api/einstellungen/steuerfreie-laender/ -->

# Country Taxes (Steuerfreie Länder / tax-free countries)

API wrapper for the list of tax-free countries at `/country-taxes`. Billomat charges no VAT for each listed country code (ISO 3166 alpha-2) — relevant for invoices to other countries.

> **Mind the endpoint name.** The Billomat docs page is called *Steuerfreie Länder* (tax-free countries), but the HTTP path is `/country-taxes` (not `/tax-free-countries`). The SDK class follows the path: `CountryTaxesApi`.

## Access

```php
$billomat->countryTaxes
```

`Justpilot\Billomat\Api\CountryTaxesApi`.

## Endpoints

| Method | HTTP | Path |
|---|---|---|
| `list($filters?)` | GET | `/country-taxes` |
| `listPage($filters?)` | GET | `/country-taxes` |
| `iterateAll($filters?, $pageSize?)` | GET | `/country-taxes` (all pages) |
| `get($id)` | GET | `/country-taxes/{id}` |
| `getOrFail($id)` | GET | `/country-taxes/{id}` |

> Read-only in the SDK. Billomat supports create/update/delete (`POST`/`PUT`/`DELETE /country-taxes`), but the SDK doesn't expose them as methods.

## Methods

### `list(array $filters = []): list<CountryTax>`

```php
foreach ($billomat->countryTaxes->list() as $row) {
    printf("#%d %s\n", $row->id, $row->countryCode ?? '');
}
```

Filters:

| Parameter | Description |
|---|---|
| `country` | ISO 3166 alpha-2 country code, e.g. `CH`, `US` |
| `page`, `per_page` | Pagination — see [Pagination and filtering](../concepts/pagination-and-filtering.md) |

### `get(int $id): ?CountryTax`

Returns `null` on 404.

### `getOrFail(int $id): CountryTax`

Like `get()`, but throws `NotFoundException` instead of returning `null`.

## Read model: `CountryTax`

`final readonly class CountryTax`.

| Property | Billomat field | Type |
|---|---|---|
| `id` | `id` | `int` |
| `countryCode` | `country_code` | `?string` |
| `extra` | — | `array<string,mixed>`: fields the SDK doesn't map yet, raw (see [Read models](../concepts/models.md)) |

## Pitfalls

- **No tax rate in the model.** Unlike `/taxes` (see [taxes.md](taxes.md)), this endpoint only holds the list of country codes; the tax rate for these countries is implicitly 0 %.
- **Single-item list quirk.** With only one entry Billomat returns an object instead of a list; `listResource()` normalizes this.

## End-to-end example

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\BillomatClient;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

$taxFree = array_map(
    static fn ($row) => $row->countryCode,
    $billomat->countryTaxes->list(),
);

printf("Tax-free: %s\n", implode(', ', array_filter($taxFree)));
```
