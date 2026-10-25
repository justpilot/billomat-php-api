<!-- Source: https://www.billomat.com/api/gutschriften/ -->

# Credit Notes (Gutschriften)

API wrapper for credit notes under `/credit-notes` and their four sub-resources (`/credit-note-items`, `/credit-note-comments`, `/credit-note-tags`, `/credit-note-payments`).

## Access

```php
$billomat->creditNotes            // credit notes
$billomat->creditNotes->items     // line items
$billomat->creditNotes->comments  // activity log / free-form comments
$billomat->creditNotes->tags      // tags
$billomat->creditNotes->payments  // payouts to the client
```

## Model

A credit note is a correction or refund to the client. It can stand on its own or be linked to an original invoice via `invoiceId` (corrective credit note). The lifecycle is status-driven:

```
DRAFT  ── complete() ──▶  OPEN  ── payment (mark_credit_note_as_paid=1) ──▶  PAID
  │                        │
  │                        └── cancel() ──▶  CANCELED  ── uncancel() ──▶  OPEN
  └── delete() (any status; prefer cancel() once completed)
```

Only credit notes in status `DRAFT` are fully editable. `update()` on a completed credit note usually throws a `ValidationException`.

## Endpoint overview

### `/credit-notes`

| Method | HTTP | Path |
|---|---|---|
| `list($filters?)` | GET | `/credit-notes` |
| `listPage($filters?)` | GET | `/credit-notes` (one page plus pagination metadata) |
| `iterateAll($filters?, $pageSize?)` | GET | `/credit-notes` (all pages, lazily) |
| `get($id)` / `getOrFail($id)` | GET | `/credit-notes/{id}` |
| `create($options)` | POST | `/credit-notes` |
| `update($id, $options)` | PUT | `/credit-notes/{id}` |
| `delete($id)` | DELETE | `/credit-notes/{id}` |
| `complete($id, $templateId?)` | PUT | `/credit-notes/{id}/complete` |
| `cancel($id)` | PUT | `/credit-notes/{id}/cancel` |
| `uncancel($id)` | PUT | `/credit-notes/{id}/uncancel` |
| `email($id, $options?)` | POST | `/credit-notes/{id}/email` |
| `pdf($id, $type?)` | GET | `/credit-notes/{id}/pdf` |
| `pdfContent($id, $type?)` | GET | `/credit-notes/{id}/pdf?format=pdf` |
| `uploadSignature($id, $base64Pdf)` | PUT | `/credit-notes/{id}/upload-signature` |

### `/credit-note-items`

| Method | HTTP | Path |
|---|---|---|
| `list($creditNoteId, $filters?)` | GET | `/credit-note-items?credit_note_id={id}` |
| `listPage($creditNoteId, $filters?)` / `iterateAll($creditNoteId, $filters?, $pageSize?)` | GET | `/credit-note-items?credit_note_id={id}` (paginated) |
| `get($id)` / `getOrFail($id)` | GET | `/credit-note-items/{id}` |
| `create($creditNoteId, $options)` | POST | `/credit-note-items` |
| `update($id, $options)` | PUT | `/credit-note-items/{id}` |
| `delete($id)` | DELETE | `/credit-note-items/{id}` |

### `/credit-note-comments`

| Method | HTTP | Path |
|---|---|---|
| `list($creditNoteId, $actionKeys?, $filters?)` | GET | `/credit-note-comments?credit_note_id={id}` |
| `listPage(…)` / `iterateAll(…, $pageSize?)` | GET | `/credit-note-comments?credit_note_id={id}` (paginated) |
| `get($id)` / `getOrFail($id)` | GET | `/credit-note-comments/{id}` |
| `create($creditNoteId, $options)` | POST | `/credit-note-comments` |
| `delete($id)` | DELETE | `/credit-note-comments/{id}` |

### `/credit-note-tags`

