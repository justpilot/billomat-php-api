<!-- Source: https://www.billomat.com/api/angebote/ -->

# Offers (Angebote)

API wrapper for offers (quotes) at `/offers` and their three sub-resources (`/offer-items`, `/offer-comments`, `/offer-tags`).

## Access

```php
$billomat->offers           // offers
$billomat->offers->items    // items of an offer
$billomat->offers->comments // comments / audit trail
$billomat->offers->tags     // tags
```

## Model

An offer goes through a fixed status lifecycle:

```text
DRAFT  ──complete()──▶  OPEN  ──win()─────▶  WON  ──clear()────▶  CLEARED
                         │                   ▲  ◀──unclear()─────┘
                         │                   │
                         ├──lose()────▶  LOST  (also from WON)
                         └──cancel()──▶  CANCELED  (also from WON)
                                         └──uncancel()──▶  OPEN
```

`clear()` ("billed") only works on a `WON` offer; `unclear()` takes it back to `WON`. `uncancel()` takes a `CANCELED` offer back to `OPEN`. There is no generic `undo` endpoint (Billomat answers `PUT /offers/{id}/undo` with 404). An offer is fully editable only as a `DRAFT`; `complete()` assigns the final `offer_number` and limits editing to a few fields. An accepted offer usually becomes a [confirmation](confirmations.md) (Auftragsbestätigung) or directly an [invoice](invoices.md).

## Endpoints

### `/offers`

| Method | HTTP | Path |
|---|---|---|
| `list($filters?)` | GET | `/offers` |
| `listPage($filters?)` | GET | `/offers` (one page with metadata) |
| `iterateAll($filters?, $pageSize?)` | GET | `/offers` (lazy, across all pages) |
| `get($id)` / `getOrFail($id)` | GET | `/offers/{id}` |
| `create($options)` | POST | `/offers` |
| `update($id, $options)` | PUT | `/offers/{id}` |
| `delete($id)` | DELETE | `/offers/{id}` |
| `complete($id, $templateId?)` | PUT | `/offers/{id}/complete` |
| `cancel($id)` | PUT | `/offers/{id}/cancel` |
| `uncancel($id)` | PUT | `/offers/{id}/uncancel` |
| `win($id)` | PUT | `/offers/{id}/win` |
| `lose($id)` | PUT | `/offers/{id}/lose` |
| `clear($id)` | PUT | `/offers/{id}/clear` |
| `unclear($id)` | PUT | `/offers/{id}/unclear` |
| `email($id, $options?)` | POST | `/offers/{id}/email` |
| `uploadSignature($id, $base64Pdf)` | PUT | `/offers/{id}/upload-signature` |
| `pdf($id, $type?, $rawPdf?)` | GET | `/offers/{id}/pdf` |

### `/offer-items`

| Method | HTTP | Path |
|---|---|---|
| `list($offerId, $query?)` | GET | `/offer-items?offer_id={id}` |
| `get($id)` / `getOrFail($id)` | GET | `/offer-items/{id}` |
| `create($offerId, $options)` | POST | `/offer-items` |
| `update($id, $options)` | PUT | `/offer-items/{id}` |
| `delete($id)` | DELETE | `/offer-items/{id}` |

### `/offer-comments`

| Method | HTTP | Path |
|---|---|---|
| `list($offerId, $actionKeys?)` | GET | `/offer-comments?offer_id={id}` |
| `get($id)` / `getOrFail($id)` | GET | `/offer-comments/{id}` |
| `create($options)` | POST | `/offer-comments` |
| `delete($id)` | DELETE | `/offer-comments/{id}` |

### `/offer-tags`

| Method | HTTP | Path |
|---|---|---|
| `list($offerId)` | GET | `/offer-tags?offer_id={id}` |
| `cloud()` | GET | `/offer-tags` |
| `get($id)` / `getOrFail($id)` | GET | `/offer-tags/{id}` |
| `create($options)` | POST | `/offer-tags` |
| `delete($id)` | DELETE | `/offer-tags/{id}` |

