<!-- Source: SDK overview — no 1:1 Billomat URL; see the sub-resource pages (countries.md, currencies.md, settings-units.md, settings-email-templates.md, settings-free-texts.md, settings-reminder-levels.md, settings-roles.md, settings-tax-free-countries.md, incoming-categories.md, users.md). -->

# Lookups (Stammdaten & settings)

API wrappers for the ten read-only lookup endpoints: master data and account settings that other resources refer to.

## Access

```php
$billomat->countries           // ISO country list
$billomat->currencies          // currency list
$billomat->units               // units (Stück, Stunde, …)
$billomat->users               // users of the account
$billomat->roles               // roles with their permission map
$billomat->emailTemplates      // e-mail templates
$billomat->freeTexts           // free text snippets
$billomat->reminderTexts       // reminder (dunning) text blocks
$billomat->countryTaxes        // tax-free countries (ISO 3166)
$billomat->incomingCategories  // incoming invoice categories (string ID)
```

## Model

All ten APIs are read-only: none of them has `create`/`update`/`delete`. `countries` and `currencies` only offer `list()`/`listPage()`/`iterateAll()`; the others also have `get($id)`, which returns `null` on 404, and `getOrFail($id)`, which throws `NotFoundException` instead. `UsersApi` additionally has `getMyself()` for the currently authenticated user. For `incomingCategories`, `$id` is a **string** slug (e.g. `goods`), not a number.

The only writable part is the sub-resource `$billomat->users->properties` (custom field definitions for users, see [properties.md](properties.md)).

Each of these resources provides master data that other models refer to only by ID or code — e.g. `Recurring::$emailTemplateId`, `Invoice::$currencyCode`, `InvoiceItem::$unit`.

## Endpoints

| API | HTTP | Path | Methods |
|---|---|---|---|
| `countries` | GET | `/countries` | `list($filters?)`, `listPage($filters?)`, `iterateAll()` |
| `currencies` | GET | `/currencies` | `list($filters?)`, `listPage($filters?)`, `iterateAll()` |
| `units` | GET | `/units`, `/units/{id}` | `list($filters?)`, `listPage()`, `iterateAll()`, `get($id)`, `getOrFail($id)` |
| `users` | GET | `/users`, `/users/{id}`, `/users/myself` | `list($filters?)`, `listPage()`, `iterateAll()`, `get($id)`, `getOrFail($id)`, `getMyself()` |
| `emailTemplates` | GET | `/email-templates`, `/email-templates/{id}` | `list($filters?)`, `listPage()`, `iterateAll()`, `get($id)`, `getOrFail($id)` |
| `freeTexts` | GET | `/free-texts`, `/free-texts/{id}` | `list($filters?)`, `listPage()`, `iterateAll()`, `get($id)`, `getOrFail($id)` |
| `reminderTexts` | GET | `/reminder-texts`, `/reminder-texts/{id}` | `list($filters?)`, `listPage()`, `iterateAll()`, `get($id)`, `getOrFail($id)` |
| `roles` | GET | `/roles`, `/roles/{id}` | `list($filters?)`, `listPage()`, `iterateAll()`, `get($id)`, `getOrFail($id)` |
| `countryTaxes` | GET | `/country-taxes`, `/country-taxes/{id}` | `list($filters?)`, `listPage()`, `iterateAll()`, `get($id)`, `getOrFail($id)` |
| `incomingCategories` | GET | `/incoming-categories`, `/incoming-categories/{id}` | `list($filters?)`, `listPage()`, `iterateAll()`, `get($id)`, `getOrFail($id)` |

All generated read models below also have an `extra` array with the fields the SDK doesn't map yet (see [Read models](../concepts/models.md)); it is left out of the tables.

## Methods

### `countries`

Endpoint: `/countries`. No `get()`. Details: [countries.md](countries.md).

```php
$countries = $billomat->countries->list();

$de = array_find($countries, static fn ($c) => 'DE' === $c->code);
```

#### Read model: `Country`

`final readonly class Country`. Wire fields: `code` (or `country_code`), `name`, `id`.

| Property | Type |
|---|---|
| `code` | `string` |
| `name` | `?string` |
| `id` | `?int` |

### `currencies`

Endpoint: `/currencies`. No `get()`. Details: [currencies.md](currencies.md).

```php
$currencies = $billomat->currencies->list();
```

#### Read model: `Currency`

`final readonly class Currency`. Wire fields: `code` (or `currency_code`), `name`, `symbol`, `id`.

| Property | Type |
|---|---|
| `code` | `string` |
| `name` | `?string` |
| `symbol` | `?string` |
| `id` | `?int` |

