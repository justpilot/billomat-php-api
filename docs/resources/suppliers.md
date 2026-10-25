<!-- Source: https://www.billomat.com/api/lieferanten/ -->

# Suppliers (Lieferanten)

API wrapper for suppliers under `/suppliers` and their two sub-resources (`/supplier-tags`, `/supplier-property-values`).

## Access

```php
$billomat->suppliers                  // the suppliers themselves
$billomat->suppliers->tags            // tags on a supplier
$billomat->suppliers->propertyValues  // custom property values on a supplier
```

The property definitions (schemas) live separately under `$billomat->suppliers->properties`, see [Property definitions](properties.md). This page only covers the values a specific supplier holds for those definitions.

## Model

A supplier is the counterpart of a [client](clients.md) on the purchasing side: suppliers appear on incoming invoices (see [Incomings](incomings.md)) and can be assigned to an [article](articles.md) as default supplier (`Article::$supplierId`).

- **Address**: split into `street`, `zip`, `city`, `state`, `countryCode` (ISO alpha-2). The read model also has a combined `address` field that Billomat builds from the parts.
- **Contact**: `email`, `phone`, `fax`, `mobile`, `www`, plus an optional personal salutation (`salutation`, `firstName`, `lastName`).
- **Tax and bank**: `taxNumber`, `vatNumber`, a full bank block (`bankIban`, `bankBic`, …) for transfers to the supplier, and the SEPA `creditorIdentifier`.

## Endpoint overview

### `/suppliers`

| Method | HTTP | Path |
|---|---|---|
| `list($filters?)` | GET | `/suppliers` |
| `listPage($filters?)` | GET | `/suppliers` (one page with metadata) |
| `iterateAll($filters?, $pageSize?)` | GET | `/suppliers` (all pages) |
| `get($id)` / `getOrFail($id)` | GET | `/suppliers/{id}` |
| `create($options)` | POST | `/suppliers` |
| `update($id, $options)` | PUT | `/suppliers/{id}` |
| `delete($id)` | DELETE | `/suppliers/{id}` |

### `/supplier-tags`

| Method | HTTP | Path |
|---|---|---|
| `list($supplierId, $filters?)` | GET | `/supplier-tags?supplier_id={id}` |
| `listPage($supplierId, $filters?)` / `iterateAll($supplierId, $filters?, $pageSize?)` | GET | `/supplier-tags?supplier_id={id}` (paginated) |
| `cloud()` | GET | `/supplier-tags` |
| `get($id)` / `getOrFail($id)` | GET | `/supplier-tags/{id}` |
| `create($supplierId, $options)` | POST | `/supplier-tags` |
| `delete($id)` | DELETE | `/supplier-tags/{id}` |

### `/supplier-property-values`

| Method | HTTP | Path |
|---|---|---|
| `list($supplierId?, $filters?)` | GET | `/supplier-property-values` |
| `listPage($supplierId?, $filters?)` | GET | `/supplier-property-values` |
| `iterateAll($supplierId?, $filters?, $pageSize?)` | GET | `/supplier-property-values` (all pages) |
| `get($id)` / `getOrFail($id)` | GET | `/supplier-property-values/{id}` |
| `create($supplierId, $options)` | POST | `/supplier-property-values` |

## Suppliers

### `list(SupplierFilter|array $filters = []): list<Supplier>`

Filters according to the Billomat docs: `name`, `client_number`, `email`, `first_name`, `last_name`, `country_code`, `creditor_identifier`, `note`, `incoming_id`, `tags`. Use the typed filter `SupplierFilter`; its fields are the camelCase versions of these names (`countryCode`, `creditorIdentifier`, `incomingId`, …; `incomingId` and `tags` are lists, sent comma-separated), plus `orderBy`, `page`, `perPage` and `extra` for other parameters. A plain array still works; array values are encoded as `key[]=…`.

```php
use Justpilot\Billomat\Api\OrderBy;
use Justpilot\Billomat\Api\SupplierFilter;

$german = $billomat->suppliers->list(new SupplierFilter(
    countryCode: 'DE',
    orderBy: OrderBy::asc('name'),
));
```

`listPage()` returns the same list plus pagination metadata, `iterateAll()` walks through all pages lazily, see [Pagination](../advanced/pagination.md).

