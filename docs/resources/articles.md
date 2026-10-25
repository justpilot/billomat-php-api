<!-- Source: https://www.billomat.com/api/artikel/ -->

# Articles (Artikel)

API wrapper for articles under `/articles` and their two sub-resources (`/article-tags`, `/article-property-values`).

## Access

```php
$billomat->articles                  // the articles themselves
$billomat->articles->tags            // tags on an article
$billomat->articles->propertyValues  // custom property values on an article
```

The property definitions (schemas) live separately under `$billomat->articles->properties`, see [Property definitions](properties.md). This page only covers the values a specific article holds for those definitions.

## Model

An article is a reusable line item that invoices, offers and recurring invoices can reference as a position.

- **Identification**: the sequential `number` combined with `numberPre` and `numberLength` gives the displayed `article_number`.
- **Prices**: up to five sales price levels (`salesPrice` to `salesPrice5`) for tiered prices or customer groups, optionally a `purchasePrice` with its own currency.
- **References**: `taxId` points to a [tax rate](taxes.md), `unitId` to a [unit](lookups.md), and `supplierId` to a [supplier](suppliers.md). `categoryId` refers to the account's article categories.
- **Type**: `type` (`ItemType`) marks the article as `PRODUCT` or `SERVICE`.
- **Custom fields**: dynamic values via `$billomat->articles->propertyValues` (e.g. "lead time", "warranty"); the property definitions themselves are managed via `$billomat->articles->properties`.

## Endpoint overview

### `/articles`

| Method | HTTP | Path |
|---|---|---|
| `list($filters?)` | GET | `/articles` |
| `listPage($filters?)` | GET | `/articles` (one page with metadata) |
| `iterateAll($filters?, $pageSize?)` | GET | `/articles` (all pages) |
| `get($id)` / `getOrFail($id)` | GET | `/articles/{id}` |
| `create($options)` | POST | `/articles` |
| `update($id, $options)` | PUT | `/articles/{id}` |
| `delete($id)` | DELETE | `/articles/{id}` |

### `/article-tags`

| Method | HTTP | Path |
|---|---|---|
| `list($articleId, $filters?)` | GET | `/article-tags?article_id={id}` |
| `listPage($articleId, $filters?)` / `iterateAll($articleId, $filters?, $pageSize?)` | GET | `/article-tags?article_id={id}` (paginated) |
| `cloud()` | GET | `/article-tags` |
| `get($id)` / `getOrFail($id)` | GET | `/article-tags/{id}` |
| `create($articleId, $options)` | POST | `/article-tags` |
| `delete($id)` | DELETE | `/article-tags/{id}` |

### `/article-property-values`

| Method | HTTP | Path |
|---|---|---|
| `list($articleId?, $filters?)` | GET | `/article-property-values` |
| `listPage($articleId?, $filters?)` | GET | `/article-property-values` |
| `iterateAll($articleId?, $filters?, $pageSize?)` | GET | `/article-property-values` (all pages) |
| `get($id)` / `getOrFail($id)` | GET | `/article-property-values/{id}` |
| `create($articleId, $options)` | POST | `/article-property-values` |

## Articles

### `list(ArticleFilter|array $filters = []): list<Article>`

Filters according to the Billomat docs: `article_number`, `title`, `description`, `currency_code`, `unit_id`, `tags`, `supplier_id`, `category_id`. Use the typed filter `ArticleFilter` with the camelCase fields `articleNumber`, `title`, `description`, `currencyCode`, `unitId`, `tags` (list, sent comma-separated) and `supplierId`, plus `orderBy`, `page` and `perPage`. Parameters without their own field (e.g. `category_id`) go into `extra: [...]`; a plain array still works too.

```php
use Justpilot\Billomat\Api\ArticleFilter;
use Justpilot\Billomat\Api\OrderBy;

$active = $billomat->articles->list(new ArticleFilter(
    currencyCode: 'EUR',
    orderBy: OrderBy::asc('title'),
));
```

