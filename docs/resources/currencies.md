<!-- Source: https://www.billomat.com/api/waehrungen/ -->

# Currencies (Währungen)

Read-only API wrapper for the Billomat currency list at `/currencies`.

## Access

```php
$billomat->currencies
```

`Justpilot\Billomat\Api\CurrenciesApi`, created lazily on first access to `$billomat->currencies`.

## Endpoints

| Method | HTTP | Path |
|---|---|---|
| `list($filters?)` | GET | `/currencies` |
| `listPage()` | GET | `/currencies` (page with metadata) |
| `iterateAll()` | GET | `/currencies` (lazy over all pages) |

Fetching a single currency by ID (`GET /currencies/{id}`) is not implemented in the SDK; the list endpoint supports the filter `code` for ISO currency codes.

## Methods

### `list(array $filters = []): list<Currency>`

Lists all currencies.

```php
$currencies = $billomat->currencies->list();

foreach ($currencies as $currency) {
    echo $currency->code . ': ' . $currency->name . PHP_EOL;
}
```

To look up a single currency (e.g. `EUR`), pass `code` as a filter:

```php
$eur = $billomat->currencies->list(['code' => 'EUR'])[0] ?? null;
```

**Exceptions:** `AuthenticationException`, `HttpException`.

### `listPage(array $filters = []): Page<Currency>`

Like `list()`, with pagination metadata (`page`, `perPage`, `total`) in `$page->info`.

### `iterateAll(array $filters = [], int $pageSize = 100): Generator<int, Currency>`

Lazy iteration over all pages.

## Read model: `Currency`

`final readonly class Justpilot\Billomat\Model\Currency`

| Property | Type | Mapping |
|---|---|---|
| `code` | `string` | `code` (fallback: `currency_code`) |
| `name` | `?string` | `name` |
| `symbol` | `?string` | `symbol` |
| `id` | `?int` | `id` |
| `extra` | `array<string,mixed>` | Fields the SDK doesn't map yet, raw (see [Read models](../concepts/models.md)) |

`Currency::fromArray($data)` hydrates from the wire format; `Currency::toArray()` writes snake_case back.
