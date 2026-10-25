<!-- Source: https://www.billomat.com/api/eingangsrechnungen/kategorien/ -->

# Incoming Categories (Eingangsrechnungs-Kategorien)

API wrapper for the read-only list of incoming invoice categories under `/incoming-categories`. Categories are buckets predefined by Billomat, such as *goods*, *services* or *entertainment expenses*, and are referenced on incoming invoices.

## Access

```php
$billomat->incomingCategories
```

`Justpilot\Billomat\Api\IncomingCategoriesApi`.

## Endpoint overview

| Method | HTTP | Path |
|---|---|---|
| `list($filters?)` | GET | `/incoming-categories` |
| `listPage($filters?)` | GET | `/incoming-categories` (one page with metadata) |
| `iterateAll($filters?, $pageSize?)` | GET | `/incoming-categories` (all pages) |
| `get($id)` / `getOrFail($id)` | GET | `/incoming-categories/{id}` |

> Read-only. Billomat documents no write endpoints for this resource.

## Methods

### `list(array $filters = []): list<IncomingCategory>`

```php
foreach ($billomat->incomingCategories->list() as $cat) {
    printf("[%s] %s\n", $cat->id, $cat->title ?? '');
}
```

Pagination via `page`/`per_page`; see [Pagination and filtering](../concepts/pagination-and-filtering.md). `listPage()` additionally returns the pagination metadata, `iterateAll(array $filters = [], int $pageSize = 100)` walks through all pages lazily.

### `get(string $id): ?IncomingCategory`

Note: the ID is a **string** slug (e.g. `goods`, `services`), not a numeric key. Returns `null` on 404. `getOrFail(string $id): IncomingCategory` throws a `NotFoundException` instead.

## Read model: `IncomingCategory`

`final readonly class IncomingCategory`.

| Property | Billomat field | Type |
|---|---|---|
| `id` | `id` | `string` |
| `title` | `title` | `?string` |
| `description` | `description` | `?string` |
| `extra` | — | `array<string, mixed>` (unmapped fields, see [Models](../concepts/models.md)) |

## Pitfalls

- **String ID.** Unlike most Billomat resources, categories are identified by a slug (`goods`, `services`, …) instead of an integer.
- **Single-item list quirk.** With only one category, Billomat returns an object instead of a list; the SDK normalizes this.

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

foreach ($billomat->incomingCategories->iterateAll() as $cat) {
    printf("[%s] %s — %s\n",
        $cat->id,
        $cat->title ?? '(no title)',
        $cat->description ?? '',
    );
}
```