`listPage()` returns the same list plus pagination metadata, `iterateAll()` walks through all pages lazily, see [Pagination](../advanced/pagination.md).

### `get(int $id): ?Article`

Returns `null` on 404. `getOrFail(int $id): Article` throws a `NotFoundException` instead.

### `create(ArticleCreateOptions $options): Article`

```php
use Justpilot\Billomat\Api\ArticleCreateOptions;
use Justpilot\Billomat\Model\Enum\ItemType;
use Justpilot\Billomat\Model\Money;

$opts = new ArticleCreateOptions(
    title: 'Web hosting Basic',
    description: 'Shared hosting, 10 GB SSD',
    salesPrice: Money::of('19.90'),
    currencyCode: 'EUR',
    unit: 'month',
    taxId: 12345,
    type: ItemType::SERVICE,
);

$article = $billomat->articles->create($opts);
```

### `update(int $id, ArticleUpdateOptions $options): Article`

Partial update: fields left `null` stay unchanged.

### `delete(int $id): void`

Deletes the article.

## Write model: `ArticleCreateOptions`

Constructor: `new ArticleCreateOptions(string $title, …)`. `title` is required and the first parameter. All other fields are constructor parameters with a default and are passed as named arguments. The object is immutable (`final readonly`); each field has a `with*()` method that returns a modified copy (e.g. `$opts->withDescription('New')`).

### Identification

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `title` | `title` | `string` (required) | — |
| `numberPre` | `number_pre` | `?string` | Article number prefix (e.g. `ART-`) |
| `number` | `number` | `?int` | Sequential number; otherwise Billomat increments it itself |
| `numberLength` | `number_length` | `?int` | Minimum number of digits, padded with zeros |
| `description` | `description` | `?string` | — |
| `type` | `type` | `?ItemType` | `PRODUCT` or `SERVICE` |

### Prices

Amounts are [`Money`](../concepts/money.md) values, not floats.

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `salesPrice` | `sales_price` | `?Money` | Default sales price |
| `salesPrice2` | `sales_price2` | `?Money` | Level 2 (e.g. resellers) |
| `salesPrice3` | `sales_price3` | `?Money` | Level 3 |
| `salesPrice4` | `sales_price4` | `?Money` | Level 4 |
| `salesPrice5` | `sales_price5` | `?Money` | Level 5 |
| `currencyCode` | `currency_code` | `?string` | ISO 4217, e.g. `EUR` |
| `purchasePrice` | `purchase_price` | `?Money` | Purchase price |
| `purchasePriceCurrencyCode` | `purchase_price_currency_code` | `?string` | Separate currency for the purchase price |
| `purchasePriceNetGross` | `purchase_price_net_gross` | `?NetGross` | Whether `purchasePrice` is net or gross |

### References

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `unit` | `unit` | `?string` | Free-text unit (e.g. `piece`), alternative to `unitId` |
| `unitId` | `unit_id` | `?int` | Reference to `/units` |
| `supplierId` | `supplier_id` | `?int` | Reference to a [supplier](suppliers.md) |
| `taxId` | `tax_id` | `?int` | Reference to [taxes](taxes.md) |
| `categoryId` | `category_id` | `?int` | Reference to `/article-categories` |

## Write model: `ArticleUpdateOptions`

Mirrors `ArticleCreateOptions`, but `title` is no longer required and every field is nullable. `null` means "don't send", the value stays unchanged. `Clear::Field` (`Justpilot\Billomat\Api\Clear`) sends the field as JSON `null` so Billomat empties it, e.g. `new ArticleUpdateOptions(description: Clear::Field)`.

## Read model: `Article`

`final readonly class Article`.

