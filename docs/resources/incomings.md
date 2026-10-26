<!-- Source: https://www.billomat.com/api/eingangsrechnungen/ -->

# Incomings (Eingangsrechnungen)

API wrapper for incoming invoices under `/incomings` and their four sub-resources (`/incoming-comments`, `/incoming-payments`, `/incoming-tags`, `/incoming-property-values`). The custom property definitions live under `/incoming-properties` (`$billomat->incomings->properties`, documented in [Properties](properties.md) and only touched on here).

## Access

```php
$billomat->incomings                  // the incoming invoices themselves
$billomat->incomings->comments        // comment history per document
$billomat->incomings->payments        // payments made
$billomat->incomings->tags            // tags
$billomat->incomings->propertyValues  // custom property values
$billomat->incomings->properties      // custom property definitions
```

## Model

An incoming is a supplier invoice: a document someone else issued to your company. It's the counterpart of `Invoice`, but much slimmer: Billomat stores no line items here, only the totals (`totalGross`, `totalNet`).

- **Origin**: the required field `supplierId` points to a [supplier](suppliers.md).
- **Status**: `IncomingStatus` (`DRAFT`, `OPEN`, `OVERDUE`, `PAID`, `CANCELED`). Billomat derives it from the due date, cancellation and the sum of payments; you can't write it directly.
- **Attachment**: optionally via `upload()` or the `base64file` field on create/update. Alternatively, a file already uploaded as an [inbox document](inbox-documents.md) can serve as the source.
- **Payments**: via `IncomingPayment` entries. The sum of payments goes into `paidAmount`, the remainder into `openAmount`.
- **History**: every state change (creation, payment, cancellation, upload, …) automatically creates an `IncomingComment` with the matching `actionkey`.

Lifecycle in practice: `create()` creates a document with status `OPEN` (or `DRAFT`, depending on required fields). `$billomat->incomings->payments->create(...)` with `markIncomingAsPaid: true` switches it to `PAID` once the sum matches. `cancel()` sets it to `CANCELED`, `uncancel()` reverts that.

## Endpoint overview

### `/incomings`

| Method | HTTP | Path |
|---|---|---|
| `list($filters?)` | GET | `/incomings` |
| `listPage($filters?)` | GET | `/incomings` (one page with metadata) |
| `iterateAll($filters?, $pageSize?)` | GET | `/incomings` (all pages) |
| `get($id)` / `getOrFail($id)` | GET | `/incomings/{id}` |
| `create($options)` | POST | `/incomings` |
| `update($id, $options)` | PUT | `/incomings/{id}` |
| `delete($id)` | DELETE | `/incomings/{id}` |
| `cancel($id)` | PUT | `/incomings/{id}/cancel` |
| `uncancel($id)` | PUT | `/incomings/{id}/uncancel` |
| `upload($id, $base64Pdf)` | PUT | `/incomings/{id}/upload` |

### `/incoming-comments`

| Method | HTTP | Path |
|---|---|---|
| `list($incomingId, $actionKeys?, $filters?)` | GET | `/incoming-comments?incoming_id={id}` |
| `listPage(…)` / `iterateAll(…, $pageSize?)` | GET | `/incoming-comments?incoming_id={id}` (paginated) |
| `get($id)` / `getOrFail($id)` | GET | `/incoming-comments/{id}` |
| `create($incomingId, $options)` | POST | `/incoming-comments` |
| `delete($id)` | DELETE | `/incoming-comments/{id}` |

### `/incoming-payments`

| Method | HTTP | Path |
|---|---|---|
| `list($incomingId?, $filters?)` | GET | `/incoming-payments` |
| `listPage($incomingId?, $filters?)` / `iterateAll($incomingId?, $filters?, $pageSize?)` | GET | `/incoming-payments` (paginated) |
| `get($id)` / `getOrFail($id)` | GET | `/incoming-payments/{id}` |
| `create($incomingId, $options)` | POST | `/incoming-payments` |
| `delete($id)` | DELETE | `/incoming-payments/{id}` |

### `/incoming-tags`

