<!-- Source: https://www.billomat.com/api/abo-rechnungen/ -->

# Recurrings (Abo-Rechnungen)

API wrapper for recurring invoices at `/recurrings` and their three sub-resources (`/recurring-items`, `/recurring-tags`, `/recurring-email-receivers`).

## Access

```php
$billomat->recurrings                 // recurring invoices
$billomat->recurrings->items          // items of a recurring invoice
$billomat->recurrings->tags           // tags
$billomat->recurrings->emailReceivers // email recipients (TO/CC/BCC)
```

## Model

A recurring invoice is a template for repeating invoices: what is billed to whom, at what interval, and what happens on each run?

- **Interval**: `cycle` + `cycleNumber` (e.g. `MONTHLY` × `2` → every two months) plus `startDate`, `endDate` and optionally `iterations`.
- **Action**: `action` defines what Billomat does on each run, from "only create a draft" to "create, complete and send by email".
- **Content**: items are maintained separately as `RecurringItem`s.

Over time, a recurring invoice produces regular invoices; they reference their recurring invoice via `Invoice::$recurringId` (see [Invoices](invoices.md)).

## Endpoints

### `/recurrings`

| Method | HTTP | Path |
|---|---|---|
| `list($filters?)` | GET | `/recurrings` |
| `listPage($filters?)` | GET | `/recurrings` (one page with metadata) |
| `iterateAll($filters?, $pageSize?)` | GET | `/recurrings` (lazy, across all pages) |
| `get($id)` / `getOrFail($id)` | GET | `/recurrings/{id}` |
| `create($options)` | POST | `/recurrings` |
| `update($id, $options)` | PUT | `/recurrings/{id}` |
| `delete($id)` | DELETE | `/recurrings/{id}` |
| `stop($id)` | PUT | `/recurrings/{id}/stop` |

### `/recurring-items`

| Method | HTTP | Path |
|---|---|---|
| `list($recurringId, $filters?)` | GET | `/recurring-items?recurring_id={id}` |
| `listPage($recurringId, $filters?)` / `iterateAll($recurringId, $filters?, $pageSize?)` | GET | `/recurring-items?recurring_id={id}` (paginated) |
| `get($id)` / `getOrFail($id)` | GET | `/recurring-items/{id}` |
| `create($recurringId, $options)` | POST | `/recurring-items` |
| `update($id, $options)` | PUT | `/recurring-items/{id}` |
| `delete($id)` | DELETE | `/recurring-items/{id}` |

### `/recurring-tags`

| Method | HTTP | Path |
|---|---|---|
| `list($recurringId, $filters?)` | GET | `/recurring-tags?recurring_id={id}` |
| `listPage($recurringId, $filters?)` / `iterateAll($recurringId, $filters?, $pageSize?)` | GET | `/recurring-tags?recurring_id={id}` (paginated) |
| `cloud()` | GET | `/recurring-tags` |
| `get($id)` / `getOrFail($id)` | GET | `/recurring-tags/{id}` |
| `create($recurringId, $options)` | POST | `/recurring-tags` |
| `delete($id)` | DELETE | `/recurring-tags/{id}` |

### `/recurring-email-receivers`

| Method | HTTP | Path |
|---|---|---|
| `list($recurringId, $filters?)` | GET | `/recurring-email-receivers?recurring_id={id}` |
| `listPage($recurringId, $filters?)` / `iterateAll($recurringId, $filters?, $pageSize?)` | GET | `/recurring-email-receivers?recurring_id={id}` (paginated) |
| `get($id)` / `getOrFail($id)` | GET | `/recurring-email-receivers/{id}` |
| `create($recurringId, $options)` | POST | `/recurring-email-receivers` |
| `update($id, $options)` | PUT | `/recurring-email-receivers/{id}` |
| `delete($id)` | DELETE | `/recurring-email-receivers/{id}` |

## Recurrings

### `list(RecurringFilter|array $filters = []): list<Recurring>`

Filters according to the Billomat docs: `client_id`, `contact_id`, `name`, `payment_type`, `cycle_number`, `cycle`, `label`, `intro`, `note`, `tags`, `article_id`.

The typed `RecurringFilter` is the recommended way to filter. Its camelCase fields are `clientId`, `contactId`, `name`, `paymentType` (`list<PaymentType>`), `cycle` (`RecurringCycle`), `label`, `intro`, `note` and `tags`; multi-value fields are lists and are sent comma-separated. On top come `orderBy`, `page`, `perPage` and `extra` for further query parameters (e.g. `cycle_number`, `article_id`). A plain array still works; array values in it are encoded as `key[]=…`.