### `units`

Endpoint: `/units`. Details: [settings-units.md](settings-units.md).

```php
$units = $billomat->units->list();
$piece = $billomat->units->get(42);
```

#### Read model: `Unit`

`final readonly class Unit`.

| Property | Type |
|---|---|
| `id` | `int` |
| `name` | `string` |
| `created`, `updated` | `?\DateTimeImmutable` |
| `customfield` | `?string` (free extra field, see [Custom meta attributes](../concepts/custom-meta-attributes.md)) |

### `users`

Endpoint: `/users`. Also offers `getMyself()` for `/users/myself`. Details: [users.md](users.md).

```php
$me = $billomat->users->getMyself();
$colleague = $billomat->users->getOrFail(12345);
$all = $billomat->users->list();
```

#### Read model: `User`

`final readonly class User`.

| Property | Type |
|---|---|
| `id` | `int` |
| `email` | `?string` |
| `firstName` | `?string` |
| `lastName` | `?string` |
| `salutation` | `?string` |
| `roleId` | `?int` |
| `created`, `updated` | `?\DateTimeImmutable` |
| `customfield` | `?string` (free extra field, see [Custom meta attributes](../concepts/custom-meta-attributes.md)) |
| `locale` | `?string` |
| `timezone` | `?string` |
| `isAccountOwner` | `?bool` (account owner) |

### `emailTemplates`

Endpoint: `/email-templates`. Templates for sending documents by e-mail. Details: [settings-email-templates.md](settings-email-templates.md).

```php
use Justpilot\Billomat\Model\Enum\EmailTemplateType;

$default = array_find(
    $billomat->emailTemplates->list(),
    static fn ($t) => true === $t->isDefault && EmailTemplateType::INVOICES === $t->type,
);
```

#### Read model: `EmailTemplate`

`final readonly class EmailTemplate`. The message text comes in the wire field `text` and ends up in `body`. Billomat no longer returns `fromAddress` (wire field `from`); it is usually `null`.

| Property | Type |
|---|---|
| `id` | `int` |
| `name` | `?string` |
| `subject` | `?string` |
| `body` | `?string` |
| `fromAddress` | `?string` |
| `isDefault` | `?bool` |
| `type` | `?EmailTemplateType` |
| `bcc` | `?bool` |
| `created`, `updated` | `?\DateTimeImmutable` |

### `freeTexts`

Endpoint: `/free-texts`. Free text snippets that can be reused as `title`/`label`/`intro`/`note` on documents (see `freeTextId` on `InvoiceCreateOptions`, `OfferCreateOptions`, `RecurringCreateOptions`, …). Details: [settings-free-texts.md](settings-free-texts.md).

```php
$blocks = $billomat->freeTexts->list();
```

#### Read model: `FreeText`

`final readonly class FreeText`.

| Property | Type |
|---|---|
| `id` | `int` |
| `title` | `?string` |
| `label` | `?string` |
| `intro` | `?string` |
| `note` | `?string` |
| `created`, `updated` | `?\DateTimeImmutable` |
| `type` | `?FreeTextType` |
| `name` | `?string` |
| `isDefault` | `?bool` |

### `reminderTexts`

Endpoint: `/reminder-texts`. Text blocks for reminders, referenced via `Reminder::$reminderTextId` (see [Reminders](reminders.md)). Details: [settings-reminder-levels.md](settings-reminder-levels.md).

```php
use Justpilot\Billomat\Api\OrderBy;

$texts = $billomat->reminderTexts->list(['order_by' => OrderBy::asc('sorting')->toQuery()]);
```

#### Read model: `ReminderText`

`final readonly class ReminderText`.

| Property | Type |
|---|---|
| `id` | `int` |
| `name` | `?string` |
| `subject` | `?string` |
| `header` | `?string` |
| `footer` | `?string` |
| `sort` | `?int` |
| `chargeName` | `?string` |
| `chargeDescription` | `?string` |
| `chargeAmount` | `?Money` |
| `customfield` | `?string` (free extra field, see [Custom meta attributes](../concepts/custom-meta-attributes.md)) |

### `roles`

Endpoint: `/roles`. User roles with their access rights. Details: [settings-roles.md](settings-roles.md).

```php
use Justpilot\Billomat\Model\Enum\AccessLevel;

$master = $billomat->roles->get(1);
$canDeleteInvoices = ($master?->permissions['invoices'] ?? null) === AccessLevel::DELETE;
```

#### Read model: `Role`

`final readonly class Role`.