### `get(int $id): ?Supplier`

Returns `null` on 404. `getOrFail(int $id): Supplier` throws a `NotFoundException` instead.

### `create(SupplierCreateOptions $options): Supplier`

```php
use Justpilot\Billomat\Api\SupplierCreateOptions;

$opts = new SupplierCreateOptions(
    name: 'Hetzner Online GmbH',
    street: 'Industriestr. 25',
    zip: '91710',
    city: 'Gunzenhausen',
    countryCode: 'DE',
    vatNumber: 'DE812871812',
    bankIban: 'DE89370400440532013000',
    bankBic: 'COBADEFFXXX',
    currencyCode: 'EUR',
);

$supplier = $billomat->suppliers->create($opts);
```

### `update(int $id, SupplierUpdateOptions $options): Supplier`

Partial update: fields left `null` stay unchanged.

### `delete(int $id): void`

Deletes the supplier.

## Write model: `SupplierCreateOptions`

Constructor: `new SupplierCreateOptions(string $name, …)`. `name` is required and the first parameter. All other fields are constructor parameters with a default and are passed as named arguments. The object is immutable (`final readonly`); each field has a `with*()` method that returns a modified copy (e.g. `$opts->withEmail('new@example.com')`).

### Identification and salutation

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `name` | `name` | `string` (required) | Company name or short name |
| `clientNumber` | `client_number` | `?string` | Your own customer number at the supplier |
| `salutation` | `salutation` | `?string` | e.g. `Herr` / `Frau` |
| `firstName` | `first_name` | `?string` | Contact person |
| `lastName` | `last_name` | `?string` | Contact person |
| `note` | `note` | `?string` | Free-text note |

### Address

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `street` | `street` | `?string` | — |
| `zip` | `zip` | `?string` | Postal code |
| `city` | `city` | `?string` | — |
| `state` | `state` | `?string` | State/region |
| `countryCode` | `country_code` | `?string` | ISO 3166-1 alpha-2, e.g. `DE` |

### Contact

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `email` | `email` | `?string` | — |
| `phone` | `phone` | `?string` | — |
| `fax` | `fax` | `?string` | — |
| `mobile` | `mobile` | `?string` | — |
| `www` | `www` | `?string` | Website |

### Tax and bank

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `taxNumber` | `tax_number` | `?string` | Local tax number |
| `vatNumber` | `vat_number` | `?string` | VAT ID |
| `bankAccountNumber` | `bank_account_number` | `?string` | Legacy account number format |
| `bankAccountOwner` | `bank_account_owner` | `?string` | Account holder |
| `bankNumber` | `bank_number` | `?string` | Legacy bank code (BLZ) format |
| `bankName` | `bank_name` | `?string` | — |
| `bankIban` | `bank_iban` | `?string` | IBAN |
| `bankBic` | `bank_bic` | `?string` | BIC/SWIFT |
| `bankSwift` | `bank_swift` | `?string` | BIC/SWIFT as named in the Billomat docs; alias of `bankBic`, both are serialized |
| `currencyCode` | `currency_code` | `?string` | Default currency for incoming invoices |
| `creditorIdentifier` | `creditor_identifier` | `?string` | SEPA creditor ID |

## Write model: `SupplierUpdateOptions`

Mirrors `SupplierCreateOptions`, but `name` is no longer required and every field is nullable. `null` means "don't send", the value stays unchanged. `Clear::Field` (`Justpilot\Billomat\Api\Clear`) sends the field as JSON `null` so Billomat empties it, e.g. `new SupplierUpdateOptions(note: Clear::Field)`.

## Read model: `Supplier`

`final readonly class Supplier`. Note: `name` is a non-nullable `string` in the read model too.