```php
use Justpilot\Billomat\Api\OrderBy;
use Justpilot\Billomat\Api\RecurringFilter;
use Justpilot\Billomat\Model\Enum\RecurringCycle;

$monthly = $billomat->recurrings->list(new RecurringFilter(
    cycle: RecurringCycle::MONTHLY,
    orderBy: OrderBy::asc('next_creation_date'),
));
```

`listPage()` and `iterateAll()` work as described in [Pagination](../advanced/pagination.md).

### `get(int $id): ?Recurring` / `getOrFail(int $id): Recurring`

`get()` returns `null` on 404; `getOrFail()` throws a `NotFoundException` instead. The response contains embedded `recurring-items` and the tax breakdown; both are hydrated automatically.

### `create(RecurringCreateOptions $options): Recurring`

Items can be passed directly in the `create()` call via `items: […]` or `withItem()`; they end up under `recurring-items.recurring-item` in the payload.

```php
use Justpilot\Billomat\Api\RecurringCreateOptions;
use Justpilot\Billomat\Api\RecurringItemCreateOptions;
use Justpilot\Billomat\Model\Enum\RecurringAction;
use Justpilot\Billomat\Model\Enum\RecurringCycle;
use Justpilot\Billomat\Model\Money;

$opts = new RecurringCreateOptions(
    clientId: 12345,
    name: 'Hosting Basic',
    cycle: RecurringCycle::MONTHLY,
    cycleNumber: 1,
    action: RecurringAction::EMAIL,
    startDate: new DateTimeImmutable('2026-07-01'),
);

$item = new RecurringItemCreateOptions(quantity: 1.0, unitPrice: Money::of('19.90'), title: 'Web hosting Basic');
$opts = $opts->withItem($item);

$recurring = $billomat->recurrings->create($opts);
```

### `update(int $id, RecurringUpdateOptions $options): Recurring`

For partial updates. According to the Billomat docs, items **cannot** be changed here; use `$billomat->recurrings->items` for that. The class therefore has no `items` field.

### `delete(int $id): void`

Deletes a recurring invoice. Errors are thrown as exceptions.

### `stop(int $id): void`

Stops a recurring invoice without deleting it: Billomat sets `nextCreationDate` to `null` and creates no further invoices. To resume, set a new `nextCreationDate` with `update()`.

```php
$billomat->recurrings->stop($recurring->id);
```

## Write model: `RecurringCreateOptions`

Constructor: `new RecurringCreateOptions(int $clientId, …)`. `clientId` is required and the first parameter. All other fields are constructor parameters with a default and are passed as named arguments. The object is immutable (`final readonly`); every field has a `with*()` method that returns a modified copy (e.g. `$opts->withTitle('New')`).

### Identification and master data

| Property | Billomat field | Type |
|---|---|---|
| `clientId` | `client_id` | `int` (required) |
| `contactId` | `contact_id` | `?int` |
| `address` | `address` | `?string` |
| `numberPre` | `number_pre` | `?string` |
| `name` | `name` | `?string`, internal name |
| `title` | `title` | `?string` |
| `label` | `label` | `?string` |
| `intro`, `note` | same | `?string` |
| `reduction` | `reduction` | `?string` |
| `currencyCode` | `currency_code` | `?string` |
| `netGross` | `net_gross` | `?NetGross` |
| `quote` | `quote` | `?float` |
| `paymentTypes` | `payment_types` | `list<PaymentType>` (Billomat: comma-separated) |
| `freeTextId` | `free_text_id` | `?int` |
| `templateId` | `template_id` | `?int` |
| `offerId` | `offer_id` | `?int`, source [offer](offers.md) |
| `confirmationId` | `confirmation_id` | `?int`, source [confirmation](confirmations.md) |

### Dates and terms

| Property | Billomat field | Type |
|---|---|---|
| `supplyDate` | `supply_date` | `?\DateTimeImmutable` |
| `supplyDateType` | `supply_date_type` | `?SupplyDateType` |
| `dueDays` | `due_days` | `?int` |
| `discountRate` | `discount_rate` | `?int` |
| `discountDays` | `discount_days` | `?int` |

### Recurrence

