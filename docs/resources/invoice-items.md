<!-- Source: https://www.billomat.com/api/rechnungen/positionen/ -->

# Invoice Items (Rechnungspositionen)

API wrapper for the items (line items) of an invoice. Endpoints at `/invoice-items`.

## Access

```php
$billomat->invoices->items
```

The generic `Justpilot\Billomat\Api\ItemsApi`, typed for `InvoiceItem`, `InvoiceItemCreateOptions` and `InvoiceItemUpdateOptions`.

## Endpoints

| Method | HTTP | Path |
|---|---|---|
| `list($invoiceId, $query?)` | GET | `/invoice-items?invoice_id={id}` |
| `get($id)` / `getOrFail($id)` | GET | `/invoice-items/{id}` |
| `create($invoiceId, $options)` | POST | `/invoice-items` |
| `update($id, $options)` | PUT | `/invoice-items/{id}` |
| `delete($id)` | DELETE | `/invoice-items/{id}` |

## Methods

### `list(int $invoiceId, array $query = []): list<InvoiceItem>`

Lists all items of an invoice. `invoice_id` is set as a filter automatically; further parameters (`page`, `per_page`, …) can be passed in `$query`.

```php
$items = $billomat->invoices->items->list(98765, [
    'per_page' => 100,
]);
```

### `get(int $id): ?InvoiceItem` / `getOrFail(int $id): InvoiceItem`

`get()` returns `null` on 404; `getOrFail()` throws a `NotFoundException` instead.

### `create(int $invoiceId, InvoiceItemCreateOptions $options): InvoiceItem`

Adds an item to an existing invoice. The SDK inserts `invoice_id` into the payload.

```php
use Justpilot\Billomat\Api\InvoiceItemCreateOptions;
use Justpilot\Billomat\Model\Enum\ItemType;
use Justpilot\Billomat\Model\Money;

$item = new InvoiceItemCreateOptions(
    quantity: 3.0,
    unitPrice: Money::of('49.90'),
    title: 'Premium support (3 months)',
    type: ItemType::SERVICE,
    unit: 'month',
);

$created = $billomat->invoices->items->create($invoiceId, $item);
```

### `update(int $id, InvoiceItemUpdateOptions $options): InvoiceItem`

Updates an existing item. Only the fields you set are sent; Billomat keeps all other fields (checked against the sandbox). `Clear::Field` empties a field. Passing `InvoiceItemCreateOptions` throws an `InvalidArgumentException`.

```php
use Justpilot\Billomat\Api\InvoiceItemUpdateOptions;

$updated = $billomat->invoices->items->update($created->id, new InvoiceItemUpdateOptions(unitPrice: Money::of('25')));
```

### `delete(int $id): void`

Deletes an item. Errors are thrown as exceptions.

## Write model: `InvoiceItemCreateOptions`

Constructor: `new InvoiceItemCreateOptions(float $quantity, Money $unitPrice, …)`. The object is immutable (`final readonly`); all other fields are optional named arguments, and every field has a `with*()` method that returns a modified copy.

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `type` | `type` | `?ItemType` | `PRODUCT` or `SERVICE` |
| `articleId` | `article_id` | `?int` | If linked to an article |
| `title` | `title` | `?string` | Item name |
| `description` | `description` | `?string` | Multi-line description |
| `quantity` | `quantity` | `float` | Required (constructor) |
| `unitPrice` | `unit_price` | `Money` | Required (constructor) |
| `unit` | `unit` | `?string` | Free text, e.g. `hour`, `piece`, `month` |
| `taxName` | `tax_name` | `?string` | Name of the tax rate, e.g. `MwSt` |
| `taxRate` | `tax_rate` | `?float` | Percentage, e.g. `19.0` |
| `taxChangedManually` | `tax_changed_manually` | `?bool` | Set when the tax rate differs from the client's/article's default |
| `reduction` | `reduction` | `?string` | Discount, absolute (`"5"`) or relative (`"10%"`) |
| `position` | `position` | `?int` | Sort order; usually assigned automatically on create |

`toArray()` drops `null` values. `quantity` and `unit_price` are never `null`, so they are always sent (`unit_price` as a string via `Money::toString()`).

## Write model: `InvoiceItemUpdateOptions`

Same fields as `InvoiceItemCreateOptions`, but all of them optional: `new InvoiceItemUpdateOptions(unitPrice: Money::of('25'))`. Each field is `X|Clear|null`; `null` (not set) is left out of the payload, `Clear::Field` is sent as `null` and empties the field. Every field has a `with*()` method.

## Read model: `InvoiceItem`

`final readonly class InvoiceItem`.

| Property | Type | Notes |
|---|---|---|
| `id` | `int` | |
| `invoiceId` | `?int` | |
| `articleId` | `?int` | |
| `position` | `?int` | Sort order within the invoice |
| `unit` | `?string` | |
| `quantity` | `float` | |
| `unitPrice` | `Money` | |
| `taxName` | `?string` | |
| `taxRate` | `?float` | |
| `taxChangedManually` | `?bool` | |
| `title` | `?string` | |
| `description` | `?string` | |
| `reduction` | `?string` | |
| `type` | `?ItemType` | |
| `totalGross`, `totalNet` | `?Money` | Including discount |
| `totalGrossUnreduced`, `totalNetUnreduced` | `?Money` | Without discount |
| `customfield` | `?string` | Free extra field, see [custom meta attributes](../concepts/custom-meta-attributes.md) |
| `extra` | `array<string, mixed>` | Fields the SDK doesn't map (yet), raw; see [models](../concepts/models.md) |

## Enums

- [`ItemType`](../../src/Model/Enum/ItemType.php): `PRODUCT`, `SERVICE`.

## Pitfalls

- **Changing the tax rate manually.** If the item should differ from the client's/article's default tax rate, set `taxRate` **and** `taxChangedManually: true`. Without the flag, Billomat often ignores the manual `taxRate`.
- **Order.** On create, `position` is usually irrelevant; Billomat appends the item at the end. On update you can set `position` to reorder. Other items are not shifted automatically.
- **Items in the invoice's `create()` call.** If you create an invoice directly with `items: […]`, the items are part of the same POST request; no second call is needed. See [Invoices](invoices.md).

## End-to-end example

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\InvoiceCreateOptions;
use Justpilot\Billomat\Api\InvoiceItemCreateOptions;
use Justpilot\Billomat\Api\InvoiceItemUpdateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\Enum\ItemType;
use Justpilot\Billomat\Model\Money;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

// Create an empty invoice
$invoice = $billomat->invoices->create(new InvoiceCreateOptions(clientId: 12345));

// Add the first item via the items API
$first = new InvoiceItemCreateOptions(
    quantity: 10.0,
    unitPrice: Money::of('12.5'),
    title: 'License fee module A',
    type: ItemType::PRODUCT,
    unit: 'license',
);

$created = $billomat->invoices->items->create($invoice->id, $first);

// Second item
$second = new InvoiceItemCreateOptions(
    quantity: 2.5,
    unitPrice: Money::of('110'),
    title: 'Customization & support',
    type: ItemType::SERVICE,
    unit: 'hour',
);

$billomat->invoices->items->create($invoice->id, $second);

// Read all items
foreach ($billomat->invoices->items->list($invoice->id) as $item) {
    printf("- %s: %.2f x %s = %s\n", $item->title, $item->quantity, $item->unitPrice->format(), ($item->totalNet ?? Money::zero())->format());
}

// Update the first item: only the price is sent, quantity and title stay the same
$billomat->invoices->items->update($created->id, new InvoiceItemUpdateOptions(unitPrice: Money::of('9.99')));
```
