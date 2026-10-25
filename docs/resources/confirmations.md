<!-- Source: https://www.billomat.com/api/auftragsbestaetigungen/ -->

# Confirmations (Auftragsbestätigungen)

API wrapper for order confirmations under `/confirmations` and their three sub-resources (`/confirmation-items`, `/confirmation-comments`, `/confirmation-tags`).

## Access

```php
$billomat->confirmations             // order confirmations
$billomat->confirmations->items      // line items
$billomat->confirmations->comments   // comments / audit trail
$billomat->confirmations->tags       // tags
```

## Model

An order confirmation is the binding record of an order, typically issued after an [offer](offers.md) has been accepted and before the actual invoice. Its status lifecycle is simpler than the offer's and has no accept/reject step:

```
DRAFT  ──complete()──▶  COMPLETED  ──clear()────▶  CLEARED
                         │   ▲  ◀──unclear()──────┘
                         │   │
                         │   └──uncancel()──┐
                         └──cancel()──▶  CANCELED
```

`unclear()` and `uncancel()` set `CLEARED`/`CANCELED` back to `COMPLETED` (older accounts may report `OPEN` instead of `COMPLETED`). The confirmation is only fully editable in status `DRAFT`. The source offer is referenced via `offerId`.

## Endpoint overview

### `/confirmations`

| Method | HTTP | Path |
|---|---|---|
| `list($filters?)` | GET | `/confirmations` |
| `listPage($filters?)` | GET | `/confirmations` (one page plus pagination metadata) |
| `iterateAll($filters?, $pageSize?)` | GET | `/confirmations` (all pages, lazily) |
| `get($id)` / `getOrFail($id)` | GET | `/confirmations/{id}` |
| `create($options)` | POST | `/confirmations` |
| `update($id, $options)` | PUT | `/confirmations/{id}` |
| `delete($id)` | DELETE | `/confirmations/{id}` |
| `complete($id, $templateId?)` | PUT | `/confirmations/{id}/complete` |
| `cancel($id)` | PUT | `/confirmations/{id}/cancel` |
| `uncancel($id)` | PUT | `/confirmations/{id}/uncancel` |
| `clear($id)` | PUT | `/confirmations/{id}/clear` |
| `unclear($id)` | PUT | `/confirmations/{id}/unclear` |
| `email($id, $options?)` | POST | `/confirmations/{id}/email` |
| `uploadSignature($id, $base64Pdf)` | PUT | `/confirmations/{id}/upload-signature` |
| `pdf($id, $type?, $rawPdf?)` | GET | `/confirmations/{id}/pdf` |

### `/confirmation-items`

| Method | HTTP | Path |
|---|---|---|
| `list($confirmationId, $query?)` | GET | `/confirmation-items?confirmation_id={id}` |
| `get($id)` / `getOrFail($id)` | GET | `/confirmation-items/{id}` |
| `create($confirmationId, $options)` | POST | `/confirmation-items` |
| `update($id, $options)` | PUT | `/confirmation-items/{id}` |
| `delete($id)` | DELETE | `/confirmation-items/{id}` |

### `/confirmation-comments`

| Method | HTTP | Path |
|---|---|---|
| `list($confirmationId, $actionKeys?)` | GET | `/confirmation-comments?confirmation_id={id}` |
| `get($id)` / `getOrFail($id)` | GET | `/confirmation-comments/{id}` |
| `create($options)` | POST | `/confirmation-comments` |
| `delete($id)` | DELETE | `/confirmation-comments/{id}` |

### `/confirmation-tags`

| Method | HTTP | Path |
|---|---|---|
| `list($confirmationId)` | GET | `/confirmation-tags?confirmation_id={id}` |
| `cloud()` | GET | `/confirmation-tags` |
| `get($id)` / `getOrFail($id)` | GET | `/confirmation-tags/{id}` |
| `create($options)` | POST | `/confirmation-tags` |
| `delete($id)` | DELETE | `/confirmation-tags/{id}` |

## Methods

### Confirmations

#### `list(ConfirmationFilter|array $filters = []): list<Confirmation>`

Filters according to the Billomat docs: `client_id`, `contact_id`, `status`, `from`, `to`, `number_pre`, `tags`, `order_by` and more. If Billomat returns a single match as an object, the SDK normalizes it to a list.