## Methods

### Offers

#### `list(OfferFilter|array $filters = []): list<Offer>`

Returns offers with optional filters (e.g. `client_id`, `status`, `from`/`to`, `number_pre`, `tags`, `order_by`). When there is exactly one match, Billomat returns the object as an associative array instead of a list; `array_is_list()` detects this and wraps the single result in an array.

The typed `OfferFilter` is the recommended way to filter. Its camelCase fields are `clientId`, `contactId`, `offerNumber`, `status` (`list<OfferStatus>`), `from`/`to` (`DateTimeImmutable`), `label`, `intro`, `note`, `tags` and `articleId`; multi-value fields are lists and are sent comma-separated. On top come `orderBy`, `page`, `perPage` and `extra` for further query parameters (e.g. `number_pre`). A plain array still works.

```php
use Justpilot\Billomat\Api\OfferFilter;
use Justpilot\Billomat\Api\OrderBy;
use Justpilot\Billomat\Model\Enum\OfferStatus;

$open = $billomat->offers->list(new OfferFilter(
    status: [OfferStatus::OPEN, OfferStatus::WON],
    orderBy: OrderBy::desc('date'),
));
```

`listPage()` and `iterateAll()` work as described in [Pagination](../advanced/pagination.md).

#### `get(int $id): ?Offer` / `getOrFail(int $id): Offer`

`get()` returns `null` on 404; `getOrFail()` throws a `NotFoundException` instead. Embedded `offer-items` and `taxes` are hydrated automatically.

#### `create(OfferCreateOptions $options): Offer`

Items can be passed directly in the `create()` call via `items: […]` or `withItem()`; they end up as `offer-items.offer-item` in the payload. New offers start with status `DRAFT`.

```php
use Justpilot\Billomat\Api\OfferCreateOptions;
use Justpilot\Billomat\Api\OfferItemCreateOptions;
use Justpilot\Billomat\Model\Money;

$opts = new OfferCreateOptions(
    clientId: 12345,
    title: 'Web hosting offer',
    date: new DateTimeImmutable('2026-06-02'),
    validityDays: 30,
    items: [
        new OfferItemCreateOptions(quantity: 1.0, unitPrice: Money::of('99.00'), title: 'Setup fee'),
    ],
);

$offer = $billomat->offers->create($opts);
```

#### `update(int $id, OfferUpdateOptions $options): Offer`

For partial updates. Fully editable only as a `DRAFT`. Items can't be changed with a PUT to `/offers/{id}`; use `$billomat->offers->items` for that. The class therefore has no `items` field.

#### `complete(int $id, ?int $templateId = null): void`

DRAFT → OPEN. Assigns the final `offer_number` and generates the PDF. Optionally pass a different template ID.

```php
$billomat->offers->complete($offer->id, templateId: 42);
```

#### `delete(int $id): void`

Only allowed as a `DRAFT`. For any other status Billomat responds with 422 → `ValidationException`.

#### Status transitions

```php
$billomat->offers->cancel($offer->id);   // OPEN/WON → CANCELED
$billomat->offers->uncancel($offer->id); // CANCELED → OPEN
$billomat->offers->win($offer->id);      // OPEN     → WON
$billomat->offers->lose($offer->id);     // OPEN/WON → LOST
$billomat->offers->clear($offer->id);    // WON      → CLEARED
$billomat->offers->unclear($offer->id);  // CLEARED  → WON
```

All of them return `void`. Invalid transitions throw a `ValidationException` (Billomat answers 400 "status change not possible").

#### `email(int $id, ?EmailOptions $options = null): void`

Sends the offer by email. Without options Billomat uses all defaults (sender, subject, body, template); the PDF is attached automatically.

