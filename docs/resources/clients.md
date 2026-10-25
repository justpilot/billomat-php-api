<!-- Source: https://www.billomat.com/api/kunden/ -->

# Clients (Kunden)

API wrapper for the Billomat "clients" resource, covering the endpoints under `/clients`.

## Access

```php
$billomat->clients
$billomat->clients->tags            // tags on a client, see client-tags.md
$billomat->clients->properties      // custom property definitions, see properties.md
$billomat->clients->propertyValues  // custom property values on a client
```

`Justpilot\Billomat\Api\ClientsApi`, created lazily by `BillomatClient`.

## Endpoint overview

| Method | HTTP | Path |
|---|---|---|
| `list($filters?)` | GET | `/clients` |
| `listPage($filters?)` | GET | `/clients` (one page with metadata) |
| `iterateAll($filters?, $pageSize?)` | GET | `/clients` (all pages) |
| `get($id)` / `getOrFail($id)` | GET | `/clients/{id}` |
| `getMyself()` | GET | `/clients/myself` |
| `create($options)` | POST | `/clients` |
| `update($id, $options)` | PUT | `/clients/{id}` |
| `delete($id)` | DELETE | `/clients/{id}` |
| `avatar($id, $size?)` | GET | `/clients/{id}/avatar` |

## Methods

### `list(ClientFilter|array $filters = []): list<Client>`

Lists clients, optionally filtered.

```php
public function list(ClientFilter|array $filters = []): array
```

Use the typed filter `ClientFilter` (every field is an optional named argument, plus a `with*()` wither per field). A plain array of Billomat parameters still works, e.g. for parameters the filter doesn't cover; array values are serialized as `key[]=…`.

```php
use Justpilot\Billomat\Api\ClientFilter;
use Justpilot\Billomat\Api\OrderBy;

$clients = $billomat->clients->list(new ClientFilter(
    orderBy: OrderBy::asc('name'),
    perPage: 50,
));

foreach ($clients as $client) {
    echo $client->id . ': ' . $client->name . PHP_EOL;
}
```

| Billomat filter | `ClientFilter` field |
|---|---|
| `name` | `name` |
| `client_number` | `clientNumber` |
| `email` | `email` |
| `first_name` / `last_name` | `firstName` / `lastName` |
| `country_code` | `countryCode` |
| `note` | `note` |
| `invoice_id` | `invoiceId` (list, sent comma-separated) |
| `tags` | `tags` (list, sent comma-separated) |
| `order_by` | `orderBy` (`OrderBy::asc('name')->thenDesc('id')`) |
| `page` / `per_page` | `page` / `perPage` |

Other query parameters (e.g. filters on custom properties) go into `extra: [...]`; they override fields with the same name.

**Exceptions:** `AuthenticationException`, `HttpException`.

### `listPage(ClientFilter|array $filters = []): Page<Client>`

Same as `list()`, but also returns the pagination metadata (`@page`, `@per_page`, `@total`) from the response envelope. See [Pagination](../advanced/pagination.md).

### `iterateAll(ClientFilter|array $filters = [], int $pageSize = 100): Generator<int, Client>`

Lazily walks through all pages and yields one client at a time. `page` and `per_page` in the filter are overwritten.

```php
foreach ($billomat->clients->iterateAll(new ClientFilter(countryCode: 'DE')) as $client) {
    // …
}
```

### `get(int $id): ?Client`

Fetches a client by ID. Returns `null` when Billomat responds with 404.

```php
$client = $billomat->clients->get(12345);

if ($client === null) {
    throw new RuntimeException('Client 12345 does not exist.');
}
```

**Exceptions:** `AuthenticationException`, `HttpException` (no `NotFoundException`).

### `getOrFail(int $id): Client`

Like `get()`, but throws `NotFoundException` instead of returning `null`.

### `create(ClientCreateOptions $options): Client`

Creates a client. The payload is sent wrapped as `{ "client": { … } }`.

```php
use Justpilot\Billomat\Api\ClientCreateOptions;

$opts = new ClientCreateOptions(
    name: 'Acme GmbH',
    firstName: 'Max',
    lastName: 'Mustermann',
    email: 'max@acme.example',
    countryCode: 'DE',
);

$client = $billomat->clients->create($opts);
echo $client->id;
```

**Exceptions:** `ValidationException` (required field missing, invalid value), `AuthenticationException`, `HttpException`.

### `update(int $id, ClientUpdateOptions $options): Client`

Partial update of an existing client. Only fields set in `ClientUpdateOptions` are sent to Billomat; all other fields stay unchanged.

Billomat restriction: an archived client can't be updated. Set `archived: false` first if necessary.

