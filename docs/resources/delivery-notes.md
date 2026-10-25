<!-- Source: https://www.billomat.com/api/lieferscheine/ -->

# Delivery Notes (Lieferscheine)

API wrapper for delivery notes under `/delivery-notes` and their three sub-resources (`/delivery-note-items`, `/delivery-note-comments`, `/delivery-note-tags`).

## Access

```php
$billomat->deliveryNotes             // delivery notes
$billomat->deliveryNotes->items      // line items
$billomat->deliveryNotes->comments   // comments / audit trail
$billomat->deliveryNotes->tags       // tags
```

## Model

A delivery note documents the physical handover of a delivery. Compared to an [invoice](invoices.md) it carries little monetary data: the read model has no total or price fields, and the document itself creates no payment obligation. The status lifecycle is a single step:

```
DRAFT  ──complete()──▶  CREATED
```

Unlike [confirmations](confirmations.md), the API offers no further status transitions: `PUT /delivery-notes/{id}/cancel` and `/uncancel` answer 400 ("cancellation not supported"), `/clear`, `/unclear` and `/undo` answer 404. The enum still knows `OPEN`, `CLEARED` and `CANCELED`, which Billomat may report for documents changed in the web app. A delivery note can be linked to a source invoice (`invoiceId`), confirmation (`confirmationId`) and/or offer (`offerId`).

## Endpoint overview

### `/delivery-notes`

| Method | HTTP | Path |
|---|---|---|
| `list($filters?)` | GET | `/delivery-notes` |
| `listPage($filters?)` | GET | `/delivery-notes` (one page plus pagination metadata) |
| `iterateAll($filters?, $pageSize?)` | GET | `/delivery-notes` (all pages, lazily) |
| `get($id)` / `getOrFail($id)` | GET | `/delivery-notes/{id}` |
| `create($options)` | POST | `/delivery-notes` |
| `update($id, $options)` | PUT | `/delivery-notes/{id}` |
| `delete($id)` | DELETE | `/delivery-notes/{id}` |
| `complete($id, $templateId?)` | PUT | `/delivery-notes/{id}/complete` |
| `email($id, $options?)` | POST | `/delivery-notes/{id}/email` |
| `uploadSignature($id, $base64Pdf)` | PUT | `/delivery-notes/{id}/upload-signature` |
| `pdf($id, $type?, $rawPdf?)` | GET | `/delivery-notes/{id}/pdf` |

### `/delivery-note-items`

| Method | HTTP | Path |
|---|---|---|
| `list($deliveryNoteId, $query?)` | GET | `/delivery-note-items?delivery_note_id={id}` |
| `get($id)` / `getOrFail($id)` | GET | `/delivery-note-items/{id}` |
| `create($deliveryNoteId, $options)` | POST | `/delivery-note-items` |
| `update($id, $options)` | PUT | `/delivery-note-items/{id}` |
| `delete($id)` | DELETE | `/delivery-note-items/{id}` |

### `/delivery-note-comments`

| Method | HTTP | Path |
|---|---|---|
| `list($deliveryNoteId, $actionKeys?)` | GET | `/delivery-note-comments?delivery_note_id={id}` |
| `get($id)` / `getOrFail($id)` | GET | `/delivery-note-comments/{id}` |
| `create($options)` | POST | `/delivery-note-comments` |
| `delete($id)` | DELETE | `/delivery-note-comments/{id}` |

### `/delivery-note-tags`

| Method | HTTP | Path |
|---|---|---|
| `list($deliveryNoteId)` | GET | `/delivery-note-tags?delivery_note_id={id}` |
| `cloud()` | GET | `/delivery-note-tags` |
| `get($id)` / `getOrFail($id)` | GET | `/delivery-note-tags/{id}` |
| `create($options)` | POST | `/delivery-note-tags` |
| `delete($id)` | DELETE | `/delivery-note-tags/{id}` |

## Methods

### Delivery notes

#### `list(DeliveryNoteFilter|array $filters = []): list<DeliveryNote>`

Filters according to the Billomat docs: `client_id`, `contact_id`, `status`, `from`, `to`, `number_pre`, `tags`, `invoice_id`, `confirmation_id`, `order_by`. If Billomat returns a single match as an object, the SDK normalizes it to a list.

The typed filter `DeliveryNoteFilter` is recommended. Its camelCase fields are `clientId`, `contactId`, `deliveryNoteNumber`, `status` (`list<DeliveryNoteStatus>`), `from`/`to` (`DateTimeImmutable`), `label`, `intro`, `note`, `tags` and `articleId`. Multi-value fields are lists and are sent comma-separated. On top of that there are `orderBy`, `page`, `perPage` and `extra` for further query parameters (e.g. `invoice_id`, `confirmation_id`, `number_pre`). A plain array still works.