| Property | Type |
|---|---|
| `id` | `int` |
| `name` | `?string` |
| `permissions` | `array<string, AccessLevel>` |

### `countryTaxes`

Endpoint: `/country-taxes`. Country codes for which Billomat charges no VAT. Details: [settings-tax-free-countries.md](settings-tax-free-countries.md).

```php
$taxFree = array_map(static fn ($c) => $c->countryCode, $billomat->countryTaxes->list());
```

#### Read model: `CountryTax`

`final readonly class CountryTax`.

| Property | Type |
|---|---|
| `id` | `int` |
| `countryCode` | `?string` |

### `incomingCategories`

Endpoint: `/incoming-categories`. Predefined categories for incoming invoices; the ID is a **string** slug. Details: [incoming-categories.md](incoming-categories.md).

```php
$goods = $billomat->incomingCategories->get('goods');
```

#### Read model: `IncomingCategory`

`final readonly class IncomingCategory`.

| Property | Type |
|---|---|
| `id` | `string` |
| `title` | `?string` |
| `description` | `?string` |

## Enums

- [`EmailTemplateType`](../../src/Model/Enum/EmailTemplateType.php) for `EmailTemplate::$type`.
- [`FreeTextType`](../../src/Model/Enum/FreeTextType.php) for `FreeText::$type`.

Unknown values become `null`.

## Pitfalls

- **Everything is read-only.** None of these ten APIs has `create`/`update`/`delete`. To create a new user or e-mail template, use the Billomat web UI (the API has no write endpoint for users; for the others the SDK doesn't expose one).
- **`countries` and `currencies` have no `get()`.** To get a single country or currency, pass `code` as a filter (`list(['code' => 'DE'])`) or load the list and filter on the client side. The lists are small (~250 countries, ~170 currencies).
- **`code` is the key, not `id`.** `Country` and `Currency` have an optional `id`, but all references (`Client::$countryCode`, `Invoice::$currencyCode`) use the ISO code.
- **Billomat is inconsistent.** Some endpoints return `code`, others `country_code`/`currency_code`; the read models accept both. Don't build logic around which field comes back.
- **`from` is called `fromAddress` in the PHP model.** `toArray()` renders it as `from` again.
- **`User::$roleId`** refers to a role configured in the Billomat account; resolve it via [`$billomat->roles->get($roleId)`](settings-roles.md).
- **`getMyself()`** is the only way to identify the current user programmatically, useful for "created by" fields in audit logs.
- **Don't URL-encode filter strings yourself.** `order_by=sorting+ASC` is passed through correctly by the HTTP layer; Billomat rejects `%2B` instead of `+` (see `BillomatHttpClient::buildBillomatQuery`). Build sort values with `OrderBy` instead of writing them by hand.

## End-to-end example

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\OrderBy;
use Justpilot\Billomat\BillomatClient;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

// 1) Who am I?
$me = $billomat->users->getMyself();
printf("Logged in as %s %s <%s>\n", $me?->firstName, $me?->lastName, $me?->email);

// 2) Cache master data
$countriesByCode = [];
foreach ($billomat->countries->list() as $country) {
    $countriesByCode[$country->code] = $country;
}

$currenciesByCode = [];
foreach ($billomat->currencies->list() as $currency) {
    $currenciesByCode[$currency->code] = $currency;
}

$unitsById = [];
foreach ($billomat->units->list() as $unit) {
    if (null !== $unit->id) {
        $unitsById[$unit->id] = $unit;
    }
}

printf(
    "Master data: %d countries, %d currencies, %d units\n",
    \count($countriesByCode),
    \count($currenciesByCode),
    \count($unitsById),
);

// 3) Find the default e-mail template
$default = array_find(
    $billomat->emailTemplates->list(),
    static fn ($template) => true === $template->isDefault,
);
if (null !== $default) {
    printf("Default template: #%d %s\n", $default->id, $default->name);
}

// 4) Load reminder levels for reporting
foreach ($billomat->reminderTexts->list(['order_by' => OrderBy::asc('sorting')->toQuery()]) as $level) {
    printf(
        "Level %d: %s — %s EUR reminder fee\n",
        $level->sort ?? 0,
        $level->name ?? '-',
        $level->chargeAmount?->format(2) ?? '0.00',
    );
}

// 5) Search free text snippets for an invoice template
$agbBlock = array_find(
    $billomat->freeTexts->list(),
    static fn ($block) => str_contains((string) $block->title, 'AGB'),
);
// $agbBlock->id can now be used as InvoiceCreateOptions::$freeTextId.
```