| Method | HTTP | Path |
|---|---|---|
| `list($incomingId, $filters?)` | GET | `/incoming-tags?incoming_id={id}` |
| `listPage($incomingId, $filters?)` / `iterateAll($incomingId, $filters?, $pageSize?)` | GET | `/incoming-tags?incoming_id={id}` (paginated) |
| `cloud()` | GET | `/incoming-tags` |
| `get($id)` / `getOrFail($id)` | GET | `/incoming-tags/{id}` |
| `create($incomingId, $options)` | POST | `/incoming-tags` |
| `delete($id)` | DELETE | `/incoming-tags/{id}` |

### `/incoming-property-values`

| Method | HTTP | Path |
|---|---|---|
| `list($incomingId?, $filters?)` | GET | `/incoming-property-values` |
| `listPage($incomingId?, $filters?)` | GET | `/incoming-property-values` |
| `iterateAll($incomingId?, $filters?, $pageSize?)` | GET | `/incoming-property-values` (all pages) |
| `get($id)` / `getOrFail($id)` | GET | `/incoming-property-values/{id}` |
| `create($incomingId, $options)` | POST | `/incoming-property-values` |

## Methods

### Incomings

#### `list(IncomingFilter|array $filters = []): list<Incoming>`

Filters according to the Billomat docs: `supplier_id`, `incoming_number`, `status`, `from`, `to`, `label`, `intro`, `note`, `tags`.

Use the typed filter `IncomingFilter` with the camelCase fields `supplierId`, `incomingNumber`, `status` (`list<IncomingStatus>`), `from`/`to` (`DateTimeImmutable`), `note` and `tags`. Multiple values are lists and are sent comma-separated. There are also `orderBy`, `page`, `perPage` and `extra` for other query parameters (e.g. `label`, `intro`). A plain array still works; multiple statuses are then passed as an array and encoded as `status[]=OPEN&status[]=OVERDUE`.

```php
use Justpilot\Billomat\Api\IncomingFilter;
use Justpilot\Billomat\Api\OrderBy;
use Justpilot\Billomat\Model\Enum\IncomingStatus;

$open = $billomat->incomings->list(new IncomingFilter(
    status: [IncomingStatus::OPEN, IncomingStatus::OVERDUE],
    orderBy: OrderBy::desc('date'),
));
```

`listPage()` returns the same list plus pagination metadata, `iterateAll()` walks through all pages lazily, see [Pagination](../advanced/pagination.md).

#### `get(int $id): ?Incoming`

Returns `null` on 404. `getOrFail(int $id): Incoming` throws a `NotFoundException` instead.

#### `create(IncomingCreateOptions $options): Incoming`

```php
use Justpilot\Billomat\Api\IncomingCreateOptions;
use Justpilot\Billomat\Model\Money;

$opts = new IncomingCreateOptions(
    supplierId: 4711,
    incomingNumber: 'LR-2026-0815',
    date: new DateTimeImmutable('2026-05-30'),
    dueDate: new DateTimeImmutable('2026-06-29'),
    totalGross: Money::of('357.00'),
    totalNet: Money::of('300.00'),
    currencyCode: 'EUR',
);

$incoming = $billomat->incomings->create($opts);
```

#### `update(int $id, IncomingUpdateOptions $options): Incoming`

Partial update: only set fields are sent.

#### `delete(int $id): void`

Deletes the incoming invoice.

#### `cancel(int $id): void`

Sets the status to `CANCELED`. Errors are thrown as exceptions.

#### `uncancel(int $id): void`

Reverts a cancellation.

#### `upload(int $id, string $base64Pdf): void`

Attaches a PDF to the document. Expects content that is **already Base64-encoded**; the method doesn't encode it.

```php
$billomat->incomings->upload(
    $incoming->id,
    base64_encode(file_get_contents('/tmp/invoice.pdf')),
);
```

### Incoming comments

```php
use Justpilot\Billomat\Api\IncomingCommentCreateOptions;
use Justpilot\Billomat\Model\Enum\IncomingCommentActionKey;

$billomat->incomings->comments->create(
    $incoming->id,
    new IncomingCommentCreateOptions(
        comment: 'Reminder sent',
    ),
);

// Filter the history by actionkey
$paymentEvents = $billomat->incomings->comments->list(
    $incoming->id,
    [IncomingCommentActionKey::PAYMENT],
);
```

`list(int $incomingId, ?array $actionKeys = null, SubResourceFilter|array $filters = [])` passes the `actionkey` list to Billomat as CSV (`actionkey=PAYMENT,UPLOAD`).