| Property | Type |
|---|---|
| `id` | `int` |
| `created` | `?\DateTimeImmutable` |
| `name` | `string` |
| `clientNumber` | `?int` |
| `salutation`, `firstName`, `lastName` | `?string` |
| `street`, `zip`, `city`, `state`, `countryCode` | `?string` |
| `address` | `?string` (read-only: address line assembled server-side) |
| `note` | `?string` |
| `email`, `phone`, `fax`, `mobile`, `www` | `?string` |
| `taxNumber`, `vatNumber` | `?string` |
| `bankAccountNumber`, `bankAccountOwner`, `bankNumber`, `bankName`, `bankIban`, `bankBic` | `?string` |
| `currencyCode` | `?string` |
| `updated` | `?\DateTimeImmutable` |
| `isEuCountry` | `?bool` |
| `locale` | `?string` |
| `creditorAccountNumber` | `?string` |
| `creditorIdentifier` | `?string` |
| `costsGross` | `?Money` (read-only, see [Money](../concepts/money.md)) |
| `costsNet` | `?Money` (read-only) |
| `customfield` | `?string` (free-form extra field, see [Custom meta attributes](../concepts/custom-meta-attributes.md)) |
| `extra` | `array<string, mixed>` (unmapped fields, see [Models](../concepts/models.md)) |

## Supplier tags