The typed filter `ConfirmationFilter` is recommended. Its camelCase fields are `clientId`, `contactId`, `confirmationNumber`, `status` (`list<ConfirmationStatus>`), `from`/`to` (`DateTimeImmutable`), `label`, `intro`, `note`, `tags` and `articleId`. Multi-value fields are lists and are sent comma-separated. On top of that there are `orderBy`, `page`, `perPage` and `extra` for further query parameters (e.g. `number_pre`). A plain array still works; array values are sent as `key[]=…`.

```php
use Justpilot\Billomat\Api\ConfirmationFilter;
use Justpilot\Billomat\Api\OrderBy;
use Justpilot\Billomat\Model\Enum\ConfirmationStatus;

$open = $billomat->confirmations->list(new ConfirmationFilter(
    status: [ConfirmationStatus::OPEN],
    orderBy: OrderBy::desc('date'),
));
```

`listPage()` takes the same filters and returns a `Page` with pagination metadata; `iterateAll()` walks through all pages lazily. See [Pagination and filtering](../concepts/pagination-and-filtering.md).

#### `get(int $id): ?Confirmation`

Returns `null` on 404. Embedded `confirmation-items` and `taxes` are hydrated automatically. `getOrFail()` throws a `NotFoundException` instead of returning `null`.

#### `create(ConfirmationCreateOptions $options): Confirmation`

Line items can be included in the payload directly via `items: […]` or `withItem()` (as `confirmation-items.confirmation-item`). Optionally set `offerId` to link the confirmation to an existing offer.

```php
use Justpilot\Billomat\Api\ConfirmationCreateOptions;
use Justpilot\Billomat\Api\ConfirmationItemCreateOptions;
use Justpilot\Billomat\Model\Money;

$opts = new ConfirmationCreateOptions(
    clientId: 12345,
    offerId: 678,
    title: 'Order confirmation',
    date: new DateTimeImmutable('today'),
    items: [
        new ConfirmationItemCreateOptions(quantity: 1.0, unitPrice: Money::of('99.00')),
    ],
);

$conf = $billomat->confirmations->create($opts);
```

#### `update(int $id, ConfirmationUpdateOptions $options): Confirmation`

Fully editable only in `DRAFT`. Line items are not processed here; use `$billomat->confirmations->items`.

#### `complete(int $id, ?int $templateId = null): void`

Completes the draft. Assigns the final `confirmation_number` and generates the PDF. Optionally pass a different template ID.

#### `delete(int $id): void`

Only allowed in `DRAFT`; after that, use `cancel()`.

#### Status transitions

```php
$billomat->confirmations->cancel($conf->id);   // COMPLETED → CANCELED
$billomat->confirmations->uncancel($conf->id); // CANCELED  → COMPLETED
$billomat->confirmations->clear($conf->id);    // COMPLETED → CLEARED
$billomat->confirmations->unclear($conf->id);  // CLEARED   → COMPLETED
```

All four return `void`. Invalid transitions throw a `ValidationException` (Billomat answers 400 "status change not possible"). There is no generic `undo` endpoint; Billomat answers `PUT /confirmations/{id}/undo` with 404.

#### `email(int $id, ?EmailOptions $options = null): void`

Sends the confirmation by email. Without options, Billomat uses the account defaults and attaches the PDF automatically.

```php
use Justpilot\Billomat\Api\EmailOptions;

$email = new EmailOptions(to: ['client@example.com']);
$billomat->confirmations->email($conf->id, $email);
```

#### `uploadSignature(int $id, string $base64Pdf): void`

Uploads a signed PDF version. `$base64Pdf` is the plain base64-encoded PDF content.

#### `pdf(int $id, ?PdfType $type = null, bool $rawPdf = false): ConfirmationPdf|string`

Returns a `ConfirmationPdf` model with base64 content; with `$rawPdf = true` the method skips JSON and returns the raw bytes.

```php
$pdf = $billomat->confirmations->pdf($conf->id);
file_put_contents('confirmation.pdf', $pdf->getBinary());
```

### Confirmation items

```php
$items = $billomat->confirmations->items->list($conf->id);

$item = $billomat->confirmations->items->create(
    $conf->id,
    new ConfirmationItemCreateOptions(quantity: 2.0, unitPrice: Money::of('49.90')),
);

$billomat->confirmations->items->update($item->id, new ConfirmationItemUpdateOptions(quantity: 3.0));
$billomat->confirmations->items->delete($item->id);
```