| Method | HTTP | Path |
|---|---|---|
| `list($creditNoteId, $filters?)` | GET | `/credit-note-tags?credit_note_id={id}` |
| `listPage($creditNoteId, $filters?)` / `iterateAll($creditNoteId, $filters?, $pageSize?)` | GET | `/credit-note-tags?credit_note_id={id}` (paginated) |
| `cloud()` | GET | `/credit-note-tags` |
| `get($id)` / `getOrFail($id)` | GET | `/credit-note-tags/{id}` |
| `create($creditNoteId, $options)` | POST | `/credit-note-tags` |
| `delete($id)` | DELETE | `/credit-note-tags/{id}` |

### `/credit-note-payments`

| Method | HTTP | Path |
|---|---|---|
| `list($creditNoteId?, $filters?)` | GET | `/credit-note-payments` |
| `listPage($creditNoteId?, $filters?)` / `iterateAll($creditNoteId?, $filters?, $pageSize?)` | GET | `/credit-note-payments` (paginated) |
| `get($id)` / `getOrFail($id)` | GET | `/credit-note-payments/{id}` |
| `create($creditNoteId, $options)` | POST | `/credit-note-payments` |
| `delete($id)` | DELETE | `/credit-note-payments/{id}` |

## Methods

### Credit notes

#### `list(CreditNoteFilter|array $filters = []): list<CreditNote>`

Billomat filters by `client_id`, `contact_id`, `status`, `from`, `to`, `tags`, `invoice_id` and more.

The typed filter `CreditNoteFilter` is recommended. Its camelCase fields are `clientId`, `contactId`, `creditNoteNumber`, `status` (`list<CreditNoteStatus>`), `from`/`to` (`DateTimeImmutable`), `label`, `intro`, `note`, `tags` and `articleId`. Multi-value fields are lists and are sent comma-separated. On top of that there are `orderBy`, `page`, `perPage` and `extra` for further query parameters (e.g. `invoice_id`). A plain array still works: it is passed through to the query string as is, and array values are sent as `key[]=…`.

```php
use Justpilot\Billomat\Api\CreditNoteFilter;
use Justpilot\Billomat\Api\OrderBy;
use Justpilot\Billomat\Model\Enum\CreditNoteStatus;

$drafts = $billomat->creditNotes->list(new CreditNoteFilter(
    status: [CreditNoteStatus::DRAFT],
    orderBy: OrderBy::desc('created'),
));
```

`listPage()` takes the same filters and returns a `Page` with pagination metadata; `iterateAll()` walks through all pages lazily. See [Pagination and filtering](../concepts/pagination-and-filtering.md).

#### `get(int $id): ?CreditNote`

Returns `null` on 404. Embedded `credit-note-items` and `taxes` are hydrated automatically. `getOrFail()` throws a `NotFoundException` instead of returning `null`.

#### `create(CreditNoteCreateOptions $options): CreditNote`

Line items can be passed directly in the `create()` call via `items: […]` or `withItem()`. They end up under `credit-note-items.credit-note-item` in the payload.

```php
use Justpilot\Billomat\Api\CreditNoteCreateOptions;
use Justpilot\Billomat\Api\CreditNoteItemCreateOptions;
use Justpilot\Billomat\Model\Money;

$opts = new CreditNoteCreateOptions(
    clientId: 12345,
    title: 'Correction of invoice 2026-0042',
    invoiceId: 9876,
    date: new DateTimeImmutable('2026-06-02'),
);

$item = new CreditNoteItemCreateOptions(
    quantity: 1.0,
    unitPrice: Money::of('-19.90'),
    title: 'Hosting refund (May)',
);
$opts = $opts->withItem($item);

$creditNote = $billomat->creditNotes->create($opts);
```

#### `update(int $id, CreditNoteUpdateOptions $options): CreditNote`

Only advisable in status `DRAFT`. Line items are **not** accepted here; use `$billomat->creditNotes->items` instead.

#### `complete(int $id, ?int $templateId = null): void`

Status `DRAFT → OPEN`. Optionally pass a specific `templateId` for the PDF; otherwise the account's default template is used. Errors are thrown as exceptions.

#### `cancel(int $id): void` / `uncancel(int $id): void`