```php
use Justpilot\Billomat\Api\EmailOptions;

$email = new EmailOptions(
    to: ['client@example.com'],
    bcc: ['sales@mycompany.example'],
    subject: 'Your offer',
);

$billomat->offers->email($offer->id, $email);
```

#### `uploadSignature(int $id, string $base64Pdf): void`

Uploads a signed PDF version. `$base64Pdf` is the Base64-encoded PDF content (without a `data:` prefix).

#### `pdf(int $id, ?PdfType $type = null, bool $rawPdf = false): OfferPdf|string`

By default the method returns an `OfferPdf` model with Base64 content; with `$rawPdf = true` it skips the JSON layer and returns the raw binary bytes. The `$type` parameter shares the `PdfType` enum with the invoice PDF (`SIGNED`, `PRINT`).

```php
use Justpilot\Billomat\Model\Enum\PdfType;

$pdf = $billomat->offers->pdf($offer->id);              // OfferPdf
file_put_contents('offer.pdf', $pdf->getBinary());

$bytes = $billomat->offers->pdf($offer->id, PdfType::SIGNED, rawPdf: true);
```

### Offer items

`$billomat->offers->items` is the generic `ItemsApi`, as for [invoice items](invoice-items.md).

```php
$items = $billomat->offers->items->list($offer->id);

$opts = new OfferItemCreateOptions(quantity: 2.0, unitPrice: Money::of('49.90'));
$item = $billomat->offers->items->create($offer->id, $opts);

$billomat->offers->items->update($item->id, new OfferItemUpdateOptions(quantity: 3.0));
$billomat->offers->items->delete($item->id);
```

`create()` injects `offer_id` into the payload automatically; `OfferItemCreateOptions` itself has no such field. `update()` takes `OfferItemUpdateOptions` and only sends the fields you set.

### Offer comments

`list()` can optionally filter by `actionkey` values (sent to the API comma-separated):

```php
use Justpilot\Billomat\Api\OfferCommentCreateOptions;
use Justpilot\Billomat\Model\Enum\OfferCommentActionKey;

$comments = $billomat->offers->comments->list(
    $offer->id,
    actionKeys: [OfferCommentActionKey::EMAIL, OfferCommentActionKey::WIN],
);

$billomat->offers->comments->create(
    new OfferCommentCreateOptions(offerId: $offer->id, comment: 'Accepted by phone.'),
);
```

Billomat creates comments for system actions (`COMPLETE`, `WIN`, `EMAIL`, …) automatically. See [invoice comments](invoice-comments.md) for details on the generic `CommentsApi`.

### Offer tags

```php
use Justpilot\Billomat\Api\OfferTagCreateOptions;

$tag = $billomat->offers->tags->create(
    new OfferTagCreateOptions(offerId: $offer->id, name: 'hot-lead'),
);

$tags  = $billomat->offers->tags->list($offer->id); // list<OfferTag>
$cloud = $billomat->offers->tags->cloud();          // list<OfferTagCloudEntry>, aggregated with count
```

## Write model: `OfferCreateOptions`

Constructor: `new OfferCreateOptions(int $clientId, …)`. `clientId` is required and the first parameter. All other fields are constructor parameters with a default and are passed as named arguments. The object is immutable (`final readonly`); every field has a `with*()` method that returns a modified copy (e.g. `$opts->withTitle('New')`).

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `clientId` | `client_id` | `int` | Required (constructor) |
| `contactId` | `contact_id` | `?int` | |
| `address` | `address` | `?string` | Overrides the client address |
| `numberPre` | `number_pre` | `?string` | |
| `number` | `number` | `?int` | |
| `numberLength` | `number_length` | `?int` | |
| `date` | `date` | `?\DateTimeImmutable` | Serialized as `Y-m-d` |
| `validityDays` | `validity_days` | `?int` | Validity in days |
| `validityDate` | `validity_date` | `?\DateTimeImmutable` | Valid until; Billomat default: `date` + `validity_days` |
| `discountRate` | `discount_rate` | `?int` | |
| `discountDays` | `discount_days` | `?int` | |
| `discountDate` | `discount_date` | `?\DateTimeImmutable` | Serialized as `Y-m-d` |
| `title` | `title` | `?string` | |
| `label` | `label` | `?string` | |
| `intro` | `intro` | `?string` | |
| `note` | `note` | `?string` | |
| `reduction` | `reduction` | `?string` | Absolute or with `%` |
| `currencyCode` | `currency_code` | `?string` | ISO 4217 |
| `netGross` | `net_gross` | `?NetGross` | `NET`/`GROSS`/`SETTINGS` |
| `quote` | `quote` | `?float` | Exchange rate |
| `freeTextId` | `free_text_id` | `?int` | |
| `templateId` | `template_id` | `?int` | |

