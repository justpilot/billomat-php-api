<!-- Source: https://www.billomat.com/api/einstellungen/artikel-attribute/ + https://www.billomat.com/api/einstellungen/kunden-attribute/ + https://www.billomat.com/api/einstellungen/lieferanten-attribute/ + https://www.billomat.com/api/einstellungen/eingangsrechnung-attribute/ + https://www.billomat.com/api/einstellungen/benutzer-attribute/ -->

# Properties (custom field definitions)

API wrapper for the five property definition endpoints `/article-properties`, `/client-properties`, `/supplier-properties`, `/incoming-properties` and `/user-properties`.

## Access

```php
$billomat->articles->properties   // definitions of article custom fields
$billomat->clients->properties    // definitions of client custom fields
$billomat->suppliers->properties  // definitions of supplier custom fields
$billomat->incomings->properties  // definitions of incoming invoice custom fields
$billomat->users->properties      // definitions of user custom fields
```

All five are instances of the generic `Justpilot\Billomat\Api\PropertiesApi`.

## Model

A *property* is a custom field on one of the five main resources. These APIs manage only the **definitions**: name, type and optional default value of such a field.

The actual **values** on a record are managed through separate sub-resources, with one exception for users:

- Values on an article: `$billomat->articles->propertyValues` (see [Articles](articles.md))
- Values on a client: `$billomat->clients->propertyValues` (see [Clients](clients.md))
- Values on a supplier: `$billomat->suppliers->propertyValues` (see [Suppliers](suppliers.md))
- Values on an incoming invoice: `$billomat->incomings->propertyValues` (see [Incomings](incomings.md))
- Values on a user: **no dedicated values endpoint**; Billomat stores user property values directly on the user record.

The five property definition APIs are structurally identical (same verbs, same payload structure). They differ only in the endpoint path and the read model. The write class `PropertyCreateOptions` is shared by all five.

## Endpoint overview

All five APIs offer the same verbs with the same signatures. Path and read model vary:

| Access | Endpoint base | Read model |
|---|---|---|
| `$billomat->articles->properties` | `/article-properties` | `ArticleProperty` |
| `$billomat->clients->properties` | `/client-properties` | `ClientProperty` |
| `$billomat->suppliers->properties` | `/supplier-properties` | `SupplierProperty` |
| `$billomat->incomings->properties` | `/incoming-properties` | `IncomingProperty` |
| `$billomat->users->properties` | `/user-properties` | `UserProperty` |

### Verbs (for each of the five APIs)

| Method | HTTP | Path |
|---|---|---|
| `list($filters?)` | GET | `/{base}` |
| `listPage($filters?)` | GET | `/{base}` (one page with metadata) |
| `iterateAll($filters?, $pageSize?)` | GET | `/{base}` (all pages) |
| `get($id)` / `getOrFail($id)` | GET | `/{base}/{id}` |
| `create($options)` | POST | `/{base}` |
| `update($id, $options)` | PUT | `/{base}/{id}` |
| `delete($id)` | DELETE | `/{base}/{id}` |

## Methods

The examples mostly use the article properties; the other four behave exactly the same.

### `list(SubResourceFilter|array $filters = []): list<TProperty>`

Lists all property definitions. `SubResourceFilter` sorts and paginates (`orderBy`, `page`, `perPage`, `extra`); a raw array is passed through 1:1 as query parameters (array values are encoded as `key[]=…`).

```php
use Justpilot\Billomat\Api\OrderBy;
use Justpilot\Billomat\Api\SubResourceFilter;

$all = $billomat->articles->properties->list();

$paged = $billomat->clients->properties->list(new SubResourceFilter(
    orderBy: OrderBy::asc('position'),
    perPage: 100,
));
```

`listPage(SubResourceFilter|array $filters = [])` additionally returns the pagination metadata; `iterateAll(SubResourceFilter|array $filters = [], int $pageSize = 100)` walks through all pages lazily. See [Pagination](../advanced/pagination.md).

### `get(int $id): ?TProperty`

Returns `null` on 404. `getOrFail(int $id)` throws a `NotFoundException` instead.

```php
$prop = $billomat->suppliers->properties->get(42);
if (null === $prop) {
    // the property doesn't exist (anymore)
}
```

### `create(PropertyCreateOptions $options): TProperty`

Creates a property definition. Only `name` is required.

```php
use Justpilot\Billomat\Api\PropertyCreateOptions;
use Justpilot\Billomat\Model\Enum\PropertyType;

$opts = new PropertyCreateOptions(
    name: 'Lead time',
    type: PropertyType::TEXTFIELD,
    defaultValue: '3 business days',
    position: 10,
);

$prop = $billomat->articles->properties->create($opts);
```

### `update(int $id, PropertyCreateOptions $options): TProperty`

Updates an existing definition. The options class is the same as for `create()`: set fields are overwritten, unset (`null`) fields stay unchanged.

```php
$opts = new PropertyCreateOptions(name: 'Lead time', defaultValue: '5 business days');

$billomat->articles->properties->update(42, $opts);
```

### `delete(int $id): void`