`create()` adds `confirmation_id` automatically; the options class itself has no such field. `update()` takes `ConfirmationItemUpdateOptions` and only sends the fields you set.

### Confirmation comments

```php
use Justpilot\Billomat\Api\ConfirmationCommentCreateOptions;
use Justpilot\Billomat\Model\Enum\ConfirmationCommentActionKey;

$comments = $billomat->confirmations->comments->list(
    $conf->id,
    actionKeys: [ConfirmationCommentActionKey::COMPLETE, ConfirmationCommentActionKey::EMAIL],
);

$billomat->confirmations->comments->create(
    new ConfirmationCommentCreateOptions(
        confirmationId: $conf->id,
        comment: 'Order confirmed on June 2.',
    ),
);
```

### Confirmation tags

```php
use Justpilot\Billomat\Api\ConfirmationTagCreateOptions;

$tag = $billomat->confirmations->tags->create(
    new ConfirmationTagCreateOptions(confirmationId: $conf->id, name: 'order-2026'),
);

$tags  = $billomat->confirmations->tags->list($conf->id);
$cloud = $billomat->confirmations->tags->cloud();
```

## Write model: `ConfirmationCreateOptions`

Constructor: `new ConfirmationCreateOptions(int $clientId, …)`. `clientId` is required and the first parameter. Every field is a constructor parameter with a default and is passed as a named argument. The object is immutable (`final readonly`); each field has a `with*()` method that returns a modified copy (e.g. `$opts->withTitle('New')`).

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `clientId` | `client_id` | `int` | required (constructor) |
| `contactId` | `contact_id` | `?int` | |
| `address` | `address` | `?string` | |
| `numberPre`, `number`, `numberLength` | snake_case equivalent | `?string`/`?int`/`?int` | |
| `date` | `date` | `?\DateTimeImmutable` | serialized as `Y-m-d` |
| `discountRate` | `discount_rate` | `?int` | |
| `discountDays` | `discount_days` | `?int` | |
| `discountDate` | `discount_date` | `?\DateTimeImmutable` | serialized as `Y-m-d` |
| `title`, `label`, `intro`, `note`, `reduction` | snake_case equivalent | `?string` | |
| `currencyCode` | `currency_code` | `?string` | |
| `netGross` | `net_gross` | `?NetGross` | |
| `quote` | `quote` | `?float` | |
| `offerId` | `offer_id` | `?int` | source offer |
| `freeTextId` | `free_text_id` | `?int` | |
| `templateId` | `template_id` | `?int` | |

### Line items

Line items are passed as a list via the constructor parameter `items` (`items: [new ConfirmationItemCreateOptions(…), …]`) and are then available in the `$items` property. `withItem()` returns a copy with one additional item. `toArray()` renders them as `confirmation-items.confirmation-item`.

## Write model: `ConfirmationUpdateOptions`

Mirrors `ConfirmationCreateOptions`, but `clientId` is optional and there is no `items`. `discountRate` is `?float` here instead of `?int`. `null` means "don't send", so the value stays unchanged. `Clear::Field` (`Justpilot\Billomat\Api\Clear`) sends the field as JSON `null`, and Billomat empties it, e.g. `new ConfirmationUpdateOptions(note: Clear::Field)`.

| Property | Billomat field | Type |
|---|---|---|
| `clientId` | `client_id` | `?int` |
| `contactId`, `numberPre`, `number`, `numberLength`, `freeTextId`, `templateId`, `offerId` | snake_case equivalent | `?int`/`?string` |
| `address`, `title`, `label`, `intro`, `note`, `reduction`, `currencyCode` | snake_case equivalent | `?string` |
| `date`, `discountDate` | snake_case equivalent | `?\DateTimeImmutable` |
| `discountRate` | `discount_rate` | `?float` |
| `discountDays` | `discount_days` | `?int` |
| `netGross` | `net_gross` | `?NetGross` |
| `quote` | `quote` | `?float` |

Every field additionally accepts `Clear::Field`.

## Write model: `ConfirmationItemCreateOptions`

Constructor: `new ConfirmationItemCreateOptions(float $quantity, Money $unitPrice)`. Both are required and are sent even when they are `0`.

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

For `ItemsApi::update()` there is `ConfirmationItemUpdateOptions`: every field is optional (`X|Clear|null`) with a `with*()` method, only the fields you set are sent, and `Clear::Field` empties a field. Billomat keeps all other fields of the item.