### Items

Items are passed via the constructor parameter `items` (`list<OfferItemCreateOptions>`) and are then available in the `$items` property. `withItem()` returns a copy with one more item:

```php
$opts = $opts->withItem(new OfferItemCreateOptions(quantity: 1.0, unitPrice: Money::of('99')));
```

`toArray()` renders them as `offer-items.offer-item`.

## Write model: `OfferUpdateOptions`

Has the same fields as `OfferCreateOptions`, but `clientId` is optional and there is no `items` field. `discountRate` is `float` here instead of `int`. `null` means "don't send", the value stays unchanged. `Clear::Field` (`Justpilot\Billomat\Api\Clear`) sends the field as JSON `null` so Billomat empties it, e.g. `new OfferUpdateOptions(note: Clear::Field)`.

| Property | Billomat field | Type |
|---|---|---|
| `clientId` | `client_id` | `int\|Clear\|null` |
| `contactId` | `contact_id` | `int\|Clear\|null` |
| `address` | `address` | `string\|Clear\|null` |
| `numberPre`, `number`, `numberLength` | same | `string\|Clear\|null` / `int\|Clear\|null` / `int\|Clear\|null` |
| `date`, `validityDate`, `discountDate` | same | `\DateTimeImmutable\|Clear\|null` |
| `validityDays` | `validity_days` | `int\|Clear\|null` |
| `discountRate` | `discount_rate` | `float\|Clear\|null` |
| `discountDays` | `discount_days` | `int\|Clear\|null` |
| `title`, `label`, `intro`, `note`, `reduction` | same | `string\|Clear\|null` |
| `netGross` | `net_gross` | `NetGross\|Clear\|null` |
| `currencyCode` | `currency_code` | `string\|Clear\|null` |
| `quote` | `quote` | `float\|Clear\|null` |
| `freeTextId`, `templateId` | same | `int\|Clear\|null` |

## Write model: `OfferItemCreateOptions`

Constructor: `new OfferItemCreateOptions(float $quantity, Money $unitPrice, …)`. Both values are required and always sent, even if they are `0` (`toArray()` only drops `null` values). The object is `final readonly` with a `with*()` method per field.

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `quantity` | `quantity` | `float` | Required (constructor) |
| `unitPrice` | `unit_price` | `Money` | Required (constructor) |
| `type` | `type` | `?ItemType` | `PRODUCT`/`SERVICE` |
| `articleId` | `article_id` | `?int` | |
| `title` | `title` | `?string` | |
| `description` | `description` | `?string` | |
| `unit` | `unit` | `?string` | |
| `taxName` | `tax_name` | `?string` | |
| `taxRate` | `tax_rate` | `?float` | |
| `taxChangedManually` | `tax_changed_manually` | `?bool` | See pitfalls |
| `reduction` | `reduction` | `?string` | Absolute or with `%` |
| `position` | `position` | `?int` | |
| `optional` | `optional` | `?int` | Optional item, not included in the total (`1`/`0`) |