| Property | Type |
|---|---|
| `id` | `int` |
| `created` | `?\DateTimeImmutable` |
| `articleNumber` | `?string` (combined: `numberPre` + padded `number`) |
| `number` | `?int` |
| `numberPre` | `?string` |
| `numberLength` | `?int` |
| `title` | `?string` |
| `description` | `?string` |
| `salesPrice` | `?Money` |
| `salesPrice2` … `salesPrice5` | `?Money` |
| `currencyCode` | `?string` |
| `unitId` | `?int` |
| `purchasePrice` | `?Money` |
| `supplierId` | `?int` |
| `taxId` | `?int` |
| `updated` | `?\DateTimeImmutable` |
| `archived` | `?bool` |
| `type` | `?ItemType` |
| `revenueAccountNumber` | `?string` |
| `costCenter` | `?string` |
| `purchasePriceNetGross` | `?NetGross` (whether `purchasePrice` is net or gross) |
| `customfield` | `?string` (free-form extra field, see [Custom meta attributes](../concepts/custom-meta-attributes.md)) |
| `extra` | `array<string, mixed>` (unmapped fields, see [Models](../concepts/models.md)) |

## Article tags

Works exactly like [Invoice Tags](invoice-tags.md), but for an article instead of an invoice.

### Methods

```php
use Justpilot\Billomat\Api\ArticleTagCreateOptions;

$tag = $billomat->articles->tags->create(
    $article->id,
    new ArticleTagCreateOptions(name: 'hosting'),
);

$tags  = $billomat->articles->tags->list($article->id);
$cloud = $billomat->articles->tags->cloud(); // aggregated, with count
$billomat->articles->tags->delete($tag->id);
```

There is no `update()`: delete the tag and create a new one.

### Write model: `ArticleTagCreateOptions`

The article ID isn't part of the options: pass it as the first argument of `create()`, which adds `article_id` to the payload.

Both values are required and set via the constructor:

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `name` | `name` | `string` (required) | — |

### Read model: `ArticleTag`

| Property | Type |
|---|---|
| `id` | `int` |
| `articleId` | `int` |
| `name` | `string` |
| `customfield` | `?string` (free-form extra field, see [Custom meta attributes](../concepts/custom-meta-attributes.md)) |
| `extra` | `array<string, mixed>` |

### Read model: `ArticleTagCloudEntry`

| Property | Type |
|---|---|
| `id` | `?int` |
| `name` | `string` |
| `count` | `int` |
| `extra` | `array<string, mixed>` |

## Article property values

Stores the values for the article properties defined in [`properties.md`](properties.md) (`$billomat->articles->properties`). A property definition (`ArticleProperty`) is the schema (name + `PropertyType`); an `ArticlePropertyValue` is the actual value on one article.

```php
use Justpilot\Billomat\Api\ArticlePropertyValueCreateOptions;
use Justpilot\Billomat\Api\ArticlePropertyValueFilter;
use Justpilot\Billomat\Api\OrderBy;

// List the values of one article
$values = $billomat->articles->propertyValues->list($article->id);

// Only the values of property 12, typed filter with sorting
$values = $billomat->articles->propertyValues->list($article->id, new ArticlePropertyValueFilter(
    articlePropertyId: 12,
    orderBy: OrderBy::desc('updated'),
));

// Set the value of the property "Lead time" (id = 42)
$value = $billomat->articles->propertyValues->create(
    $article->id,
    new ArticlePropertyValueCreateOptions(
        articlePropertyId: 42,
        value: '3-5 business days',
    ),
);
```

There is neither `update()` nor `delete()`: calling `create()` again with the same `articlePropertyId` overwrites the existing value.

### Write model: `ArticlePropertyValueCreateOptions`

The article ID isn't part of the options: pass it as the first argument of `create()`, which adds `article_id` to the payload.

Both values are required and set via the constructor.

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `articlePropertyId` | `article_property_id` | `int` (required) | ID of the property definition |
| `value` | `value` | `mixed` (required) | `CHECKBOX` expects `0`/`1` |