```php
use Justpilot\Billomat\Api\ClientUpdateOptions;

$opts = new ClientUpdateOptions(name: 'Acme GmbH & Co. KG', email: 'invoices@acme.example');

$updated = $billomat->clients->update(12345, $opts);
```

**Exceptions:** `ValidationException`, `AuthenticationException`, `NotFoundException`, `HttpException`.

### `delete(int $id): void`

Deletes a client. Clients that already have documents (invoices, offers, …) can't be deleted, only archived; Billomat then responds with 400/422 and the SDK throws a `ValidationException`.

### `getMyself(): Client`

Returns your own account profile as a `Client`. Useful for health checks ("are my credentials valid?") and for reading your own master data.

```php
$me = $billomat->clients->getMyself();
echo 'Logged in as: ' . $me->name;
```

**Exceptions:** `AuthenticationException`, `HttpException`.

### `avatar(int $id, ?int $size = null): string`

Downloads the client's avatar. Billomat returns the image itself (usually PNG), not JSON; the method returns the raw body. `$size` is the optional edge length in pixels.

```php
file_put_contents('/tmp/avatar.png', $billomat->clients->avatar(12345, size: 128));
```

## Write model: `ClientCreateOptions`

Every field is a constructor parameter with a default and is passed as a named argument. The object is immutable (`final readonly`); each field has a `with*()` method that returns a modified copy (e.g. `$opts->withName('New')`). `toArray()` drops `null` values so Billomat can fill in defaults from the account settings.

### Master data

| Property | Billomat field | Type | Description |
|---|---|---|---|
| `name` | `name` | `?string` | Company name (required for companies according to the Billomat docs). |
| `archived` | `archived` | `?bool` | `true` = archived, `false` = active. |
| `numberPre` | `number_pre` | `?string` | Client number prefix (default from settings). |
| `number` | `number` | `?int` | Sequential client number (default: next free number). |
| `numberLength` | `number_length` | `?int` | Minimum length of the client number. |
| `clientNumber` | `client_number` | `?string` | Free-form client number. |

### Address

| Property | Billomat field | Type |
|---|---|---|
| `street` | `street` | `?string` |
| `zip` | `zip` | `?string` |
| `city` | `city` | `?string` |
| `state` | `state` | `?string` |
| `countryCode` | `country_code` | `?string` (ISO 3166-1 alpha-2, e.g. `DE`, `AT`, `CH`) |

### Contact person and contact details

| Property | Billomat field | Type |
|---|---|---|
| `firstName` | `first_name` | `?string` |
| `lastName` | `last_name` | `?string` |
| `salutation` | `salutation` | `?string` |
| `email` | `email` | `?string` |
| `phone` | `phone` | `?string` |
| `fax` | `fax` | `?string` |
| `mobile` | `mobile` | `?string` |
| `www` | `www` | `?string` (URL without scheme) |
| `note` | `note` | `?string` |
| `locale` | `locale` | `?string` (e.g. `de_DE`) |

### Tax and price basis

| Property | Billomat field | Type |
|---|---|---|
| `taxNumber` | `tax_number` | `?string` |
| `vatNumber` | `vat_number` | `?string` |
| `taxRule` | `tax_rule` | `?TaxRule` (`TAX`, `NO_TAX`, `COUNTRY`) |
| `netGross` | `net_gross` | `?NetGross` (`NET`, `GROSS`, `SETTINGS`) |
| `currencyCode` | `currency_code` | `?string` (ISO currency code) |

### Debtor account and price group

| Property | Billomat field | Type |
|---|---|---|
| `debitorAccountNumber` | `debitor_account_number` | `?int` |
| `priceGroup` | `price_group` | `?int` |

### Bank details

| Property | Billomat field | Type |
|---|---|---|
| `bankAccountNumber` | `bank_account_number` | `?string` |
| `bankAccountOwner` | `bank_account_owner` | `?string` |
| `bankNumber` | `bank_number` | `?string` |
| `bankName` | `bank_name` | `?string` |
| `bankSwift` | `bank_swift` | `?string` |
| `bankIban` | `bank_iban` | `?string` |

### SEPA

| Property | Billomat field | Type |
|---|---|---|
| `sepaMandate` | `sepa_mandate` | `?string` |
| `sepaMandateDate` | `sepa_mandate_date` | `?string` (`YYYY-MM-DD`) |

### Payment terms