| Property | Billomat field | Type |
|---|---|---|
| `action` | `action` | `?RecurringAction` |
| `cycle` | `cycle` | `?RecurringCycle` |
| `cycleNumber` | `cycle_number` | `?int` |
| `hour` | `hour` | `?int` (0–23, time of the run) |
| `startDate` | `start_date` | `?\DateTimeImmutable` |
| `endDate` | `end_date` | `?\DateTimeImmutable` |
| `nextCreationDate` | `next_creation_date` | `?\DateTimeImmutable` |
| `iterations` | `iterations` | `?int`, limits the number of runs |

### Email defaults (apply with `action = EMAIL`)

| Property | Billomat field | Type |
|---|---|---|
| `emailSender` | `email_sender` | `?string` |
| `emailSubject` | `email_subject` | `?string` |
| `emailMessage` | `email_message` | `?string` |
| `emailTemplateId` | `email_template_id` | `?int` |
| `emailFilename` | `email_filename` | `?string` |
| `emailBcc` | `email_bcc` | `?bool` |

### Postal mail (apply with `action = MAIL`)

| Property | Billomat field | Type |
|---|---|---|
| `letterColor` | `letter_color` | `?bool` |
| `letterDuplex` | `letter_duplex` | `?bool` |
| `letterPaperWeight` | `letter_paper_weight` | `?int` |

### Items

Items are passed via the constructor parameter `items` (`list<RecurringItemCreateOptions>`) and are then available in the `$items` property. `withItem()` returns a copy with one more item:

```php
$opts = $opts->withItem(new RecurringItemCreateOptions(quantity: 1.0, unitPrice: Money::of('19.90')));
```

`toArray()` renders them into `recurring-items.recurring-item`.

## Write model: `RecurringUpdateOptions`

Has the same fields as `RecurringCreateOptions`, but `clientId` is optional (for moving the recurring invoice to another client) and there is no `items` field. `null` means "don't send", the value stays unchanged. `Clear::Field` (`Justpilot\Billomat\Api\Clear`) sends the field as JSON `null` so Billomat empties it, e.g. `new RecurringUpdateOptions(note: Clear::Field)`.

## Read model: `Recurring`

`final readonly class Recurring`. The main fields:

| Property | Type |
|---|---|
| `id` | `int` |
| `clientId` | `int` |
| `contactId` | `?int` |
| `created` | `?\DateTimeImmutable` |
| `address` | `?string` |
| `supplyDate`, `startDate`, `endDate` | `?\DateTimeImmutable` |
| `supplyDateType` | `?SupplyDateType` |
| `dueDays`, `discountRate`, `discountDays` | `?int`, `?float`, `?int` |
| `name`, `title`, `label`, `intro`, `note`, `reduction` | `?string` |
| `currencyCode` | `?string` |
| `paymentTypes` | `list<PaymentType>` (unknown values are dropped) |
| `netGross` | `?NetGross` |
| `quote` | `?float` |
| `action`, `cycle` | `?RecurringAction`, `?RecurringCycle` |
| `cycleNumber`, `hour`, `iterations`, `counter` | `?int` |
| `lastCreationDate`, `nextCreationDate` | `?\DateTimeImmutable` |
| `totalGross`, `totalNet` | `?Money` |
| `emailSender`, `emailSubject`, `emailMessage` | `?string` |
| `emailTemplateId`, `freeTextId`, `templateId` | `?int` |
| `taxes` | `list<TaxSummary>` (`name`, `rate`, `amount` as `Money`) |
| `items` | `list<RecurringItem>` |
| `updated` | `?\DateTimeImmutable` |
| `totalReduction` | `?Money` |
| `totalNetUnreduced` | `?Money` |
| `totalGrossUnreduced` | `?Money` |
| `discountAmount` | `?Money` |
| `status` | `?string` |
| `inProgress` | `?bool` |
| `ultimo` | `?bool` |
| `emailFilename` | `?string` |
| `emailBcc` | `?bool` |
| `letterColor` | `?bool` |
| `letterDuplex` | `?bool` |
| `letterPaperWeight` | `?int` |
| `offerId` | `?int` |
| `confirmationId` | `?int` |
| `customfield` | `?string` (free extra field, see [custom meta attributes](../concepts/custom-meta-attributes.md)) |
| `extra` | `array<string, mixed>`: fields the SDK doesn't map (yet), raw; see [models](../concepts/models.md) |

`counter` counts the runs executed so far; `nextCreationDate` is the next scheduled creation date.