```php
use Justpilot\Billomat\Api\DeliveryNoteFilter;
use Justpilot\Billomat\Api\OrderBy;
use Justpilot\Billomat\Model\Enum\DeliveryNoteStatus;

$openDeliveries = $billomat->deliveryNotes->list(new DeliveryNoteFilter(
    status: [DeliveryNoteStatus::OPEN],
    orderBy: OrderBy::desc('date'),
));
```

`listPage()` takes the same filters and returns a `Page` with pagination metadata; `iterateAll()` walks through all pages lazily. See [Pagination and filtering](../concepts/pagination-and-filtering.md).

#### `get(int $id): ?DeliveryNote`

Returns `null` on 404. Embedded `delivery-note-items` and `taxes` are hydrated automatically. `getOrFail()` throws a `NotFoundException` instead of returning `null`.

#### `create(DeliveryNoteCreateOptions $options): DeliveryNote`

Line items can be passed directly in the `create()` call via `items: […]` or `withItem()`; they end up as `delivery-note-items.delivery-note-item` in the payload. `invoiceId`, `confirmationId` and `offerId` link the delivery note to preceding documents.

```php
use Justpilot\Billomat\Api\DeliveryNoteCreateOptions;
use Justpilot\Billomat\Api\DeliveryNoteItemCreateOptions;
use Justpilot\Billomat\Model\Money;

$opts = new DeliveryNoteCreateOptions(
    clientId: 12345,
    invoiceId: 999,
    date: new DateTimeImmutable('today'),
    title: 'Delivery note',
    items: [
        new DeliveryNoteItemCreateOptions(quantity: 3.0, unitPrice: Money::of('49.90')),
    ],
);

$note = $billomat->deliveryNotes->create($opts);
```

#### `update(int $id, DeliveryNoteUpdateOptions $options): DeliveryNote`

Fully editable only in `DRAFT`. Line items can't be changed here; use `$billomat->deliveryNotes->items`.

#### `complete(int $id, ?int $templateId = null): void`

Completes the draft. Assigns the final `delivery_note_number` and generates the PDF.

#### `delete(int $id): void`

Deletes the delivery note. There is no `cancel()` for delivery notes; in the sandbox, Billomat also deleted completed delivery notes.

#### `email(int $id, ?EmailOptions $options = null): void`

Sends the delivery note by email. Without options, Billomat uses the account defaults; the PDF is attached automatically.

#### `uploadSignature(int $id, string $base64Pdf): void`

Uploads a signed PDF version. Typical use case: the driver photographs the signed proof of delivery, and the PDF is uploaded base64-encoded.

#### `pdf(int $id, ?PdfType $type = null, bool $rawPdf = false): DeliveryNotePdf|string`

Returns a `DeliveryNotePdf` (base64 container), or the raw bytes with `$rawPdf = true`.

```php
use Justpilot\Billomat\Model\Enum\PdfType;

$pdf = $billomat->deliveryNotes->pdf($note->id, PdfType::SIGNED);
file_put_contents('delivery-note.pdf', $pdf->getBinary());
```

### Delivery note items

```php
$items = $billomat->deliveryNotes->items->list($note->id);

$item = $billomat->deliveryNotes->items->create(
    $note->id,
    new DeliveryNoteItemCreateOptions(quantity: 2.0, unitPrice: Money::of('49.90')),
);

$billomat->deliveryNotes->items->update($item->id, $opts);
$billomat->deliveryNotes->items->delete($item->id);
```

`create()` adds `delivery_note_id` automatically; the options class itself has no such field. `update()` uses the same class as `create()`.

### Delivery note comments

```php
use Justpilot\Billomat\Api\DeliveryNoteCommentCreateOptions;
use Justpilot\Billomat\Model\Enum\DeliveryNoteCommentActionKey;

$comments = $billomat->deliveryNotes->comments->list(
    $note->id,
    actionKeys: [DeliveryNoteCommentActionKey::COMPLETE, DeliveryNoteCommentActionKey::EMAIL],
);

$billomat->deliveryNotes->comments->create(
    new DeliveryNoteCommentCreateOptions(
        deliveryNoteId: $note->id,
        comment: 'Handed over to warehouse South.',
    ),
);
```

### Delivery note tags