| Property | Billomat field | Type |
|---|---|---|
| `defaultPaymentTypes` | `default_payment_types` | `list<PaymentType>` (sent comma-separated, e.g. `CASH,BANK_TRANSFER`) |
| `reduction` | `reduction` | `?float` (discount in %) |
| `discountRateType` | `discount_rate_type` | `?ValueType` (`SETTINGS`, `ABSOLUTE`, `RELATIVE`) |
| `discountRate` | `discount_rate` | `?float` |
| `discountDaysType` | `discount_days_type` | `?ValueType` |
| `discountDays` | `discount_days` | `?float` |
| `dueDaysType` | `due_days_type` | `?ValueType` |
| `dueDays` | `due_days` | `?int` |
| `reminderDueDaysType` | `reminder_due_days_type` | `?ValueType` |
| `reminderDueDays` | `reminder_due_days` | `?int` |
| `offerValidityDaysType` | `offer_validity_days_type` | `?ValueType` |
| `offerValidityDays` | `offer_validity_days` | `?int` |
| `dunningRun` | `dunning_run` | `?bool` (only sent when set; otherwise Billomat's default applies) |

```php
use Justpilot\Billomat\Model\Enum\PaymentType;
use Justpilot\Billomat\Model\Enum\TaxRule;
use Justpilot\Billomat\Model\Enum\ValueType;

$opts = new ClientCreateOptions(
    name: 'Acme GmbH',
    taxRule: TaxRule::COUNTRY,
    dueDaysType: ValueType::ABSOLUTE,
    dueDays: 14,
    defaultPaymentTypes: [PaymentType::BANK_TRANSFER, PaymentType::PAYPAL],
);
```

## Write model: `ClientUpdateOptions`

Same fields as `ClientCreateOptions`, all optional. Only fields that are not `null` go into the PUT request. `Clear::Field` (`Justpilot\Billomat\Api\Clear`) sends a field as JSON `null` so Billomat empties it, e.g. `new ClientUpdateOptions(note: Clear::Field)`. The boolean `archived` is serialized as `1`/`0`.

| Property | Billomat field | Type |
|---|---|---|
| all fields of `ClientCreateOptions` | same | same type, additionally `\|Clear` |
| `defaultPaymentTypes` | `default_payment_types` | `list<PaymentType>\|Clear` (empty list = don't send) |

## Read model: `Client`

`final readonly class Client`. Properties:

| Property | Type | Notes |
|---|---|---|
| `id` | `?int` | Billomat's internal ID. Nullable because a freshly constructed object might not have one. |
| `name` | `string` | Required, not nullable. |
| `created`, `updated` | `?\DateTimeImmutable` | |
| `clientNumber` | `?string` | |
| `number`, `numberLength` | `?int` | |
| `numberPre` | `?string` | |
| `street`, `zip`, `city`, `state`, `countryCode` | `?string` | |
| `address` | `?string` | Full address assembled by Billomat (read-only) |
| `firstName`, `lastName`, `salutation` | `?string` | |
| `email`, `phone`, `fax`, `mobile`, `www` | `?string` | |
| `note`, `locale` | `?string` | |
| `taxNumber`, `vatNumber` | `?string` | |
| `taxRule` | `?TaxRule` | |
| `netGross` | `?NetGross` | |
| `currencyCode` | `?string` | |
| `debitorAccountNumber`, `priceGroup` | `?int` | |
| `archived`, `dunningRun`, `enableCustomerportal`, `digExclude` | `?bool` | |
| `reduction`, `discountRate`, `discountDays` | `?float` | |
| `dueDays`, `reminderDueDays`, `offerValidityDays` | `?int` | |
| `discountRateType`, `discountDaysType`, `dueDaysType`, `reminderDueDaysType`, `offerValidityDaysType` | `?ValueType` | |
| `bankAccountOwner`, `bankNumber`, `bankName`, `bankAccountNumber`, `bankSwift`, `bankIban` | `?string` | |
| `sepaMandate` | `?string` | |
| `sepaMandateDate` | `?\DateTimeImmutable` | |
| `defaultPaymentTypes` | `list<PaymentType>` | |
| `revenueGross`, `revenueNet` | `?Money` | Revenue with this client (read-only), see [Money](../concepts/money.md) |
| `facturxIdentifier`, `sellerIdentifier` | `?string` | E-invoicing (ZUGFeRD/Factur-X), see Pitfalls |
| `customfield` | `?string` | Free-form extra field, see [Custom meta attributes](../concepts/custom-meta-attributes.md) |
| `extra` | `array<string, mixed>` | Fields Billomat returns that the SDK doesn't map (yet), see [Models](../concepts/models.md) |

`Client::fromArray(array $data): self` hydrates a model from the `client` node of the API response. `toArray()` renders the model back with Billomat field names (snake_case). It's meant for debugging and logging, **not** for update calls.

## Client property values

Stores the values for the client properties defined in [`properties.md`](properties.md) (`$billomat->clients->properties`). A property definition (`ClientProperty`) is the schema (name + `PropertyType`); a `ClientPropertyValue` is the actual value on one client.

| Method | HTTP | Path |
|---|---|---|
| `list($clientId?, $filters?)` | GET | `/client-property-values` |
| `listPage($clientId?, $filters?)` | GET | `/client-property-values` |
| `iterateAll($clientId?, $filters?, $pageSize?)` | GET | `/client-property-values` (all pages) |
| `get($id)` / `getOrFail($id)` | GET | `/client-property-values/{id}` |
| `create($options)` | POST | `/client-property-values` |

```php
use Justpilot\Billomat\Api\ClientPropertyValueCreateOptions;

// List the values of one client
$values = $billomat->clients->propertyValues->list($client->id);

// Set the value of the property "Industry" (id = 12)
$value = $billomat->clients->propertyValues->create(
    new ClientPropertyValueCreateOptions(
        clientId: $client->id,
        clientPropertyId: 12,
        value: 'Logistics',
    ),
);
```

Calling `create()` again with the same `clientPropertyId` overwrites the existing value. There is no `update()` or `delete()`.

### Write model: `ClientPropertyValueCreateOptions`

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `clientId` | `client_id` | `int` (required) | — |
| `clientPropertyId` | `client_property_id` | `int` (required) | ID of the property definition |
| `value` | `value` | `mixed` (required) | `CHECKBOX` expects `0`/`1` |

### Read model: `ClientPropertyValue`

| Property | Type |
|---|---|
| `id` | `?int` |
| `clientId` | `int` |
| `clientPropertyId` | `int` |
| `type` | `?PropertyType` (`TEXTFIELD`, `TEXTAREA`, `CHECKBOX`) |
| `name` | `?string` (name from the property definition) |
| `value` | `mixed` |
| `created`, `updated` | `?\DateTimeImmutable` |
| `customfield` | `?string` (free-form extra field, see [Custom meta attributes](../concepts/custom-meta-attributes.md)) |
| `extra` | `array<string, mixed>` |

## Enums used

- [`TaxRule`](../../src/Model/Enum/TaxRule.php): `TAX`, `NO_TAX`, `COUNTRY` (`taxRule`)
- [`NetGross`](../../src/Model/Enum/NetGross.php): `NET`, `GROSS`, `SETTINGS` (`netGross`)
- [`ValueType`](../../src/Model/Enum/ValueType.php): `SETTINGS`, `ABSOLUTE`, `RELATIVE` (the `*Type` fields)
- [`PaymentType`](../../src/Model/Enum/PaymentType.php): `INVOICE_CORRECTION`, `CREDIT_NOTE`, `BANK_CARD`, `BANK_TRANSFER`, `DEBIT`, `CASH`, `CHECK`, `PAYPAL`, `CREDIT_CARD`, `COUPON`, `MISC` (`defaultPaymentTypes`)
- [`PropertyType`](../../src/Model/Enum/PropertyType.php): `TEXTFIELD`, `TEXTAREA`, `CHECKBOX` (`ClientPropertyValue::$type`)

## Pitfalls

- **E-invoicing (undocumented).** Billomat returns `facturx_identifier` and `seller_identifier` in API responses but doesn't describe them in the API docs. The SDK only reads them (types derived from observed values); writing isn't supported for lack of a specification.
- **Single-item list quirk.** When a list contains only one client, Billomat sometimes sends it as an object instead of a list. `ClientsApi::list()` normalizes this, so you always get a `list<Client>`.
- **Archived clients can't be updated.** Send `archived: false` in a first `ClientUpdateOptions` request, then the remaining changes in a second one.
- **`number_length` shorter than the actual length.** If `number` already has more digits, Billomat's validation fails and you get a `ValidationException`.
- **Clients with documents can't be deleted.** `delete()` throws a `ValidationException`; archive them via `update()` instead.

## End-to-end example

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\ClientCreateOptions;
use Justpilot\Billomat\Api\ClientFilter;
use Justpilot\Billomat\Api\ClientUpdateOptions;
use Justpilot\Billomat\Api\OrderBy;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Exception\ValidationException;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

// Create
$create = new ClientCreateOptions(name: 'Example GmbH', countryCode: 'DE', email: 'invoices@example.de');

try {
    $client = $billomat->clients->create($create);
} catch (ValidationException $e) {
    fwrite(STDERR, 'Creation rejected: ' . $e->getResponseBody() . PHP_EOL);
    exit(1);
}

printf("Created: #%d %s\n", $client->id, $client->name);

// Read
$reloaded = $billomat->clients->getOrFail($client->id);
var_dump($reloaded->email);

// Update
$update = new ClientUpdateOptions(note: 'First contact: ' . date('Y-m-d'));
$billomat->clients->update($client->id, $update);

// List (e.g. the most recently created)
$recent = $billomat->clients->list(new ClientFilter(
    orderBy: OrderBy::desc('id'),
    perPage: 5,
));
foreach ($recent as $c) {
    printf("- #%d %s\n", $c->id, $c->name);
}
```