### Read model: `ArticlePropertyValue`

| Property | Type |
|---|---|
| `id` | `int` |
| `articleId` | `int` |
| `articlePropertyId` | `int` |
| `type` | `?PropertyType` (`TEXTFIELD`, `TEXTAREA`, `CHECKBOX`) |
| `name` | `?string` (name from the property definition) |
| `value` | `mixed` |
| `created`, `updated` | `?\DateTimeImmutable` |
| `customfield` | `?string` (free-form extra field, see [Custom meta attributes](../concepts/custom-meta-attributes.md)) |
| `extra` | `array<string, mixed>` |

## Enums used

- [`ItemType`](../../src/Model/Enum/ItemType.php): `PRODUCT`, `SERVICE` (`type`; replaces the former `ArticleType`).
- [`NetGross`](../../src/Model/Enum/NetGross.php): `NET`, `GROSS`, `SETTINGS` (`purchasePriceNetGross`).
- [`PropertyType`](../../src/Model/Enum/PropertyType.php): `TEXTFIELD`, `TEXTAREA`, `CHECKBOX`, on the property definition ([`ArticleProperty`](../../src/Model/ArticleProperty.php)) and on `ArticlePropertyValue::$type`.

## Pitfalls

- **No `update()` for tags and property values.** Delete tags and create them again; for property values, another POST with the same `article_property_id` overwrites the existing entry.
- **`unit` vs. `unitId`.** Both fields exist side by side: `unit` is a free string, `unitId` a reference to `/units`. Setting both at once can make documents display differently than expected; set only one.
- **`number` is assigned automatically otherwise.** Without an explicit `number`, Billomat increments from the last assigned article number. A manual `number` must be unique, otherwise you get a `422`.
- **`articleNumber` is read-only.** The field only appears in the read model and is built server-side from `numberPre` + `number` (padded to `numberLength`).
- **`purchasePriceCurrencyCode` can differ from `currencyCode`.** For an article bought in USD and sold in EUR, set both fields.
- **`PropertyType::CHECKBOX` expects `0`/`1` as `value`.** Boolean strings (`"true"`/`"false"`) aren't recognized.

## End-to-end example

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\ArticleCreateOptions;
use Justpilot\Billomat\Api\ArticlePropertyValueCreateOptions;
use Justpilot\Billomat\Api\ArticleTagCreateOptions;
use Justpilot\Billomat\Api\ArticleUpdateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\Enum\ItemType;
use Justpilot\Billomat\Model\Money;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

// 1) Create an article
$opts = new ArticleCreateOptions(
    title: 'Web hosting Basic',
    description: 'Shared hosting, 10 GB SSD, daily backup',
    salesPrice: Money::of('19.90'),
    salesPrice2: Money::of('17.90'), // reseller level
    currencyCode: 'EUR',
    unit: 'month',
    taxId: 12345,
    type: ItemType::SERVICE,
);

$article = $billomat->articles->create($opts);
printf("Article #%d (%s) created\n", $article->id, $article->articleNumber);

// 2) Add tags for later filtering
$billomat->articles->tags->create(
    $article->id,
    new ArticleTagCreateOptions(name: 'hosting'),
);
$billomat->articles->tags->create(
    $article->id,
    new ArticleTagCreateOptions(name: 'subscription'),
);

// 3) Set a custom property ("Lead time" = ArticleProperty #42)
$billomat->articles->propertyValues->create(
    $article->id,
    new ArticlePropertyValueCreateOptions(
        articlePropertyId: 42,
        value: 'Available immediately',
    ),
);

// 4) Change the price later: partial update
$update = new ArticleUpdateOptions(salesPrice: Money::of('24.90'));
$billomat->articles->update($article->id, $update);

// 5) Cloud overview of all article tags
foreach ($billomat->articles->tags->cloud() as $entry) {
    printf("%s (%d)\n", $entry->name, $entry->count);
}
```