Works exactly like [Article tags](articles.md#article-tags), but for a supplier instead of an article.

### Methods

```php
use Justpilot\Billomat\Api\SupplierTagCreateOptions;

$tag = $billomat->suppliers->tags->create(
    $supplier->id,
    new SupplierTagCreateOptions(name: 'hosting'),
);

$tags  = $billomat->suppliers->tags->list($supplier->id);
$cloud = $billomat->suppliers->tags->cloud(); // aggregated, with count
$billomat->suppliers->tags->delete($tag->id);
```

There is no `update()`: delete the tag and create a new one.

### Write model: `SupplierTagCreateOptions`

The supplier ID isn't part of the options: pass it as the first argument of `create()`, which adds `supplier_id` to the payload.

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `name` | `name` | `string` (required) | — |

### Read model: `SupplierTag`

| Property | Type |
|---|---|
| `id` | `int` |
| `supplierId` | `int` |
| `name` | `string` |
| `customfield` | `?string` (free-form extra field, see [Custom meta attributes](../concepts/custom-meta-attributes.md)) |
| `extra` | `array<string, mixed>` |

### Read model: `SupplierTagCloudEntry`

| Property | Type |
|---|---|
| `id` | `?int` |
| `name` | `string` |
| `count` | `int` |
| `extra` | `array<string, mixed>` |

## Supplier property values

Stores the values for the supplier properties defined in [`properties.md`](properties.md) (`$billomat->suppliers->properties`). A property definition (`SupplierProperty`) is the schema (name + `PropertyType`); a `SupplierPropertyValue` is the actual value on one supplier.

```php
use Justpilot\Billomat\Api\SupplierPropertyValueCreateOptions;
use Justpilot\Billomat\Api\SupplierPropertyValueFilter;
use Justpilot\Billomat\Api\OrderBy;

// List the values of one supplier
$values = $billomat->suppliers->propertyValues->list($supplier->id);

// Only the values of property 12, typed filter with sorting
$values = $billomat->suppliers->propertyValues->list($supplier->id, new SupplierPropertyValueFilter(
    supplierPropertyId: 12,
    orderBy: OrderBy::desc('updated'),
));

// Set the value of the property "Accounting creditor number" (id = 17)
$value = $billomat->suppliers->propertyValues->create(
    $supplier->id,
    new SupplierPropertyValueCreateOptions(
        supplierPropertyId: 17,
        value: '70001',
    ),
);
```

There is neither `update()` nor `delete()`: calling `create()` again with the same `supplierPropertyId` overwrites the existing value.

### Write model: `SupplierPropertyValueCreateOptions`

The supplier ID isn't part of the options: pass it as the first argument of `create()`, which adds `supplier_id` to the payload.

Both values are required and set via the constructor.

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `supplierPropertyId` | `supplier_property_id` | `int` (required) | ID of the property definition |
| `value` | `value` | `mixed` (required) | `CHECKBOX` expects `0`/`1` |

### Read model: `SupplierPropertyValue`

| Property | Type |
|---|---|
| `id` | `int` |
| `supplierId` | `int` |
| `supplierPropertyId` | `int` |
| `type` | `?PropertyType` (`TEXTFIELD`, `TEXTAREA`, `CHECKBOX`) |
| `name` | `?string` (name from the property definition) |
| `value` | `mixed` |
| `created`, `updated` | `?\DateTimeImmutable` |
| `customfield` | `?string` (free-form extra field, see [Custom meta attributes](../concepts/custom-meta-attributes.md)) |
| `extra` | `array<string, mixed>` |

## Enums used

- [`PropertyType`](../../src/Model/Enum/PropertyType.php): `TEXTFIELD`, `TEXTAREA`, `CHECKBOX`, on the property definition ([`SupplierProperty`](../../src/Model/SupplierProperty.php)) and on `SupplierPropertyValue::$type`.

Suppliers have no other resource-specific enums; all fields on `Supplier` itself are plain strings (`countryCode`, `currencyCode`, …).

## Pitfalls

- **No `update()` for tags and property values.** Delete tags and create them again; for property values, another POST with the same `supplier_property_id` overwrites the existing entry.
- **`address` is read-only.** The field appears in the `Supplier` read model but not in `SupplierCreateOptions`/`SupplierUpdateOptions`; Billomat builds it from `street`/`zip`/`city`/`countryCode`.
- **`clientNumber` has different types.** You write it as `?string` (your customer number at the supplier); the read model types it as `?int`.
- **`Supplier::$name` is non-null.** Unlike almost all other fields, `name` is a `string` (not `?string`) in the read model; an empty name yields `''`, not `null`.
- **`bankBic` arrives as `bank_swift`.** Billomat returns the BIC in the `bank_swift` field; the read model maps it to `bankBic` (falling back to `bank_bic`). On write, `bankBic` and `bankSwift` are sent as separate fields.
- **Legacy vs. modern bank details.** `bankAccountNumber`/`bankNumber` are the German pre-SEPA fields. For SEPA transfers `bankIban`/`bankBic` matter; you can set both pairs, but Billomat primarily uses IBAN/BIC for transfers.
- **`currencyCode` as default.** The value is copied to new incoming invoices ([Incomings](incomings.md)) and can be overridden per document there.
- **Articles link via `Article::$supplierId`.** When a supplier is deleted, affected articles only lose the reference; they aren't deleted.
- **`PropertyType::CHECKBOX` expects `0`/`1` as `value`.** Boolean strings (`"true"`/`"false"`) aren't recognized.

## End-to-end example

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\SupplierCreateOptions;
use Justpilot\Billomat\Api\SupplierPropertyValueCreateOptions;
use Justpilot\Billomat\Api\SupplierTagCreateOptions;
use Justpilot\Billomat\Api\SupplierUpdateOptions;
use Justpilot\Billomat\BillomatClient;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

// 1) Create a supplier
$opts = new SupplierCreateOptions(
    name: 'Hetzner Online GmbH',
    clientNumber: 'K-123456',
    street: 'Industriestr. 25',
    zip: '91710',
    city: 'Gunzenhausen',
    countryCode: 'DE',
    email: 'billing@hetzner.com',
    www: 'https://www.hetzner.com',
    vatNumber: 'DE812871812',
    bankIban: 'DE89370400440532013000',
    bankBic: 'COBADEFFXXX',
    currencyCode: 'EUR',
);

$supplier = $billomat->suppliers->create($opts);
printf("Supplier #%d (%s) created\n", $supplier->id, $supplier->name);

// 2) Add tags
$billomat->suppliers->tags->create(
    $supplier->id,
    new SupplierTagCreateOptions(name: 'hosting'),
);
$billomat->suppliers->tags->create(
    $supplier->id,
    new SupplierTagCreateOptions(name: 'monthly-invoice'),
);

// 3) Set a custom property (SupplierProperty #17 = "Accounting creditor number")
$billomat->suppliers->propertyValues->create(
    $supplier->id,
    new SupplierPropertyValueCreateOptions(
        supplierPropertyId: 17,
        value: '70001',
    ),
);

// 4) Add contact details later: partial update
$update = new SupplierUpdateOptions(phone: '+49 9831 505-0', note: 'Default hosting partner since 2018.');
$billomat->suppliers->update($supplier->id, $update);

// 5) Cloud overview of all supplier tags
foreach ($billomat->suppliers->tags->cloud() as $entry) {
    printf("%s (%d)\n", $entry->name, $entry->count);
}
```