`cancel()` sets the status to `CANCELED`; `uncancel()` reverts it to `OPEN`. Both only make sense on completed credit notes.

#### `email(int $id, ?EmailOptions $options = null): void`

Without options, Billomat uses the client's defaults and the configured email template. Throws a `ValidationException` if no recipient is configured.

```php
use Justpilot\Billomat\Api\EmailOptions;

$mail = new EmailOptions(
    to: ['client@example.com'],
    bcc: ['accounting@mycompany.com'],
    subject: 'Your credit note',
    body: 'Please find attached the credit note for invoice 2026-0042.',
);

$billomat->creditNotes->email($creditNote->id, $mail);
```

#### `pdf(int $id, ?PdfType $type = null): CreditNotePdf` / `pdfContent(int $id, ?PdfType $type = null): string`

`pdf()` returns a `CreditNotePdf` model (metadata plus base64 body). `pdfContent()` appends `?format=pdf` and returns the binary PDF content (`UnexpectedResponseException` if the body isn't a PDF).

```php
$pdf = $billomat->creditNotes->pdf($creditNote->id);                 // model
$raw = $billomat->creditNotes->pdfContent($creditNote->id);        // string (PDF binary)
```

#### `uploadSignature(int $id, string $base64Pdf): void`

Attaches a PDF signed by the client to the credit note.

#### `delete(int $id): void`

Only works on credit notes in status `DRAFT`.

### Credit note items

```php
use Justpilot\Billomat\Api\CreditNoteItemCreateOptions;
use Justpilot\Billomat\Api\CreditNoteItemUpdateOptions;
use Justpilot\Billomat\Model\Money;

$item = $billomat->creditNotes->items->create(
    $creditNote->id,
    new CreditNoteItemCreateOptions(quantity: 1.0, unitPrice: Money::of('-19.90')),
);

$items = $billomat->creditNotes->items->list($creditNote->id);
$billomat->creditNotes->items->update($item->id, new CreditNoteItemUpdateOptions(unitPrice: Money::of('-24.90')));
$billomat->creditNotes->items->delete($item->id);
```

`create()` adds `credit_note_id` to the payload automatically; `CreditNoteItemCreateOptions` itself has no such field. `update()` takes `CreditNoteItemUpdateOptions`: every field is optional, only the fields you set are sent and Billomat keeps the others.

### Credit note comments

```php
use Justpilot\Billomat\Api\CreditNoteCommentCreateOptions;
use Justpilot\Billomat\Model\Enum\CreditNoteCommentActionKey;

$comment = $billomat->creditNotes->comments->create(
    $creditNote->id,
    new CreditNoteCommentCreateOptions(
        comment: 'Client confirmed the refund by email.',
    ),
);

// Only EMAIL/PAYMENT entries of the activity log
$entries = $billomat->creditNotes->comments->list(
    $creditNote->id,
    [CreditNoteCommentActionKey::EMAIL, CreditNoteCommentActionKey::PAYMENT],
);
```

Billomat keeps both free-form user comments and system audit entries (status changes, payments, emails sent) in the same endpoint. `$actionKeys` filters by type; the values are sent comma-separated (`actionkey=EMAIL,PAYMENT`).

### Credit note tags

```php
use Justpilot\Billomat\Api\CreditNoteTagCreateOptions;

$billomat->creditNotes->tags->create(
    $creditNote->id,
    new CreditNoteTagCreateOptions(name: 'refund'),
);

$tags = $billomat->creditNotes->tags->list($creditNote->id);
$cloud = $billomat->creditNotes->tags->cloud(); // aggregated, with count
```

There is no update. To rename a tag, delete it and create it again.

### Credit note payments

```php
use Justpilot\Billomat\Api\CreditNotePaymentCreateOptions;
use Justpilot\Billomat\Model\Enum\PaymentType;
use Justpilot\Billomat\Model\Money;

$payment = new CreditNotePaymentCreateOptions(
    amount: Money::of('19.90'),
    type: PaymentType::BANK_TRANSFER,
    date: new DateTimeImmutable('2026-06-05'),
    markCreditNoteAsPaid: true,
);

$billomat->creditNotes->payments->create($creditNote->id, $payment);

$payments = $billomat->creditNotes->payments->list($creditNote->id);
```

The credit note only switches to status `PAID` when a payout is created with `markCreditNoteAsPaid: true` (wire value `1`). Without the flag it stays `OPEN` and keeps a positive `openAmount` (`$creditNote->openAmount?->isPositive()`).

## Write model: `CreditNoteCreateOptions`

Constructor: `new CreditNoteCreateOptions(int $clientId, …)`. `clientId` is required and the first parameter. Every field is a constructor parameter with a default and is passed as a named argument. The object is immutable (`final readonly`); each field has a `with*()` method that returns a modified copy (e.g. `$opts->withTitle('New')`).

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `clientId` | `client_id` | `int` (required) | |
| `contactId` | `contact_id` | `?int` | |
| `address` | `address` | `?string` | multi-line address, overrides the client address |
| `numberPre` | `number_pre` | `?string` | |
| `number` | `number` | `?int` | manual numbering; conflicts with auto-numbering |
| `numberLength` | `number_length` | `?int` | |
| `date` | `date` | `?\DateTimeImmutable` | formatted as `Y-m-d` |
| `title` | `title` | `?string` | |
| `label` | `label` | `?string` | |
| `intro` | `intro` | `?string` | |
| `note` | `note` | `?string` | |
| `reduction` | `reduction` | `?string` | absolute or with `%` suffix |
| `currencyCode` | `currency_code` | `?string` | ISO 4217, e.g. `EUR` |
| `netGross` | `net_gross` | `?NetGross` | `NET` / `GROSS` / `SETTINGS` |
| `quote` | `quote` | `?float` | exchange rate for foreign currency |
| `invoiceId` | `invoice_id` | `?int` | source invoice of a corrective credit note |
| `freeTextId` | `free_text_id` | `?int` | |
| `templateId` | `template_id` | `?int` | |

Line items are passed as a list via the constructor parameter `items` (`items: [new CreditNoteItemCreateOptions(…), …]`) and are then available in the `$items` property. `withItem()` returns a copy with one additional item. `toArray()` renders them as `credit-note-items.credit-note-item`.

## Write model: `CreditNoteUpdateOptions`

Mirrors `CreditNoteCreateOptions`, but `clientId` is optional (nullable) and there is no `items`. Fields left at `null` are stripped from the payload and remain unchanged on the server. `Clear::Field` instead sends the field as JSON `null`, and Billomat empties it, e.g. `new CreditNoteUpdateOptions(note: Clear::Field)`.

## Write model: `CreditNoteItemCreateOptions`

Constructor: `new CreditNoteItemCreateOptions(float $quantity, Money $unitPrice)`. Both values are required and are **sent even when they are `0`** (special case in the `toArray()` filter).

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `quantity` | `quantity` | `float` (required) | |
| `unitPrice` | `unit_price` | `Money` (required) | may be negative, common for refunds |
| `type` | `type` | `?ItemType` | shared with the invoice domain |
| `articleId` | `article_id` | `?int` | |
| `title` | `title` | `?string` | |
| `description` | `description` | `?string` | |
| `unit` | `unit` | `?string` | |
| `taxName` | `tax_name` | `?string` | |
| `taxRate` | `tax_rate` | `?float` | |
| `taxChangedManually` | `tax_changed_manually` | `?bool` | |
| `reduction` | `reduction` | `?string` | |
| `position` | `position` | `?int` | sort order in the PDF |

## Write model: `CreditNoteCommentCreateOptions`

Constructor: `new CreditNoteCommentCreateOptions(string $comment)`. The credit note ID isn't part of the options: pass it as the first argument of `create()`, which adds `credit_note_id` to the payload.

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `comment` | `comment` | `string` (required) | |
| `actionkey` | `actionkey` | `?CreditNoteCommentActionKey` | usually set by Billomat; set manually only for audit markers |

## Write model: `CreditNoteTagCreateOptions`

The credit note ID isn't part of the options: pass it as the first argument of `create()`, which adds `credit_note_id` to the payload.

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `name` | `name` | `string` (required) | |

## Write model: `CreditNotePaymentCreateOptions`

Constructor: `new CreditNotePaymentCreateOptions(Money $amount)`. The credit note ID isn't part of the options: pass it as the first argument of `create()`, which adds `credit_note_id` to the payload.

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `amount` | `amount` | `Money` (required) | always sent |
| `date` | `date` | `?\DateTimeImmutable` | `Y-m-d` |
| `type` | `type` | `?PaymentType` | same enum as invoice payments |
| `comment` | `comment` | `?string` | |
| `transactionPurpose` | `transaction_purpose` | `?string` | bank transfer reference |
| `markCreditNoteAsPaid` | `mark_credit_note_as_paid` | `bool` | **always** sent, as `1` or `0` |

## Write model: `EmailOptions`

Shared class for sending every document type by email (invoices, offers, confirmations, credit notes, delivery notes, reminders, letters); the full description is on the [Invoices](invoices.md#write-model-emailoptions) page.

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `emailTemplateId` | `email_template_id` | `?int` | |
| `from` | `from` | `?string` | |
| `to` | `recipients.to` | `list<string>` | |
| `cc` | `recipients.cc` | `list<string>` | |
| `bcc` | `recipients.bcc` | `list<string>` | |
| `subject` | `subject` | `?string` | |
| `body` | `body` | `?string` | |
| `filename` | `filename` | `?string` | name of the attached PDF |
| `attachments` | `attachments.attachment` | `list<array{filename,mimetype,base64file}>` | additional attachments |

Empty recipient arrays are left out of the payload so Billomat falls back to the email template's defaults.

## Read model: `CreditNote`

`final readonly class CreditNote`.

| Property | Type |
|---|---|
| `id` | `int` |
| `clientId` | `int` |
| `contactId` | `?int` |
| `created` | `?\DateTimeImmutable` |
| `creditNoteNumber` | `?string` (formatted number including prefix) |
| `number`, `numberPre`, `numberLength` | `?int`, `?string`, `?int` |
| `status` | `?CreditNoteStatus` |
| `date` | `?\DateTimeImmutable` |
| `address`, `title`, `label`, `intro`, `note` | `?string` |
| `totalGross`, `totalNet` | `?Money` |
| `netGross` | `?NetGross` |
| `reduction` | `?string` |
| `totalGrossUnreduced`, `totalNetUnreduced` | `?Money` |
| `paidAmount`, `openAmount` | `?Money` |
| `currencyCode` | `?string` |
| `quote` | `?float` |
| `invoiceId` | `?int` |
| `freeTextId`, `templateId` | `?int` |
| `taxes` | `list<TaxSummary>` (`name`, `rate`, `amount` as `Money`) |
| `items` | `list<CreditNoteItem>` |
| `updated` | `?\DateTimeImmutable` |
| `totalReduction` | `?Money` |
| `customfield` | `?string` (free-form extra field, see [Custom meta attributes](../concepts/custom-meta-attributes.md)) |
| `extra` | `array<string, mixed>` (raw fields the SDK doesn't map yet, see [Models](../concepts/models.md)) |

## Read model: `CreditNoteItem`

| Property | Type |
|---|---|
| `id` | `int` |
| `creditNoteId` | `?int` |
| `articleId` | `?int` |
| `position` | `?int` |
| `unit` | `?string` |
| `quantity`, `unitPrice` | `float`, `Money` |
| `taxName`, `taxRate`, `taxChangedManually` | `?string`, `?float`, `?bool` |
| `title`, `description`, `reduction` | `?string` |
| `type` | `?ItemType` |
| `totalGross`, `totalNet` | `?Money` |
| `totalGrossUnreduced`, `totalNetUnreduced` | `?Money` |
| `taxRateReverseCharge` | `?float` |
| `customfield` | `?string` (free-form extra field, see [Custom meta attributes](../concepts/custom-meta-attributes.md)) |
| `extra` | `array<string, mixed>` |

## Read model: `CreditNoteComment`

| Property | Type |
|---|---|
| `id` | `int` |
| `creditNoteId` | `int` |
| `comment` | `?string` |
| `created` | `?\DateTimeImmutable` |
| `userId` | `?int` |
| `actionkey` | `?CreditNoteCommentActionKey` |
| `actionkeyRaw` | `?string` (raw value in case Billomat sends an unknown key) |
| `public` | `?bool` |
| `byClient` | `?bool` |
| `emailId` | `?int` |
| `clientId` | `?int` |
| `customfield` | `?string` (free-form extra field, see [Custom meta attributes](../concepts/custom-meta-attributes.md)) |
| `extra` | `array<string, mixed>` |

## Read model: `CreditNoteTag`

| Property | Type |
|---|---|
| `id` | `int` |
| `creditNoteId` | `int` |
| `name` | `string` |
| `customfield` | `?string` (free-form extra field, see [Custom meta attributes](../concepts/custom-meta-attributes.md)) |
| `extra` | `array<string, mixed>` |

## Read model: `CreditNoteTagCloudEntry`

| Property | Type |
|---|---|
| `id` | `?int` |
| `name` | `string` |
| `count` | `int` |
| `extra` | `array<string, mixed>` |

## Read model: `CreditNotePayment`

| Property | Type |
|---|---|
| `id` | `int` |
| `creditNoteId` | `int` |
| `date` | `?\DateTimeImmutable` |
| `amount` | `Money` |
| `type` | `?PaymentType` |
| `comment`, `transactionPurpose` | `?string` |
| `created` | `?\DateTimeImmutable` |
| `userId` | `?int` |
| `currencyCode` | `?string` |
| `quote` | `?float` |
| `paymentGroup` | `?string` |
| `finapiTransactionId` | `?string` (finAPI bank reconciliation) |
| `finapiTransactionAmount` | `?Money` (finAPI bank reconciliation) |
| `documentTitle` | `?string` |
| `customfield` | `?string` (free-form extra field, see [Custom meta attributes](../concepts/custom-meta-attributes.md)) |
| `extra` | `array<string, mixed>` |

## Read model: `CreditNotePdf`

`final class CreditNotePdf` (not `readonly`, since it is designed as a wrapper).

| Property | Type |
|---|---|
| `id`, `creditNoteId` | `int` |
| `created` | `?\DateTimeImmutable` |
| `filename`, `mimeType` | `string` |
| `fileSize` | `int` |
| `base64file` | `string` |

`getBinary(): string` decodes `base64file` back into raw bytes.

## Enums used

- [`CreditNoteStatus`](../../src/Model/Enum/CreditNoteStatus.php): `DRAFT`, `OPEN`, `PAID`, `CANCELED`.
- [`CreditNoteCommentActionKey`](../../src/Model/Enum/CreditNoteCommentActionKey.php): `CREATE`, `EDIT`, `OPEN`, `COMPLETE`, `CANCEL`, `UNCANCEL`, `CHANGE_STATUS`, `PAYMENT`, `EMAIL`, `MAIL`, `COMMENT`.
- [`NetGross`](../../src/Model/Enum/NetGross.php): `NET`, `GROSS`, `SETTINGS`.
- [`ItemType`](../../src/Model/Enum/ItemType.php): shared item types (`PRODUCT`, `SERVICE`).
- [`PaymentType`](../../src/Model/Enum/PaymentType.php): shared payment types (`BANK_TRANSFER`, `PAYPAL`, `CASH`, …).
- [`PdfType`](../../src/Model/Enum/PdfType.php): `pdf()` accepts the same PDF variants as for invoices.

## Pitfalls

- **`delete()` isn't limited to `DRAFT`.** Billomat's API deletes credit notes in any status. Once completed, prefer `cancel()`: the document is numbered, and deleting it leaves a gap in the number sequence (GoBD).
- **`update()` only in DRAFT.** Once the credit note is completed, Billomat ignores most fields or responds with 422. Before editing, consider `cancel()` → `update()` → `complete()`.
- **Items in the update payload are ignored.** `CreditNoteUpdateOptions` deliberately has no `items` field. Change line items through `$billomat->creditNotes->items`.
- **`markCreditNoteAsPaid` is always in the payload.** Unlike other fields, `mark_credit_note_as_paid` is not filtered out: the default `false` is sent as `0`. With `true`, the status flips to `PAID` once the sum of payments reaches the gross total.
- **`quantity` and `unitPrice` stay in the payload even when they are `0`.** `CreditNoteItemCreateOptions::toArray()` explicitly whitelists them against the `array_filter`.
- **Negative amounts on items.** A credit note offsets an invoice; the usual practice is a negative `unitPrice` (e.g. `-19.90`). Billomat accepts that directly.
- **Setting `number` manually conflicts with auto-numbering.** If the account uses automatic numbers, a manual `number` leads to a `ValidationException` on `complete()`.
- **`uncancel()` reverts to `OPEN`, not to `DRAFT`.** An accidentally canceled credit note can be reactivated, but it is not editable afterwards.
- **`actionkey` in the comment filter is CSV, not an array.** `list($id, [...])` sends multiple action keys as a comma-separated string, not as `actionkey[]=…`.
- **Tag cloud and list share the same endpoint.** `GET /credit-note-tags` without `credit_note_id` returns the aggregated cloud (`tag` wrapper); with `credit_note_id` it returns the actual tags (`credit-note-tag` wrapper). The SDK splits this into `cloud()` and `list()`.

## End-to-end example

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\CreditNoteCreateOptions;
use Justpilot\Billomat\Api\CreditNoteItemCreateOptions;
use Justpilot\Billomat\Api\CreditNotePaymentCreateOptions;
use Justpilot\Billomat\Api\CreditNoteTagCreateOptions;
use Justpilot\Billomat\Api\EmailOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\Enum\PaymentType;
use Justpilot\Billomat\Model\Money;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

// 1) Create a corrective credit note for invoice 9876
$opts = new CreditNoteCreateOptions(
    clientId: 12345,
    invoiceId: 9876,
    title: 'Correction of invoice 2026-0042',
    date: new DateTimeImmutable('2026-06-02'),
    intro: 'We are refunding the pro-rata hosting fee for May.',
);

$item = new CreditNoteItemCreateOptions(
    quantity: 1.0,
    unitPrice: Money::of('-19.90'),
    title: 'Web hosting Basic refund (May)',
);
$opts = $opts->withItem($item);

$creditNote = $billomat->creditNotes->create($opts);
printf("Credit note #%d created (status: %s)\n",
    $creditNote->id,
    $creditNote->status?->label() ?? 'unknown',
);

// 2) Complete: DRAFT → OPEN
$billomat->creditNotes->complete($creditNote->id);

// 3) Add a tag
$billomat->creditNotes->tags->create(
    $creditNote->id,
    new CreditNoteTagCreateOptions(name: 'refund'),
);

// 4) Send by email
$mail = new EmailOptions(
    to: ['client@example.com'],
    bcc: ['accounting@mycompany.com'],
    subject: 'Your credit note for invoice 2026-0042',
    body: "Hello,\n\nplease find the credit note attached.\n\nBest regards",
);
$billomat->creditNotes->email($creditNote->id, $mail);

// 5) Record the payout: OPEN → PAID
$payment = new CreditNotePaymentCreateOptions(
    amount: Money::of('19.90'),
    type: PaymentType::BANK_TRANSFER,
    date: new DateTimeImmutable('2026-06-05'),
    transactionPurpose: 'Hosting refund May',
    markCreditNoteAsPaid: true,
);

$billomat->creditNotes->payments->create($creditNote->id, $payment);

// 6) Download the PDF
$pdfBinary = $billomat->creditNotes->pdfContent($creditNote->id);
file_put_contents("credit-note-{$creditNote->id}.pdf", $pdfBinary);
```