### Incoming payments

```php
use Justpilot\Billomat\Api\IncomingPaymentCreateOptions;
use Justpilot\Billomat\Api\IncomingPaymentFilter;
use Justpilot\Billomat\Api\OrderBy;
use Justpilot\Billomat\Model\Enum\PaymentType;
use Justpilot\Billomat\Model\Money;

$payment = new IncomingPaymentCreateOptions(
    amount: Money::of('357.00'),
    date: new DateTimeImmutable('2026-06-15'),
    type: PaymentType::BANK_TRANSFER,
    markIncomingAsPaid: true,
);

$billomat->incomings->payments->create($incoming->id, $payment);

$payments = $billomat->incomings->payments->list($incoming->id, new IncomingPaymentFilter(
    orderBy: OrderBy::desc('date'),
));
```

`list(?int $incomingId = null, IncomingPaymentFilter|array $filters = [])` lists the payments of one incoming invoice, or of all of them when `$incomingId` is `null`. `IncomingPaymentFilter` filters server-side by `from`, `to`, `type` (list of `PaymentType`) and `userId`, and sorts and paginates (`orderBy`, `page`, `perPage`); `listPage()` and `iterateAll()` take the same arguments.

### Incoming tags

```php
use Justpilot\Billomat\Api\IncomingTagCreateOptions;

$billomat->incomings->tags->create(
    $incoming->id,
    new IncomingTagCreateOptions(name: 'rent'),
);

$tags = $billomat->incomings->tags->list($incoming->id);
$cloud = $billomat->incomings->tags->cloud(); // aggregated, with count per tag
```

`cloud()` reads the `tag` node (not `incoming-tag`) and returns `IncomingTagCloudEntry` objects.

### Incoming property values

```php
use Justpilot\Billomat\Api\IncomingPropertyValueCreateOptions;

$billomat->incomings->propertyValues->create(
    $incoming->id,
    new IncomingPropertyValueCreateOptions(
        incomingPropertyId: 42,
        value: 'Cost center marketing',
    ),
);
```

There's deliberately no `update()`: Billomat upserts values via `create()` (an existing value for the same `incomingPropertyId` is overwritten).

## Write model: `IncomingCreateOptions`