## Recurring items

Structurally identical to `InvoiceItem`, but they reference `recurring_id` instead of `invoice_id`. `$billomat->recurrings->items` is the same generic `ItemsApi` as for [invoice items](invoice-items.md).

### Methods

```php
$items = $billomat->recurrings->items->list($recurring->id);

$opts = new RecurringItemCreateOptions(quantity: 2.0, unitPrice: Money::of('49.90'));
$item = $billomat->recurrings->items->create($recurring->id, $opts);

$billomat->recurrings->items->update($item->id, new RecurringItemUpdateOptions(unitPrice: Money::of('44.90')));
$billomat->recurrings->items->delete($item->id);
```

`create()` injects `recurring_id` into the payload automatically; `RecurringItemCreateOptions` itself has no such field. `update()` takes `RecurringItemUpdateOptions`: every field is optional, only the fields you set are sent and Billomat keeps the others.

### Write model: `RecurringItemCreateOptions`

Constructor: `new RecurringItemCreateOptions(float $quantity, Money $unitPrice, …)`. Both values are required and always sent, even if they are `0`. Like the other options classes, it is `final readonly` with a `with*()` method per field.

| Property | Billomat field | Type |
|---|---|---|
| `quantity` | `quantity` | `float` (required) |
| `unitPrice` | `unit_price` | `Money` (required) |
| `type` | `type` | `?ItemType` |
| `articleId` | `article_id` | `?int` |
| `title`, `description`, `unit` | same | `?string` |
| `taxName`, `taxRate`, `taxChangedManually` | same | `?string`, `?float`, `?bool` |
| `reduction` | `reduction` | `?string` |
| `position` | `position` | `?int` |

### Read model: `RecurringItem`

`final readonly`. Same fields as `InvoiceItem`, with `recurringId` instead of `invoiceId`.

## Recurring tags

Works exactly like [invoice tags](invoice-tags.md), but for a recurring invoice instead of an invoice.

```php
use Justpilot\Billomat\Api\RecurringTagCreateOptions;

$tag = $billomat->recurrings->tags->create(
    $recurring->id,
    new RecurringTagCreateOptions(name: 'auto-paid'),
);

$tags = $billomat->recurrings->tags->list($recurring->id);
$cloud = $billomat->recurrings->tags->cloud(); // aggregated, with count
```

Read models: `RecurringTag` (link with `recurringId`) and `RecurringTagCloudEntry` (aggregated).

## Recurring email receivers

Maintains the TO/CC/BCC list for automatic email delivery (`action = EMAIL`). There is no `update()`; delete recipients and create them again.

```php
use Justpilot\Billomat\Api\RecurringEmailReceiverCreateOptions;
use Justpilot\Billomat\Model\Enum\RecurringEmailReceiverType;

$billomat->recurrings->emailReceivers->create(
    $recurring->id,
    new RecurringEmailReceiverCreateOptions(
        type: RecurringEmailReceiverType::TO,
        address: 'client@example.com',
    ),
);

$billomat->recurrings->emailReceivers->create(
    $recurring->id,
    new RecurringEmailReceiverCreateOptions(
        type: RecurringEmailReceiverType::BCC,
        address: 'log@example.com',
    ),
);
```

`update($id, RecurringEmailReceiverUpdateOptions $options)` changes the type or the address of a receiver; only the fields you set are sent. The recurring invoice of a receiver can't be changed.

```php
use Justpilot\Billomat\Api\RecurringEmailReceiverUpdateOptions;

$billomat->recurrings->emailReceivers->update($receiverId, new RecurringEmailReceiverUpdateOptions(type: RecurringEmailReceiverType::CC));
```

### Write model: `RecurringEmailReceiverCreateOptions`

Both values are required constructor parameters: `type` (`RecurringEmailReceiverType`) and `address` (`string`). The object is `final readonly` with `with*()` methods. The recurring invoice ID is the first argument of `create()`, which adds `recurring_id` to the payload. `list($recurringId, $filters?)`, `listPage()` and `iterateAll()` take a `SubResourceFilter` for sorting and pagination.

### Read model: `RecurringEmailReceiver`

`final readonly`. Fields: `id` (`?int`), `recurringId` (`int`), `type` (`?RecurringEmailReceiverType`, `null` for an unknown value), `address` (`string`), `extra`.

## Enums