For `ItemsApi::update()` there is `OfferItemUpdateOptions`: every field is optional (`X|Clear|null`) with a `with*()` method, only the fields you set are sent, and `Clear::Field` empties a field. Billomat keeps all other fields of the item.

## Write model: `OfferCommentCreateOptions`

Constructor: `new OfferCommentCreateOptions(int $offerId, string $comment, …)`; both are required.

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `offerId` | `offer_id` | `int` | Required (constructor) |
| `comment` | `comment` | `string` | Required (constructor) |
| `actionkey` | `actionkey` | `?OfferCommentActionKey` | Usually only set for system actions |
| `public` | `public` | `?bool` | Visible in the client's activity list |

## Write model: `OfferTagCreateOptions`

Constructor: `new OfferTagCreateOptions(int $offerId, string $name)`; both are required.

| Property | Billomat field | Type |
|---|---|---|
| `offerId` | `offer_id` | `int` (required) |
| `name` | `name` | `string` (required) |

## Write model: `EmailOptions`

Shared class for sending all document types by email (invoices, offers, confirmations, credit notes, delivery notes, reminders, letters); the full description is on the [Invoices](invoices.md#write-model-emailoptions) page. No required fields; without explicit values Billomat falls back to the defaults stored in the account.

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `emailTemplateId` | `email_template_id` | `?int` | |
| `from` | `from` | `?string` | Sender override |
| `to` | `recipients.to` | `list<string>` | Empty array → omitted |
| `cc` | `recipients.cc` | `list<string>` | |
| `bcc` | `recipients.bcc` | `list<string>` | |
| `subject` | `subject` | `?string` | |
| `body` | `body` | `?string` | |
| `filename` | `filename` | `?string` | File name of the attachment |
| `attachments` | `attachments.attachment[]` | `list<array{filename,mimetype,base64file}>` | Additional attachments |

## Read model: `Offer`

`final readonly class Offer`. The main fields:

| Property | Type |
|---|---|
| `id` | `int` |
| `clientId` | `int` |
| `contactId` | `?int` |
| `created` | `?\DateTimeImmutable` |
| `offerNumber` | `?string`, only set by `complete()` |
| `number`, `numberPre`, `numberLength` | `?int`, `?string`, `?int` |
| `status` | `?OfferStatus` |
| `date` | `?\DateTimeImmutable` |
| `validityDays` | `?int` |
| `address` | `?string` |
| `title`, `label`, `intro`, `note`, `reduction` | `?string` |
| `totalGross`, `totalNet` | `?Money` |
| `totalGrossUnreduced`, `totalNetUnreduced` | `?Money` |
| `netGross` | `?NetGross` |
| `currencyCode` | `?string` |
| `quote` | `?float` |
| `freeTextId`, `templateId` | `?int` |
| `taxes` | `list<TaxSummary>` (`name`, `rate`, `amount` as `Money`) |
| `items` | `list<OfferItem>`, embedded from `offer-items.offer-item` |
| `updated` | `?\DateTimeImmutable` |
| `validityDate` | `?\DateTimeImmutable` (valid until, date only) |
| `totalReduction` | `?Money` |
| `customfield` | `?string` (free extra field, see [custom meta attributes](../concepts/custom-meta-attributes.md)) |
| `extra` | `array<string, mixed>`: fields the SDK doesn't map (yet), raw; see [models](../concepts/models.md) |

## Read model: `OfferItem`

`final readonly`. Same fields as `InvoiceItem`, with `offerId` instead of `invoiceId`.

| Property | Type |
|---|---|
| `id`, `offerId`, `articleId`, `position` | `?int` |
| `unit` | `?string` |
| `quantity`, `unitPrice` | `float`, `Money` |
| `taxName` | `?string` |
| `taxRate` | `?float` |
| `taxChangedManually` | `?bool` |
| `title`, `description`, `reduction` | `?string` |
| `type` | `?ItemType` |
| `totalGross`, `totalNet`, `totalGrossUnreduced`, `totalNetUnreduced` | `?Money` |
| `optional` | `?bool` (optional item) |
| `customfield` | `?string` (free extra field, see [custom meta attributes](../concepts/custom-meta-attributes.md)) |
| `extra` | `array<string, mixed>` |

## Read model: `OfferComment`

`final readonly`.

| Property | Type |
|---|---|
| `id` | `int` |
| `offerId` | `int` |
| `comment` | `?string` |
| `created` | `?\DateTimeImmutable` |
| `userId` | `?int` |
| `actionkey` | `?OfferCommentActionKey` |
| `actionkeyRaw` | `?string`, the raw value in case Billomat returns an unknown `actionkey` |
| `public` | `?bool` |
| `byClient` | `?bool` |
| `emailId` | `?int` |
| `clientId` | `?int` |
| `customfield` | `?string` (free extra field, see [custom meta attributes](../concepts/custom-meta-attributes.md)) |
| `extra` | `array<string, mixed>` |

## Read model: `OfferTag`

`final readonly`. Fields: `id` (`?int`), `offerId` (`int`), `name` (`string`), `customfield` (`?string`), `extra`.

## Read model: `OfferTagCloudEntry`

`final readonly`. Aggregated tag cloud entry: `id` (`?int`), `name` (`string`), `count` (`int`), `extra`.

## Read model: `OfferPdf`

`final class OfferPdf` (not `readonly`).

| Property | Type |
|---|---|
| `id` | `int` |
| `offerId` | `int` |
| `created` | `?\DateTimeImmutable` |
| `filename` | `string` |
| `mimeType` | `string`, default `application/pdf` |
| `fileSize` | `int` |
| `base64file` | `string` |

`getBinary(): string` decodes `base64file` and returns the raw PDF bytes (`''` for invalid Base64).

## Enums

- [`OfferStatus`](../../src/Model/Enum/OfferStatus.php): `DRAFT`, `OPEN`, `WON`, `LOST`, `CLEARED`, `CANCELED`. Has `label()` for the German name.
- [`OfferCommentActionKey`](../../src/Model/Enum/OfferCommentActionKey.php): `CREATE`, `EDIT`, `OPEN`, `COMPLETE`, `CANCEL`, `WIN`, `LOSE`, `CLEAR`, `CHANGE_STATUS`, `EMAIL`, `MAIL`, `COMMENT`.
- [`ItemType`](../../src/Model/Enum/ItemType.php): `PRODUCT`, `SERVICE`, shared with all document items.
- [`PdfType`](../../src/Model/Enum/PdfType.php): `SIGNED` (`signed`), `PRINT` (`print`); the wire values are lowercase.
- [`NetGross`](../../src/Model/Enum/NetGross.php): `NET`, `GROSS`, `SETTINGS`.

## Pitfalls

- **Status transitions are strict.** `win()` only works from `OPEN`, `clear()` only from `WON` (not from `OPEN`). Trying them in the wrong status gets you a 400 → `ValidationException`. Order: first `complete()`, then `win()`, then `clear()`.
- **`update()` doesn't change items.** Billomat ignores embedded `offer-items` on a PUT to `/offers/{id}`. Always use `$billomat->offers->items`.
- **`delete()` only as a `DRAFT`.** Once `complete()` has run, the offer is numbered and can't be deleted; only `cancel()` is possible.
- **Item updates are partial.** `OfferItemUpdateOptions` sends only the fields you set; Billomat keeps the rest of the item. Passing `*ItemCreateOptions` to `update()` throws an `InvalidArgumentException`.
- **`array_is_list` normalization in `list()`.** With exactly one match, Billomat returns `offer` as an object instead of a list; the method detects this and wraps the single result in an array.
- **`tax_changed_manually` is required for manual `taxRate` values.** Without the flag, Billomat ignores the explicitly set tax rate and uses the article's/client's default.
- **`pdf()` with `$rawPdf = true` bypasses JSON entirely.** The body is read as a binary string and must start with `%PDF-`, otherwise an `UnexpectedResponseException` is thrown. HTTP errors still arrive as SDK exceptions (`NotFoundException`, `AuthenticationException`, …) because `BillomatHttpClient` throws them during the request.
- **`uploadSignature()` expects plain Base64**, no data URI prefix. Passing a `data:application/pdf;base64,...` string results in a broken PDF in Billomat.
- **`OfferPdf` is not `readonly`**, unlike the other read models.
- **`OfferCommentActionKey` values are uppercase on the wire** (`WIN`, `LOSE`, …); the enum mirrors the exact values. Unknown values map to `null`, and the raw string is stored in `actionkeyRaw` as well.

## End-to-end example

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\EmailOptions;
use Justpilot\Billomat\Api\OfferCommentCreateOptions;
use Justpilot\Billomat\Api\OfferCreateOptions;
use Justpilot\Billomat\Api\OfferItemCreateOptions;
use Justpilot\Billomat\Api\OfferTagCreateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\Enum\ItemType;
use Justpilot\Billomat\Model\Enum\OfferCommentActionKey;
use Justpilot\Billomat\Model\Money;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

// 1) Create the offer (status: DRAFT) with two items
$opts = new OfferCreateOptions(
    clientId: 12345,
    title: 'Web hosting & consulting',
    date: new DateTimeImmutable('today'),
    validityDays: 30,
    intro: 'Thank you for your inquiry.',
);

$setup = new OfferItemCreateOptions(
    quantity: 1.0,
    unitPrice: Money::of('250.00'),
    title: 'Setup fee',
    type: ItemType::SERVICE,
);
$opts = $opts->withItem($setup);

$hosting = new OfferItemCreateOptions(
    quantity: 12.0,
    unitPrice: Money::of('19.90'),
    title: 'Hosting Basic (12 months)',
    unit: 'month',
);
$opts = $opts->withItem($hosting);

$offer = $billomat->offers->create($opts);
printf("Offer #%d (DRAFT) created, gross: %s %s\n",
    $offer->id,
    $offer->totalGross?->format(2) ?? '0.00',
    $offer->currencyCode ?? 'EUR',
);

// 2) Add a tag
$billomat->offers->tags->create(
    new OfferTagCreateOptions(offerId: $offer->id, name: 'hot-lead'),
);

// 3) Complete → OPEN, the number is assigned
$billomat->offers->complete($offer->id);

$opened = $billomat->offers->getOrFail($offer->id);
printf("Status: %s, offer number: %s\n",
    $opened->status?->value ?? '?',
    $opened->offerNumber ?? '?',
);

// 4) Send it, with the account defaults for everything else
$email = new EmailOptions(to: ['client@example.com'], bcc: ['sales@mycompany.example']);
$billomat->offers->email($offer->id, $email);

// 5) The client accepts → win()
$billomat->offers->win($offer->id);

$billomat->offers->comments->create(
    new OfferCommentCreateOptions(
        offerId: $offer->id,
        comment: 'Confirmed by phone on 2026-06-02.',
    ),
);

// 6) Download the PDF
$pdf = $billomat->offers->pdf($offer->id);
file_put_contents("offer-{$opened->offerNumber}.pdf", $pdf->getBinary());

// 7) Check the audit trail: status changes only
$status = $billomat->offers->comments->list(
    $offer->id,
    actionKeys: [
        OfferCommentActionKey::COMPLETE,
        OfferCommentActionKey::WIN,
        OfferCommentActionKey::EMAIL,
    ],
);

foreach ($status as $c) {
    printf("[%s] %s: %s\n",
        $c->created?->format('Y-m-d H:i') ?? '?',
        $c->actionkey?->value ?? $c->actionkeyRaw ?? '-',
        $c->comment ?? '',
    );
}
```