## Write model: `ConfirmationCommentCreateOptions`

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `confirmationId` | `confirmation_id` | `int` | required (constructor) |
| `comment` | `comment` | `string` | required (constructor) |
| `actionkey` | `actionkey` | `?ConfirmationCommentActionKey` | usually only for system actions |

## Write model: `ConfirmationTagCreateOptions`

| Property | Billomat field | Type |
|---|---|---|
| `confirmationId` | `confirmation_id` | `int` (required) |
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

## Read model: `Confirmation`

`final readonly class Confirmation`.

| Property | Type |
|---|---|
| `id` | `int` |
| `clientId` | `int` |
| `contactId` | `?int` |
| `created` | `?\DateTimeImmutable` |
| `confirmationNumber` | `?string`, only set after `complete()` |
| `number`, `numberPre`, `numberLength` | `?int`/`?string`/`?int` |
| `status` | `?ConfirmationStatus` |
| `date` | `?\DateTimeImmutable` |
| `address` | `?string` |
| `title`, `label`, `intro`, `note`, `reduction` | `?string` |
| `totalGross`, `totalNet`, `totalGrossUnreduced`, `totalNetUnreduced` | `?Money` |
| `netGross` | `?NetGross` |
| `currencyCode` | `?string` |
| `quote` | `?float` |
| `offerId` | `?int` |
| `freeTextId`, `templateId` | `?int` |
| `taxes` | `list<TaxSummary>` (`name`, `rate`, `amount` as `Money`) |
| `items` | `list<ConfirmationItem>` |
| `updated` | `?\DateTimeImmutable` |
| `totalReduction` | `?Money` |
| `customfield` | `?string` (free-form extra field, see [Custom meta attributes](../concepts/custom-meta-attributes.md)) |
| `extra` | `array<string, mixed>` (raw fields the SDK doesn't map yet, see [Models](../concepts/models.md)) |

## Read model: `ConfirmationItem`

`final readonly`.

| Property | Type |
|---|---|
| `id`, `confirmationId`, `articleId`, `position` | `?int` |
| `unit` | `?string` |
| `quantity`, `unitPrice` | `float`, `Money` |
| `taxName` | `?string` |
| `taxRate` | `?float` |
| `taxChangedManually` | `?bool` |
| `title`, `description`, `reduction` | `?string` |
| `type` | `?ItemType` |
| `totalGross`, `totalNet`, `totalGrossUnreduced`, `totalNetUnreduced` | `?Money` |
| `customfield` | `?string` (free-form extra field, see [Custom meta attributes](../concepts/custom-meta-attributes.md)) |
| `extra` | `array<string, mixed>` |

## Read model: `ConfirmationComment`

`final readonly`.

| Property | Type |
|---|---|
| `id` | `int` |
| `confirmationId` | `int` |
| `comment` | `?string` |
| `created` | `?\DateTimeImmutable` |
| `userId` | `?int` |
| `actionkey` | `?ConfirmationCommentActionKey` |
| `actionkeyRaw` | `?string`, raw value for an unknown `actionkey` |
| `public` | `?bool` |
| `byClient` | `?bool` |
| `emailId` | `?int` |
| `clientId` | `?int` |
| `customfield` | `?string` (free-form extra field, see [Custom meta attributes](../concepts/custom-meta-attributes.md)) |
| `extra` | `array<string, mixed>` |

## Read model: `ConfirmationTag`

`final readonly`. Fields: `id` (`?int`), `confirmationId` (`int`), `name` (`string`), `customfield` (`?string`), `extra` (`array`).

## Read model: `ConfirmationTagCloudEntry`

`final readonly`. Aggregate: `id` (`?int`), `name` (`string`), `count` (`int`), `extra` (`array`).

## Read model: `ConfirmationPdf`

`final class ConfirmationPdf` (not `readonly`).

| Property | Type |
|---|---|
| `id` | `int` |
| `confirmationId` | `int` |
| `created` | `?\DateTimeImmutable` |
| `filename` | `string` |
| `mimeType` | `string`, default `application/pdf` |
| `fileSize` | `int` |
| `base64file` | `string` |

`getBinary(): string` decodes `base64file`.

## Enums used

- [`ConfirmationStatus`](../../src/Model/Enum/ConfirmationStatus.php): `DRAFT`, `OPEN`, `COMPLETED`, `CLEARED`, `CANCELED`. After `complete()`, Billomat now returns `COMPLETED`. Has `label()` for the German display name. Note: **no** `WON`/`LOST` as for offers.
- [`ConfirmationCommentActionKey`](../../src/Model/Enum/ConfirmationCommentActionKey.php): `CREATE`, `EDIT`, `OPEN`, `COMPLETE`, `CANCEL`, `CLEAR`, `CHANGE_STATUS`, `EMAIL`, `MAIL`, `COMMENT`; smaller than for offers (`WIN`/`LOSE` are missing).
- [`ItemType`](../../src/Model/Enum/ItemType.php): `PRODUCT`, `SERVICE`, shared with invoice items.
- [`PdfType`](../../src/Model/Enum/PdfType.php): `SIGNED` (`signed`), `PRINT` (`print`); the wire values are lowercase.
- [`NetGross`](../../src/Model/Enum/NetGross.php): `NET`, `GROSS`, `SETTINGS`.

## Pitfalls

- **Simpler lifecycle than offers.** There is no `win()`/`lose()`: a confirmation is either marked as done (`clear()`, reverted by `unclear()`) or canceled (`cancel()`, reverted by `uncancel()`). Calling `win()` out of habit means looking for the wrong endpoint.
- **`update()` doesn't change line items.** Billomat ignores embedded `confirmation-items` on PUT. Always use `$billomat->confirmations->items`.
- **`delete()` only in `DRAFT`.** After `complete()`, only `cancel()` is possible.
- **Item updates are partial.** `ConfirmationItemUpdateOptions` sends only the fields you set; Billomat keeps the rest of the item. Passing `*ItemCreateOptions` to `update()` throws an `InvalidArgumentException`.
- **Single results are normalized in `list()`.** For exactly one match, Billomat returns `confirmation` as an object; the SDK wraps it in a list.
- **The `offerId` link is informational.** The confirmation is not synchronized with the offer status automatically; set `OfferStatus::WON` separately via `$billomat->offers->win()`.
- **`tax_changed_manually` is required for manual `taxRate` values.** Without the flag, Billomat uses the article's or client's default.
- **`ConfirmationPdf` is not `readonly`**, a pragmatic exception so the container can hold binary data.
- **`uploadSignature()` expects plain base64**, without a `data:` prefix.

## End-to-end example

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\ConfirmationCommentCreateOptions;
use Justpilot\Billomat\Api\ConfirmationCreateOptions;
use Justpilot\Billomat\Api\ConfirmationItemCreateOptions;
use Justpilot\Billomat\Api\ConfirmationTagCreateOptions;
use Justpilot\Billomat\Api\EmailOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\Enum\ItemType;
use Justpilot\Billomat\Model\Money;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

// 1) Create a confirmation from an offer
$opts = new ConfirmationCreateOptions(
    clientId: 12345,
    offerId: 678,
    title: 'Order confirmation web hosting',
    date: new DateTimeImmutable('today'),
    intro: 'We confirm your order as follows.',
);

$item = new ConfirmationItemCreateOptions(
    quantity: 12.0,
    unitPrice: Money::of('19.90'),
    title: 'Hosting Basic (12 months)',
    unit: 'month',
    type: ItemType::SERVICE,
);
$opts = $opts->withItem($item);

$conf = $billomat->confirmations->create($opts);
printf("Confirmation #%d created (DRAFT)\n", $conf->id);

// 2) Add a tag
$billomat->confirmations->tags->create(
    new ConfirmationTagCreateOptions(confirmationId: $conf->id, name: 'order-2026'),
);

// 3) Complete: the number is assigned
$billomat->confirmations->complete($conf->id);

$opened = $billomat->confirmations->getOrFail($conf->id);
printf("Status: %s, confirmation number: %s\n",
    $opened->status?->label() ?? '?',
    $opened->confirmationNumber ?? '?',
);

// 4) Send with the default template
$email = new EmailOptions(to: ['client@example.com']);
$billomat->confirmations->email($conf->id, $email);

// 5) Add an internal note
$billomat->confirmations->comments->create(
    new ConfirmationCommentCreateOptions(
        confirmationId: $conf->id,
        comment: 'Order processed, ready for invoicing.',
    ),
);

// 6) Once the invoice is out: mark the order as done
$billomat->confirmations->clear($conf->id);

// 7) Download the PDF
$pdf = $billomat->confirmations->pdf($conf->id);
file_put_contents("confirmation-{$opened->confirmationNumber}.pdf", $pdf->getBinary());
```