- [`RecurringCycle`](../../src/Model/Enum/RecurringCycle.php): `DAILY`, `WEEKLY`, `MONTHLY`, `YEARLY`.
- [`RecurringAction`](../../src/Model/Enum/RecurringAction.php):
  - `CREATE`: only creates a draft invoice,
  - `COMPLETE`: creates and completes the invoice (the PDF is generated),
  - `EMAIL`: creates, completes and sends it by email (uses `recurring-email-receivers`),
  - `MAIL`: creates, completes and sends it by post (Pixelletter, costs money).
- [`RecurringEmailReceiverType`](../../src/Model/Enum/RecurringEmailReceiverType.php): `TO`, `CC`, `BCC` with the wire values `To`/`Cc`/`Bcc`, as Billomat returns them. `fromApi()` also accepts other casing (the Billomat docs use lowercase).
- [`PaymentType`](../../src/Model/Enum/PaymentType.php), [`NetGross`](../../src/Model/Enum/NetGross.php), [`SupplyDateType`](../../src/Model/Enum/SupplyDateType.php), [`ItemType`](../../src/Model/Enum/ItemType.php): see [Invoices](invoices.md#enums).

## Pitfalls

- **Don't change items via `update()`.** Billomat ignores embedded `recurring-items` on a PUT to `/recurrings/{id}`. Always use `$billomat->recurrings->items`.
- **`action = EMAIL` without recipients fails.** If you set `action` to `EMAIL`, you need at least one `RecurringEmailReceiver` of type `TO`.
- **`emailTemplateId`** selects an email template stored in the Billomat account; `emailSender`/`emailSubject`/`emailMessage` override individual parts of it.
- **Read `cycle` and `cycleNumber` together.** `cycle=MONTHLY` alone doesn't mean "monthly"; it means "every `cycleNumber` months". The default is 1 if not set.
- **Email receiver `type` casing.** The enum sends `To`/`Cc`/`Bcc` (capitalized), matching what Billomat returns; the Billomat docs show lowercase. When reading, `fromApi()` accepts either.
- **`hour`** only affects the time of day on the creation date; the date itself comes from `cycle` + `cycleNumber`.

## End-to-end example

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\RecurringCreateOptions;
use Justpilot\Billomat\Api\RecurringEmailReceiverCreateOptions;
use Justpilot\Billomat\Api\RecurringItemCreateOptions;
use Justpilot\Billomat\Api\RecurringItemUpdateOptions;
use Justpilot\Billomat\Api\RecurringTagCreateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\Enum\RecurringAction;
use Justpilot\Billomat\Model\Enum\RecurringCycle;
use Justpilot\Billomat\Model\Enum\RecurringEmailReceiverType;
use Justpilot\Billomat\Model\Money;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

// 1) Create the recurring invoice: monthly, sent automatically by email
$opts = new RecurringCreateOptions(
    clientId: 12345,
    name: 'Hosting Basic',
    title: 'Web hosting subscription',
    cycle: RecurringCycle::MONTHLY,
    cycleNumber: 1,
    action: RecurringAction::EMAIL,
    startDate: new DateTimeImmutable('2026-07-01'),
    dueDays: 14,
);

$item = new RecurringItemCreateOptions(quantity: 1.0, unitPrice: Money::of('19.90'), title: 'Web hosting Basic');
$opts = $opts->withItem($item);

$recurring = $billomat->recurrings->create($opts);
printf("Recurring invoice #%d created, next run: %s\n",
    $recurring->id,
    $recurring->nextCreationDate?->format('Y-m-d') ?? 'tbd',
);

// 2) Add email recipients
$billomat->recurrings->emailReceivers->create(
    $recurring->id,
    new RecurringEmailReceiverCreateOptions(
        type: RecurringEmailReceiverType::TO,
        address: 'client@example.com',
    ),
);

$billomat->recurrings->emailReceivers->create(
    $recurring->id,
    new RecurringEmailReceiverCreateOptions(
        type: RecurringEmailReceiverType::BCC,
        address: 'accounting@mycompany.example',
    ),
);

// 3) Add a tag to make filtering easier
$billomat->recurrings->tags->create(
    $recurring->id,
    new RecurringTagCreateOptions(name: 'hosting'),
);

// 4) Change an item later: only the price is sent, the other fields stay
foreach ($billomat->recurrings->items->list($recurring->id) as $current) {
    $billomat->recurrings->items->update($current->id, new RecurringItemUpdateOptions(unitPrice: Money::of('24.90')));
}
```