Deletes the definition. Existing values on records become invalid as well.

```php
$billomat->incomings->properties->delete(42);
```

## Write model: `PropertyCreateOptions`

Shared options class for all five APIs, used for both `create()` and `update()`. Constructor: `new PropertyCreateOptions(string $name, …)`. `name` is required and the first parameter; all other fields are named arguments. The object is `final readonly`; `with*()` methods (e.g. `$opts->withName('New')`) return a modified copy.

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `name` | `name` | `string` | Required, constructor argument |
| `type` | `type` | `?PropertyType` | `TEXTFIELD`, `TEXTAREA`, `CHECKBOX` |
| `defaultValue` | `default_value` | `?string` | Default value for new records |
| `position` | `position` | `?int` | Sort order in the Billomat UI |
| `isNvl` | `is_nvl` | `?bool` | Use the default value when no other value is set (sent as `0`/`1`) |

`toArray()` removes `null` fields via `array_filter`. `name` is always sent.

## Read models: `ArticleProperty`, `ClientProperty`, `SupplierProperty`, `IncomingProperty`, `UserProperty`

All five are `final readonly` classes with the same structure:

| Property | Type |
|---|---|
| `id` | `int` |
| `name` | `string` |
| `type` | `?PropertyType` |
| `defaultValue` | `?string` |
| `isNvl` | `?bool` |
| `customfield` | `?string` (free-form extra field, see [Custom meta attributes](../concepts/custom-meta-attributes.md)) |
| `extra` | `array<string, mixed>` (unmapped fields, see [Models](../concepts/models.md)) |

## Enums used

- [`PropertyType`](../../src/Model/Enum/PropertyType.php): `TEXTFIELD`, `TEXTAREA`, `CHECKBOX`. Wire values are upper case (`TEXTFIELD`, not `textfield`).

<!-- json-examples:start (generated by tools/generate-json-examples.php, run `composer docs:json`) -->
## JSON on the wire

Billomat's API documentation only shows XML. These are the JSON bodies the SDK sends and receives: requests as the SDK serializes them, responses recorded in the Billomat sandbox and anonymized ([contract fixtures](../../tests/Fixtures/Contract)). Billomat sends every value as a string; the read models cast them (see [Read models](../concepts/models.md)).

::: code-group
<<< @/snippets/json/client-properties.create.json [POST /client-properties]
<<< @/../tests/Fixtures/Contract/client-properties/get.json [GET /client-properties/{id} (response)]
<<< @/../tests/Fixtures/Contract/client-properties/list.json [GET /client-properties (response)]
:::
<!-- json-examples:end -->

## Pitfalls

- **Don't confuse definitions and values.** `$billomat->articles->properties` only defines the schema ("there is a field called *Lead time*"). To assign the value `5 business days` to a specific article, use `$billomat->articles->propertyValues`.
- **One options class for all five APIs.** `PropertyCreateOptions` lives in the `Justpilot\Billomat\Api` namespace and is reused. Don't duplicate it per resource when refactoring.
- **User properties have no values endpoint.** Unlike the other four parents, Billomat stores user property values directly on the user record, not through a separate sub-resource.
- **`type` is optional.** If `type` is omitted, Billomat treats the field as free text. For `CHECKBOX` fields, `defaultValue` is a string; typical wire values are `"0"` and `"1"`.
- **Billomat doesn't return `position` on read.** You can set the field via `PropertyCreateOptions`, but it's missing from the read models; it's not a uniqueness constraint.
- **Deleting is destructive.** Deleting a definition also loses the values stored for it on records. There's no API way to "just hide" a field; your application has to model that itself.
- **`update()` takes the same class as `create()`.** There's no separate `PropertyUpdateOptions` type. A PUT without `type` keeps the previous type.

## End-to-end example

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\PropertyCreateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\Enum\PropertyType;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

// 1) Create a custom field definition for articles
$opts = new PropertyCreateOptions(
    name: 'Lead time',
    type: PropertyType::TEXTFIELD,
    defaultValue: '3 business days',
    position: 10,
);

$leadTimeDef = $billomat->articles->properties->create($opts);
printf("Definition #%d created: %s\n", $leadTimeDef->id, $leadTimeDef->name);

// 2) Inspect the existing definitions
foreach ($billomat->articles->properties->list(['per_page' => 100]) as $def) {
    printf(
        "#%d %-20s [%s] default=%s\n",
        $def->id,
        $def->name,
        $def->type?->value ?? 'FREE',
        $def->defaultValue ?? '-',
    );
}

// 3) Raise the default value later
$billomat->articles->properties->update(
    $leadTimeDef->id,
    $opts->withDefaultValue('5 business days'),
);

// 4) Parallel definition for clients: same API shape, different resource
$vipFlag = $billomat->clients->properties->create(
    new PropertyCreateOptions(name: 'VIP', type: PropertyType::CHECKBOX, defaultValue: '0'),
);

// 5) Values (not definitions!) are handled by a different API:
//    $billomat->articles->propertyValues->create(...)
//    $billomat->clients->propertyValues->create(...)
```