```php
use Justpilot\Billomat\Api\DeliveryNoteTagCreateOptions;

$tag = $billomat->deliveryNotes->tags->create(
    new DeliveryNoteTagCreateOptions(deliveryNoteId: $note->id, name: 'freight-shipping'),
);

$tags  = $billomat->deliveryNotes->tags->list($note->id);
$cloud = $billomat->deliveryNotes->tags->cloud();
```

## Write model: `DeliveryNoteCreateOptions`

Constructor: `new DeliveryNoteCreateOptions(int $clientId, …)`. `clientId` is required and the first parameter. Every field is a constructor parameter with a default and is passed as a named argument. The object is immutable (`final readonly`); each field has a `with*()` method that returns a modified copy (e.g. `$opts->withTitle('New')`).

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `clientId` | `client_id` | `int` | required (constructor) |
| `contactId` | `contact_id` | `?int` | |
| `address` | `address` | `?string` | |
| `numberPre`, `number`, `numberLength` | snake_case equivalent | `?string`/`?int`/`?int` | |
| `date` | `date` | `?\DateTimeImmutable` | serialized as `Y-m-d` |
| `title`, `label`, `intro`, `note`, `reduction` | snake_case equivalent | `?string` | |
| `currencyCode` | `currency_code` | `?string` | |
| `netGross` | `net_gross` | `?NetGross` | |
| `quote` | `quote` | `?float` | |
| `invoiceId` | `invoice_id` | `?int` | source invoice |
| `confirmationId` | `confirmation_id` | `?int` | source confirmation |
| `freeTextId` | `free_text_id` | `?int` | |
| `templateId` | `template_id` | `?int` | |
| `offerId` | `offer_id` | `?int` | source offer |

Unlike `OfferCreateOptions`/`ConfirmationCreateOptions`, there is **no** `discountRate`/`discountDays`/`discountDate`/`validityDays`: a delivery note has no payment or validity context of its own.

### Line items

Line items are passed as a list via the constructor parameter `items` (`items: [new DeliveryNoteItemCreateOptions(…), …]`) and are then available in the `$items` property. `withItem()` returns a copy with one additional item. `toArray()` renders them as `delivery-note-items.delivery-note-item`.

## Write model: `DeliveryNoteUpdateOptions`

Mirrors `DeliveryNoteCreateOptions`, but `clientId` is optional and there is no `items`. `null` means "don't send", so the value stays unchanged. `Clear::Field` (`Justpilot\Billomat\Api\Clear`) sends the field as JSON `null`, and Billomat empties it, e.g. `new DeliveryNoteUpdateOptions(note: Clear::Field)`.

| Property | Billomat field | Type |
|---|---|---|
| `clientId` | `client_id` | `?int` |
| `contactId`, `numberPre`, `number`, `numberLength`, `invoiceId`, `confirmationId`, `offerId`, `freeTextId`, `templateId` | snake_case equivalent | `?int`/`?string` |
| `address`, `title`, `label`, `intro`, `note`, `reduction`, `currencyCode` | snake_case equivalent | `?string` |
| `date` | `date` | `?\DateTimeImmutable` |
| `netGross` | `net_gross` | `?NetGross` |
| `quote` | `quote` | `?float` |

Every field additionally accepts `Clear::Field`.

## Write model: `DeliveryNoteItemCreateOptions`

Constructor: `new DeliveryNoteItemCreateOptions(float $quantity, Money $unitPrice)`. Both are required and are sent even when they are `0`.

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `quantity` | `quantity` | `float` | required (constructor) |
| `unitPrice` | `unit_price` | `Money` | required (constructor) |
| `type` | `type` | `?ItemType` | |
| `articleId` | `article_id` | `?int` | |
| `title`, `description`, `unit` | snake_case equivalent | `?string` | |
| `taxName`, `taxRate`, `taxChangedManually` | snake_case equivalent | `?string`/`?float`/`?bool` | |
| `reduction` | `reduction` | `?string` | |
| `position` | `position` | `?int` | |

`ItemsApi::update()` uses the same class; there is no separate update type.

## Write model: `DeliveryNoteCommentCreateOptions`

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `deliveryNoteId` | `delivery_note_id` | `int` | required (constructor) |
| `comment` | `comment` | `string` | required (constructor) |
| `actionkey` | `actionkey` | `?DeliveryNoteCommentActionKey` | usually only for system actions |

## Write model: `DeliveryNoteTagCreateOptions`

| Property | Billomat field | Type |
|---|---|---|
| `deliveryNoteId` | `delivery_note_id` | `int` (required) |
| `name` | `name` | `string` (required) |

## Write model: `EmailOptions`

