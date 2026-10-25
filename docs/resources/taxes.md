<!-- Source: https://www.billomat.com/api/einstellungen/steuersaetze/ -->

# Taxes (Steuersätze / tax rates)

API wrapper for managing tax rates at `/taxes`. These are the account-wide tax rates (e.g. "MwSt 19 %"). The tax actually applied to a document item is stored on the item itself (`InvoiceItemCreateOptions::$taxRate`).

## Access

```php
$billomat->taxes
```

`Justpilot\Billomat\Api\TaxesApi`.

## Endpoints

| Method | HTTP | Path |
|---|---|---|
| `list($query?)` | GET | `/taxes` |
| `get($id)` | GET | `/taxes/{id}` |
| `getOrFail($id)` | GET | `/taxes/{id}` |
| `create($options)` | POST | `/taxes` |
| `update($id, $options)` | PUT | `/taxes/{id}` |
| `delete($id)` | DELETE | `/taxes/{id}` |

## Methods

### `list(array $query = []): list<TaxRate>`

```php
$rates = $billomat->taxes->list();

foreach ($rates as $rate) {
    printf("#%d %s — %.2f %% %s\n",
        $rate->id,
        $rate->name,
        $rate->rate,
        $rate->isDefault ? '(default)' : '',
    );
}
```

`$query` takes pagination parameters (`page`, `per_page`).

### `get(int $id): ?TaxRate`

Returns `null` on 404.

### `getOrFail(int $id): TaxRate`

Like `get()`, but throws `NotFoundException` instead of returning `null`.

### `create(TaxRateCreateOptions $options): TaxRate`

```php
use Justpilot\Billomat\Api\TaxRateCreateOptions;

$opts = new TaxRateCreateOptions(name: 'Ermäßigt 7%', rate: 7.0, isDefault: false);
$created = $billomat->taxes->create($opts);
```

### `update(int $id, TaxRateCreateOptions $options): TaxRate`

Uses the **same** options class as `create()`. There is no partial update: you always send a complete tax rate.

### `delete(int $id): void`

Deletes a tax rate. Note: this fails while the rate is still used on documents or articles (see `TaxRate::$canDelete`).

## Write model: `TaxRateCreateOptions`

Constructor: `new TaxRateCreateOptions(string $name, float $rate, bool $isDefault = false)`. The object is `final readonly`; the `with*()` methods (e.g. `$opts->withRate(7.0)`) return a modified copy.

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `name` | `name` | `string` | Required (e.g. `MwSt`) |
| `rate` | `rate` | `float` | Required, percentage (e.g. `19.0`) |
| `isDefault` | `is_default` | `bool` | Defaults to `false`. Serialized as `1`/`0`. |

`toArray()` doesn't filter anything here — all three fields are always set.

## Read model: `TaxRate`

`final readonly class TaxRate`.

| Property | Type | Notes |
|---|---|---|
| `id` | `?int` | |
| `name` | `string` | |
| `rate` | `float` | Percentage |
| `isDefault` | `bool` | |
| `created`, `updated` | `?\DateTimeImmutable` |  |
| `canDelete` | `?bool` | Whether the tax rate can be deleted; `false` while it is used on documents or articles |
| `customfield` | `?string` | Free extra field, see [Custom meta attributes](../concepts/custom-meta-attributes.md) |
| `extra` | `array<string,mixed>` | Fields the SDK doesn't map yet, raw (see [Read models](../concepts/models.md)) |

## Enums

None. `name` is free text and `rate` a `float`. Tax rates are percentages, not amounts, so they stay `float` rather than `Money`.

## Pitfalls

- **`isDefault = true` is exclusive.** When you make a tax rate the default, the others lose the flag automatically on the server.
- **Deleting a rate that is in use fails.** If `delete()` throws a `ValidationException` (422), the rate is still used. Check `canDelete` first, or deactivate the rate in the Billomat UI instead of deleting it.
- **Single-item list quirk.** With only one tax rate in the account Billomat returns an object instead of a list; `list()` normalizes this.

## End-to-end example

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\TaxRateCreateOptions;
use Justpilot\Billomat\BillomatClient;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

// Current tax rates
foreach ($billomat->taxes->list() as $rate) {
    printf("[#%d] %s — %.2f %%%s\n",
        $rate->id,
        $rate->name,
        $rate->rate,
        $rate->isDefault ? ' (default)' : '',
    );
}

// Create a new tax rate
$new = $billomat->taxes->create(new TaxRateCreateOptions(
    name: 'Reverse-Charge',
    rate: 0.0,
    isDefault: false,
));
printf("Created: #%d %s\n", $new->id, $new->name);

// Make it the default (full payload, no partial update)
$updated = $billomat->taxes->update(
    $new->id,
    new TaxRateCreateOptions(name: $new->name, rate: $new->rate)->withIsDefault(true),
);
var_dump($updated->isDefault); // true

// Delete
$billomat->taxes->delete($new->id);
```