Constructor: `new IncomingCreateOptions(int $supplierId, …)`. `supplierId` is required and the first parameter. All other fields are constructor parameters with a default and are passed as named arguments. The object is immutable (`final readonly`); each field has a `with*()` method that returns a modified copy (e.g. `$opts->withLabel('New')`).

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `supplierId` | `supplier_id` | `int` (required) | |
| `date` | `date` | `?\DateTimeImmutable` | Invoice date |
| `supplyDate` | `supply_date` | `?\DateTimeImmutable` | Delivery/service date |
| `dueDate` | `due_date` | `?\DateTimeImmutable` | Due date |
| `dueDays` | `due_days` | `?int` | Alternative to `dueDate` |
| `incomingNumber` | `incoming_number` | `?string` | The supplier's invoice number |
| `number` | `number` | `?string` | The supplier's invoice number under the name Billomat also uses in responses (read model: `incomingNumber`) |
| `address` | `address` | `?string` | Full sender address (default: the supplier's address) |
| `label` | `label` | `?string` | |
| `intro`, `note` | same | `?string` | |
| `totalGross` | `total_gross` | `?Money` | |
| `totalNet` | `total_net` | `?Money` | |
| `currencyCode` | `currency_code` | `?string` | ISO 4217, e.g. `EUR` (default: the supplier's currency) |
| `quote` | `quote` | `?float` | Exchange rate to the default currency |
| `clientNumber` | `client_number` | `?string` | Your customer number at the supplier (e.g. contract number) |
| `expenseAccountNumber` | `expense_account_number` | `?int` | Expense account |
| `category` | `category` | `?string` | Category |
| `base64file` | `base64file` | `?string` | Base64-encoded document file (PDF or image) |

## Write model: `IncomingUpdateOptions`

Mirrors `IncomingCreateOptions`, but `supplierId` is optional here (`?int`, not a required parameter). `null` means "don't send", the value stays unchanged. `Clear::Field` (`Justpilot\Billomat\Api\Clear`) sends the field as JSON `null` so Billomat empties it, e.g. `new IncomingUpdateOptions(note: Clear::Field)`.

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `supplierId` | `supplier_id` | `int\|Clear\|null` | |
| all other fields | same as `IncomingCreateOptions` | same type, additionally `\|Clear` | |

## Write model: `IncomingCommentCreateOptions`

Constructor: `new IncomingCommentCreateOptions(string $comment, ?IncomingCommentActionKey $actionkey = null)`. The incoming invoice ID isn't part of the options: pass it as the first argument of `create()`, which adds `incoming_id` to the payload.

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `comment` | `comment` | `string` (required) | Free text |
| `actionkey` | `actionkey` | `?IncomingCommentActionKey` | Default `COMMENT`; for manually set events `EDIT`, `OPEN`, … |

## Write model: `IncomingPaymentCreateOptions`

Constructor: `new IncomingPaymentCreateOptions(Money $amount, …)`. `amount` is sent even if it is `0`. `mark_incoming_as_paid` is always sent as well, also for `false` (as `0`). The incoming invoice ID isn't part of the options: pass it as the first argument of `create()`, which adds `incoming_id` to the payload.

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `amount` | `amount` | `Money` (required) | |
| `date` | `date` | `?\DateTimeImmutable` | Payment date |
| `type` | `type` | `?PaymentType` | Enum shared with invoices |
| `comment` | `comment` | `?string` | |
| `transactionPurpose` | `transaction_purpose` | `?string` | Payment reference |
| `markIncomingAsPaid` | `mark_incoming_as_paid` | `bool` (default `false`) | Serialized as `1`/`0` |

## Write model: `IncomingTagCreateOptions`

Constructor: `new IncomingTagCreateOptions(string $name)`. No optional fields. The incoming invoice ID isn't part of the options: pass it as the first argument of `create()`, which adds `incoming_id` to the payload.

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `name` | `name` | `string` (required) | |

## Write model: `IncomingPropertyValueCreateOptions`

Constructor: `new IncomingPropertyValueCreateOptions(int $incomingPropertyId, mixed $value)`. All fields are required. The incoming invoice ID isn't part of the options: pass it as the first argument of `create()`, which adds `incoming_id` to the payload.

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `incomingPropertyId` | `incoming_property_id` | `int` (required) | Refers to `IncomingProperty::$id` |
| `value` | `value` | `mixed` (required) | Type depends on the definition's `PropertyType` |

## Read model: `Incoming`

`final readonly class Incoming`.

| Property | Type |
|---|---|
| `id` | `int` |
| `supplierId` | `?int` |
| `created` | `?\DateTimeImmutable` |
| `date`, `dueDate` | `?\DateTimeImmutable` |
| `status` | `?IncomingStatus` |
| `incomingNumber` (wire field `number`, falling back to `incoming_number`), `address`, `label`, `note` | `?string` |
| `totalGross`, `totalNet`, `paidAmount`, `openAmount` | `?Money` |
| `currencyCode` | `?string` |
| `quote` | `?float` |
| `updated` | `?\DateTimeImmutable` |
| `clientNumber` | `?string` |
| `expenseAccountNumber` | `?string` |
| `category` | `?string` |
| `pageCount` | `?int` |
| `hasAttachment` | `?bool` |
| `customfield` | `?string` (free-form extra field, see [Custom meta attributes](../concepts/custom-meta-attributes.md)) |
| `extra` | `array<string, mixed>` (unmapped fields, see [Models](../concepts/models.md)) |

`paidAmount` and `openAmount` are calculated server-side from the payments. Amounts are [`Money`](../concepts/money.md) values.

## Read model: `IncomingComment`

| Property | Type |
|---|---|
| `id` | `int` |
| `incomingId` | `int` |
| `comment` | `?string` |
| `created` | `?\DateTimeImmutable` |
| `userId` | `?int` |
| `actionkey` | `?IncomingCommentActionKey` |
| `actionkeyRaw` | `?string` |
| `customfield` | `?string` (free-form extra field, see [Custom meta attributes](../concepts/custom-meta-attributes.md)) |
| `extra` | `array<string, mixed>` |

`actionkeyRaw` holds the raw API value; `actionkey` is `null` if Billomat returns an unknown value (`tryFrom`). `actionkeyRaw` helps when debugging.

## Read model: `IncomingPayment`

| Property | Type |
|---|---|
| `id` | `int` |
| `incomingId` | `int` |
| `date` | `?\DateTimeImmutable` |
| `amount` | `Money` |
| `type` | `?PaymentType` |
| `comment` | `?string` |
| `created` | `?\DateTimeImmutable` |
| `userId` | `?int` |
| `transactionPurpose` | `?string` |
| `currencyCode` | `?string` |
| `quote` | `?float` |
| `paymentGroup` | `?string` |
| `finapiTransactionId` | `?string` (finAPI bank reconciliation) |
| `finapiTransactionAmount` | `?Money` (finAPI bank reconciliation) |
| `documentTitle` | `?string` |
| `customfield` | `?string` (free-form extra field, see [Custom meta attributes](../concepts/custom-meta-attributes.md)) |
| `extra` | `array<string, mixed>` |

## Read model: `IncomingTag`

| Property | Type |
|---|---|
| `id` | `int` |
| `incomingId` | `int` |
| `name` | `string` |
| `customfield` | `?string` (free-form extra field, see [Custom meta attributes](../concepts/custom-meta-attributes.md)) |
| `extra` | `array<string, mixed>` |

## Read model: `IncomingTagCloudEntry`

| Property | Type |
|---|---|
| `id` | `?int` |
| `name` | `string` |
| `count` | `int` |
| `extra` | `array<string, mixed>` |

## Read model: `IncomingPropertyValue`

| Property | Type |
|---|---|
| `id` | `int` |
| `incomingId` | `int` |
| `incomingPropertyId` | `int` |
| `type` | `?PropertyType` |
| `name` | `?string` |
| `value` | `mixed` |
| `created`, `updated` | `?\DateTimeImmutable` |
| `customfield` | `?string` (free-form extra field, see [Custom meta attributes](../concepts/custom-meta-attributes.md)) |
| `extra` | `array<string, mixed>` |

## Read model: `IncomingProperty`

`final readonly class IncomingProperty`. Definition of a custom property on incoming invoices, see [Properties](properties.md).

| Property | Type |
|---|---|
| `id` | `int` |
| `name` | `string` |
| `type` | `?PropertyType` |
| `defaultValue` | `?string` |
| `isNvl` | `?bool` |
| `customfield` | `?string` (free-form extra field, see [Custom meta attributes](../concepts/custom-meta-attributes.md)) |
| `extra` | `array<string, mixed>` |

## Enums used

- [`IncomingStatus`](../../src/Model/Enum/IncomingStatus.php): `DRAFT`, `OPEN`, `OVERDUE`, `PAID`, `CANCELED`. Set by the server, not by the client. `label()` returns a German display label (`Entwurf`, `Offen`, …).
- [`IncomingCommentActionKey`](../../src/Model/Enum/IncomingCommentActionKey.php): `CREATE`, `EDIT`, `OPEN`, `COMPLETE`, `CANCEL`, `UNCANCEL`, `CHANGE_STATUS`, `PAYMENT`, `UPLOAD`, `COMMENT`. Billomat generates most values automatically; usually only `COMMENT` makes sense manually.
- [`PaymentType`](../../src/Model/Enum/PaymentType.php): enum shared with invoices (`BANK_TRANSFER`, `CASH`, `PAYPAL`, …).
- [`PropertyType`](../../src/Model/Enum/PropertyType.php): data type of the property definition (`IncomingProperty`) and of `IncomingPropertyValue::$type`.

<!-- json-examples:start (generated by tools/generate-json-examples.php, run `composer docs:json`) -->
## JSON on the wire

Billomat's API documentation only shows XML. These are the JSON bodies the SDK sends and receives: requests as the SDK serializes them, responses recorded in the Billomat sandbox and anonymized ([contract fixtures](../../tests/Fixtures/Contract)). Billomat sends every value as a string; the read models cast them (see [Read models](../concepts/models.md)).

::: code-group
<<< @/snippets/json/incomings.create.json [POST /incomings]
:::
<!-- json-examples:end -->

## Pitfalls

- **`upload()` doesn't encode.** The string must already be Base64-encoded; passing `file_get_contents()` directly results in broken PDFs on the server. Always use `base64_encode(file_get_contents(...))`. The same applies to the `base64file` option.
- **Status is read-only.** You can't set `IncomingStatus` via `IncomingCreateOptions`/`IncomingUpdateOptions`; Billomat derives it from the due date, cancellation and the sum of payments. Status changes happen only via `cancel()`/`uncancel()` and `markIncomingAsPaid` on payments.
- **`markIncomingAsPaid` is always sent.** Unlike the other optional fields, `IncomingPaymentCreateOptions::toArray()` doesn't filter it out; `false` ends up as `mark_incoming_as_paid=0` in the payload. Leave it unset (`false`) for partial payments.
- **`tag` node in `cloud()`.** For `GET /incoming-tags` without `incoming_id`, Billomat returns the node `tag` (with `count`), not `incoming-tag`. `$billomat->incomings->tags` handles both paths internally; cloud entries have a different shape than single tags and are hydrated into a separate read model.
- **`incomingNumber` vs. `number`.** Billomat accepts the supplier's invoice number as `incoming_number` and as `number`, and returns it as `number`. The read model exposes it as `incomingNumber`.
- **No line items.** Unlike `Invoice`, there's no `IncomingItem` resource; amounts are only kept as totals in `totalGross`/`totalNet`.
- **`actionkey` on comments is mostly server-driven.** When you create a comment manually, omit `actionkey`; Billomat then sets `COMMENT`. Self-set values like `PAYMENT` are accepted, but their effects (e.g. a status change) don't happen.
- **`$billomat->incomings->propertyValues` has no `update()`/`delete()`.** Values are upserted via `create()`; the API has no explicit update endpoint.
- **`$billomat->incomings->properties->create()` uses `PropertyCreateOptions`** (shared with the article, client, supplier and user properties), not a class of its own.

## End-to-end example

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\IncomingCommentCreateOptions;
use Justpilot\Billomat\Api\IncomingCreateOptions;
use Justpilot\Billomat\Api\IncomingPaymentCreateOptions;
use Justpilot\Billomat\Api\IncomingTagCreateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\Enum\IncomingCommentActionKey;
use Justpilot\Billomat\Model\Enum\PaymentType;
use Justpilot\Billomat\Model\Money;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

// 1) Create an incoming invoice
$opts = new IncomingCreateOptions(
    supplierId: 4711,
    incomingNumber: 'LR-2026-0815',
    date: new DateTimeImmutable('2026-05-30'),
    dueDate: new DateTimeImmutable('2026-06-29'),
    totalGross: Money::of('357.00'),
    totalNet: Money::of('300.00'),
    currencyCode: 'EUR',
    label: 'Hosting May 2026',
);

$incoming = $billomat->incomings->create($opts);

// 2) Attach the PDF
$billomat->incomings->upload(
    $incoming->id,
    base64_encode((string) file_get_contents('/tmp/invoice-may-2026.pdf')),
);

// 3) Add a tag to make filtering easier
$billomat->incomings->tags->create(
    $incoming->id,
    new IncomingTagCreateOptions(name: 'hosting'),
);

// 4) Record the payment; this marks the document as paid
$billomat->incomings->payments->create(
    $incoming->id,
    new IncomingPaymentCreateOptions(
        amount: Money::of('357.00'),
        date: new DateTimeImmutable('2026-06-15'),
        type: PaymentType::BANK_TRANSFER,
        transactionPurpose: 'INV LR-2026-0815',
        markIncomingAsPaid: true,
    ),
);

// 5) Add your own comment
$billomat->incomings->comments->create(
    $incoming->id,
    new IncomingCommentCreateOptions(
        comment: 'Included in the DATEV export.',
    ),
);

// 6) Inspect the history: payment and status events only
$paymentLog = $billomat->incomings->comments->list(
    $incoming->id,
    [IncomingCommentActionKey::PAYMENT, IncomingCommentActionKey::CHANGE_STATUS],
);

foreach ($paymentLog as $entry) {
    printf("[%s] %s: %s\n",
        $entry->created?->format('Y-m-d H:i') ?? '?',
        $entry->actionkeyRaw ?? '-',
        $entry->comment ?? '',
    );
}

// 7) Check the current state
$fresh = $billomat->incomings->getOrFail($incoming->id);
printf("Status: %s, open: %s %s\n",
    $fresh->status?->label() ?? 'unknown',
    $fresh->openAmount?->format(2) ?? '0.00',
    $fresh->currencyCode ?? '',
);
```