Shared class for sending every document type by email (invoices, offers, confirmations, credit notes, delivery notes, reminders, letters); the full description is on the [Invoices](invoices.md#write-model-emailoptions) page. No required fields.

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `emailTemplateId` | `email_template_id` | `?int` | |
| `from` | `from` | `?string` | |
| `to`, `cc`, `bcc` | `recipients.{to,cc,bcc}` | `list<string>` | empty arrays are left out |
| `subject`, `body`, `filename` | snake_case equivalent | `?string` | |
| `attachments` | `attachments.attachment[]` | `list<array{filename,mimetype,base64file}>` | |

## Read model: `DeliveryNote`

`final readonly class DeliveryNote`.

| Property | Type |
|---|---|
| `id` | `?int` |
| `clientId` | `int` |
| `contactId` | `?int` |
| `created` | `?\DateTimeImmutable` |
| `deliveryNoteNumber` | `?string`, only set after `complete()` |
| `number`, `numberPre`, `numberLength` | `?int`/`?string`/`?int` |
| `status` | `?DeliveryNoteStatus` |
| `date` | `?\DateTimeImmutable` |
| `address` | `?string` |
| `title`, `label`, `intro`, `note` | `?string` |
| `invoiceId` | `?int` |
| `confirmationId` | `?int` |
| `freeTextId`, `templateId` | `?int` |
| `taxes` | `list<TaxSummary>` (`name`, `rate`, `amount` as `Money`) |
| `items` | `list<DeliveryNoteItem>` |
| `updated` | `?\DateTimeImmutable` |
| `offerId` | `?int` |
| `customfield` | `?string` (free-form extra field, see [Custom meta attributes](../concepts/custom-meta-attributes.md)) |
| `extra` | `array<string, mixed>` (raw fields the SDK doesn't map yet, see [Models](../concepts/models.md)) |

## Read model: `DeliveryNoteItem`

`final readonly`.

| Property | Type |
|---|---|
| `id`, `deliveryNoteId`, `articleId`, `position` | `?int` |
| `unit` | `?string` |
| `quantity` | `float` |
| `taxChangedManually` | `?bool` |
| `title`, `description` | `?string` |
| `type` | `?ItemType` |
| `customfield` | `?string` (free-form extra field, see [Custom meta attributes](../concepts/custom-meta-attributes.md)) |
| `extra` | `array<string, mixed>` |

## Read model: `DeliveryNoteComment`

`final readonly`.

| Property | Type |
|---|---|
| `id` | `?int` |
| `deliveryNoteId` | `int` |
| `comment` | `?string` |
| `created` | `?\DateTimeImmutable` |
| `userId` | `?int` |
| `actionkey` | `?DeliveryNoteCommentActionKey` |
| `actionkeyRaw` | `?string`, raw value for an unknown `actionkey` |
| `public` | `?bool` |
| `byClient` | `?bool` |
| `emailId` | `?int` |
| `clientId` | `?int` |
| `customfield` | `?string` (free-form extra field, see [Custom meta attributes](../concepts/custom-meta-attributes.md)) |
| `extra` | `array<string, mixed>` |

## Read model: `DeliveryNoteTag`

`final readonly`. Fields: `id` (`?int`), `deliveryNoteId` (`int`), `name` (`string`), `customfield` (`?string`), `extra` (`array`).

## Read model: `DeliveryNoteTagCloudEntry`

`final readonly`. Aggregate: `id` (`?int`), `name` (`string`), `count` (`int`), `extra` (`array`).

## Read model: `DeliveryNotePdf`

`final class DeliveryNotePdf` (not `readonly`).

| Property | Type |
|---|---|
| `id` | `int` |
| `deliveryNoteId` | `int` |
| `created` | `?\DateTimeImmutable` |
| `filename` | `string` |
| `mimeType` | `string`, default `application/pdf` |
| `fileSize` | `int` |
| `base64file` | `string` |

`getBinary(): string` decodes `base64file`.

## Enums used

- [`DeliveryNoteStatus`](../../src/Model/Enum/DeliveryNoteStatus.php): `DRAFT`, `OPEN`, `CREATED`, `CLEARED`, `CANCELED`. After `complete()`, Billomat now returns `CREATED`. Has `label()` for the German display name.
- [`DeliveryNoteCommentActionKey`](../../src/Model/Enum/DeliveryNoteCommentActionKey.php): `CREATE`, `EDIT`, `OPEN`, `COMPLETE`, `CANCEL`, `CLEAR`, `CHANGE_STATUS`, `EMAIL`, `MAIL`, `COMMENT`.
- [`ItemType`](../../src/Model/Enum/ItemType.php): `PRODUCT`, `SERVICE`, shared with invoice items.
- [`PdfType`](../../src/Model/Enum/PdfType.php): `SIGNED` (`signed`), `PRINT` (`print`); the wire values are lowercase.
- [`NetGross`](../../src/Model/Enum/NetGross.php): `NET`, `GROSS`, `SETTINGS`.

## Pitfalls

- **A delivery note carries little monetary data.** Billomat returns no total or price fields in the read model, and the document creates no receivable. If you expect a payment, you want `$billomat->invoices` or `$billomat->incomings`.
- **No `discount`/`validity`.** Unlike `OfferCreateOptions`/`ConfirmationCreateOptions`, `DeliveryNoteCreateOptions` has no `discountRate`/`discountDays`/`discountDate`/`validityDays` fields. Don't look for them out of copy-paste habit.
- **`update()` doesn't change line items.** Billomat ignores embedded `delivery-note-items` on PUT. Always use `$billomat->deliveryNotes->items`.
- **No status transitions after `complete()`.** The SDK has no `cancel()`, `clear()` or `undo()` for delivery notes because Billomat's API doesn't support them (400/404). Up to 3.x the SDK had these methods; they always failed.
- **`DeliveryNoteItemCreateOptions` is also the update type.** The constructor requires `quantity` and `unitPrice` even if you only want to change the title.
- **Single results are normalized in `list()`.** For exactly one match, Billomat returns `delivery-note` as an object; the SDK wraps it in a list.
- **Multiple source references are possible.** `invoiceId`, `confirmationId` and `offerId` can all be set at the same time, but Billomat does **not** synchronize the status of the linked documents automatically.
- **`tax_changed_manually` is required for manual `taxRate` values.** Without the flag, Billomat uses the article's or client's default.
- **`DeliveryNotePdf` is not `readonly`**, a pragmatic exception for the binary data container.
- **`uploadSignature()` expects plain base64**, without a `data:` prefix. Typical use case: sending a PDF signed by the driver back from a mobile workflow.

## End-to-end example

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\DeliveryNoteCommentCreateOptions;
use Justpilot\Billomat\Api\DeliveryNoteCreateOptions;
use Justpilot\Billomat\Api\DeliveryNoteItemCreateOptions;
use Justpilot\Billomat\Api\DeliveryNoteTagCreateOptions;
use Justpilot\Billomat\Api\EmailOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\Enum\ItemType;
use Justpilot\Billomat\Model\Enum\PdfType;
use Justpilot\Billomat\Model\Money;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

// 1) Create a delivery note for an existing invoice
$opts = new DeliveryNoteCreateOptions(
    clientId: 12345,
    invoiceId: 999,
    date: new DateTimeImmutable('today'),
    title: 'Delivery note',
    intro: 'Please countersign on receipt.',
);

$item = new DeliveryNoteItemCreateOptions(
    quantity: 3.0,
    unitPrice: Money::of('49.90'),
    title: 'Hardware bundle',
    type: ItemType::PRODUCT,
    unit: 'piece',
);
$opts = $opts->withItem($item);

$note = $billomat->deliveryNotes->create($opts);
printf("Delivery note #%d created (DRAFT)\n", $note->id);

// 2) Add a tag
$billomat->deliveryNotes->tags->create(
    new DeliveryNoteTagCreateOptions(deliveryNoteId: $note->id, name: 'freight-shipping'),
);

// 3) Complete: the number is assigned
$billomat->deliveryNotes->complete($note->id);

$opened = $billomat->deliveryNotes->getOrFail($note->id);
printf("Status: %s, delivery note number: %s\n",
    $opened->status?->label() ?? '?',
    $opened->deliveryNoteNumber ?? '?',
);

// 4) Email the PDF to the carrier
$email = new EmailOptions(to: ['carrier@example.com'], subject: 'Delivery note {NUMBER}');
$billomat->deliveryNotes->email($note->id, $email);

// 5) Upload the signed proof of delivery (e.g. from a mobile workflow)
$signed = file_get_contents('/var/spool/scans/' . $opened->deliveryNoteNumber . '.pdf');
if (false !== $signed) {
    $billomat->deliveryNotes->uploadSignature($note->id, base64_encode($signed));
}

// 6) Add a note
$billomat->deliveryNotes->comments->create(
    new DeliveryNoteCommentCreateOptions(
        deliveryNoteId: $note->id,
        comment: 'Receipt confirmed by warehouse South on June 2.',
    ),
);

// 7) Archive the signed PDF version
$pdf = $billomat->deliveryNotes->pdf($note->id, PdfType::SIGNED);
file_put_contents("delivery-note-{$opened->deliveryNoteNumber}.pdf", $pdf->getBinary());
```
