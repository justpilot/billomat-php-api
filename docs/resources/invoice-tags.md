<!-- Source: https://www.billomat.com/api/rechnungen/schlagworte/ -->

# Invoice Tags (Schlagworte)

API wrapper for invoice tags at `/invoice-tags`.

## Access

```php
$billomat->invoices->tags
```

The generic `Justpilot\Billomat\Api\TagsApi`, typed for `InvoiceTag`, `InvoiceTagCloudEntry` and `InvoiceTagCreateOptions`.

## Overview

Invoice tags are free-form keywords (`important`, `key-account`, `season`). The same resource provides two different views:

- **Tags of one invoice**, via `?invoice_id={id}`. Response element: `invoice-tag`.
- **Tag cloud**: all tags of the account with their frequency, without a filter. Response element: `tag` (a different key).

The SDK splits this into two methods with two separate read models.

## Endpoints

| Method | HTTP | Path |
|---|---|---|
| `list($invoiceId)` | GET | `/invoice-tags?invoice_id={id}` |
| `cloud()` | GET | `/invoice-tags` |
| `get($id)` / `getOrFail($id)` | GET | `/invoice-tags/{id}` |
| `create($options)` | POST | `/invoice-tags` |
| `delete($id)` | DELETE | `/invoice-tags/{id}` |

There is no `update()` method; delete the tag and create it again.

## Methods

### `list(int $invoiceId): list<InvoiceTag>`

Lists the tags of a single invoice.

```php
$tags = $billomat->invoices->tags->list(98765);

foreach ($tags as $tag) {
    echo $tag->name . "\n";
}
```

### `cloud(): list<InvoiceTagCloudEntry>`

Aggregated list of all tags in the account with their `count`. Useful for autocompletion or a tag cloud in a UI.

```php
foreach ($billomat->invoices->tags->cloud() as $entry) {
    printf("%s (%d invoices)\n", $entry->name, $entry->count);
}
```

### `get(int $id): ?InvoiceTag` / `getOrFail(int $id): InvoiceTag`

`get()` returns `null` on 404; `getOrFail()` throws a `NotFoundException` instead.

### `create(InvoiceTagCreateOptions $options): InvoiceTag`

```php
use Justpilot\Billomat\Api\InvoiceTagCreateOptions;

$tag = $billomat->invoices->tags->create(
    new InvoiceTagCreateOptions(invoiceId: 98765, name: 'key-account'),
);
```

Several tags for the same invoice → several `create()` calls.

### `delete(int $id): void`

Removes the link between invoice and tag. Once the tag isn't used on any invoice, it disappears from the tag cloud automatically.

## Write model: `InvoiceTagCreateOptions`

Constructor: `new InvoiceTagCreateOptions(int $invoiceId, string $name)`. Both values are required. The object is immutable (`final readonly`) and has `withInvoiceId()`/`withName()`.

| Property | Billomat field | Type |
|---|---|---|
| `invoiceId` | `invoice_id` | `int` |
| `name` | `name` | `string` |

## Read model: `InvoiceTag`

`final readonly class InvoiceTag`: one tag linked to an invoice.

| Property | Type |
|---|---|
| `id` | `?int` |
| `invoiceId` | `int` |
| `name` | `string` |
| `customfield` | `?string` (free extra field, see [custom meta attributes](../concepts/custom-meta-attributes.md)) |
| `extra` | `array<string, mixed>`: fields the SDK doesn't map (yet), raw; see [models](../concepts/models.md) |

## Read model: `InvoiceTagCloudEntry`

`final readonly class InvoiceTagCloudEntry`: one aggregated entry from `cloud()`. It intentionally has **no** `invoiceId` field because the entry aggregates several invoices.

| Property | Type |
|---|---|
| `id` | `?int` |
| `name` | `string` |
| `count` | `int` |
| `extra` | `array<string, mixed>` |

## Pitfalls

- **Two views, two models.** `list()` returns `InvoiceTag`, `cloud()` returns `InvoiceTagCloudEntry`. Mixing them up leads to "why doesn't this have an `invoiceId`?" moments.
- **No PUT.** Renaming isn't possible: delete the old link and create a new one.
- **`name` is case-sensitive.** For Billomat, `important` and `Important` are two different tags and appear separately in the tag cloud.

## End-to-end example

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\InvoiceTagCreateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\InvoiceTag;
use Justpilot\Billomat\Model\InvoiceTagCloudEntry;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

$invoiceId = 98765;

// 1) Add tags
foreach (['important', 'key-account', '2026-Q2'] as $name) {
    $billomat->invoices->tags->create(
        new InvoiceTagCreateOptions(invoiceId: $invoiceId, name: $name),
    );
}

// 2) List the invoice's tags
$tags = $billomat->invoices->tags->list($invoiceId);
printf("Tags on invoice #%d: %s\n", $invoiceId, implode(', ', array_map(
    static fn (InvoiceTag $t): string => $t->name,
    $tags,
)));

// 3) Tag cloud: the 5 most used tags
$cloud = $billomat->invoices->tags->cloud();
usort($cloud, static fn (InvoiceTagCloudEntry $a, InvoiceTagCloudEntry $b): int => $b->count <=> $a->count);
foreach (array_slice($cloud, 0, 5) as $entry) {
    printf("%-20s %d\n", $entry->name, $entry->count);
}
```
