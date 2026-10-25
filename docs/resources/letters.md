<!-- Source: https://www.billomat.com/api/briefe/ -->

# Letters (Briefe)

API wrapper for free-form correspondence under `/letters` and its two sub-resources (`/letter-comments`, `/letter-tags`).

## Access

```php
$billomat->letters            // letters
$billomat->letters->comments  // activity log / free-form comments
$billomat->letters->tags      // tags
```

## Model

A letter is free-form, formal correspondence to a client (or supplier, via `supplierId`), with no line items, no tax calculation and no payment lifecycle. Its main purposes are sending by email, uploading an externally generated PDF (`upload()`) and keeping a record in the client's history. Billomat also offers postal delivery for letters (Pixelletter, `POST /letters/{id}/mail`); the SDK doesn't wrap that endpoint.

The lifecycle is a single step:

```
DRAFT  ── complete() ──▶  COMPLETED
  └── delete() (any status)
```

The API offers no further status transitions for letters: `PUT /letters/{id}/cancel` and `/uncancel` answer 400 ("cancellation not supported"), `/clear`, `/unclear` and `/undo` answer 404. A completed letter can't be moved back to `DRAFT`. The enum still knows `OPEN`, `CLEARED` and `CANCELED`, which Billomat may report for letters changed in the web app.

## Endpoint overview

### `/letters`

| Method | HTTP | Path |
|---|---|---|
| `list($filters?)` | GET | `/letters` |
| `listPage($filters?)` | GET | `/letters` (one page plus pagination metadata) |
| `iterateAll($filters?, $pageSize?)` | GET | `/letters` (all pages, lazily) |
| `get($id)` / `getOrFail($id)` | GET | `/letters/{id}` |
| `create($options)` | POST | `/letters` |
| `update($id, $options)` | PUT | `/letters/{id}` |
| `delete($id)` | DELETE | `/letters/{id}` |
| `complete($id, $templateId?)` | PUT | `/letters/{id}/complete` |
| `email($id, $options?)` | POST | `/letters/{id}/email` |
| `pdf($id, $type?)` | GET | `/letters/{id}/pdf` |
| `pdfContent($id, $type?)` | GET | `/letters/{id}/pdf?format=pdf` |
| `upload($id, $base64Pdf)` | PUT | `/letters/{id}/upload` |
| `uploadSignature($id, $base64Pdf)` | PUT | `/letters/{id}/upload-signature` |

### `/letter-comments`

| Method | HTTP | Path |
|---|---|---|
| `list($letterId, $actionKeys?, $filters?)` | GET | `/letter-comments?letter_id={id}` |
| `listPage(…)` / `iterateAll(…, $pageSize?)` | GET | `/letter-comments?letter_id={id}` (paginated) |
| `get($id)` / `getOrFail($id)` | GET | `/letter-comments/{id}` |
| `create($letterId, $options)` | POST | `/letter-comments` |
| `delete($id)` | DELETE | `/letter-comments/{id}` |

### `/letter-tags`

| Method | HTTP | Path |
|---|---|---|
| `list($letterId, $filters?)` | GET | `/letter-tags?letter_id={id}` |
| `listPage($letterId, $filters?)` / `iterateAll($letterId, $filters?, $pageSize?)` | GET | `/letter-tags?letter_id={id}` (paginated) |
| `cloud()` | GET | `/letter-tags` |
| `get($id)` / `getOrFail($id)` | GET | `/letter-tags/{id}` |
| `create($letterId, $options)` | POST | `/letter-tags` |
| `delete($id)` | DELETE | `/letter-tags/{id}` |

## Methods

### Letters

#### `list(LetterFilter|array $filters = []): list<Letter>`

Billomat filters by `client_id`, `contact_id`, `supplier_id`, `status`, `from`, `to`, `tags` and more.

The typed filter `LetterFilter` is recommended. Its camelCase fields are `clientId`, `contactId`, `supplierId`, `status` (`list<LetterStatus>`), `from`/`to` (`DateTimeImmutable`), `label`, `intro` and `tags`. Multi-value fields are lists and are sent comma-separated. On top of that there are `orderBy`, `page`, `perPage` and `extra` for further query parameters. A plain array still works: it is passed through to the query string as is, and array values are sent as `key[]=…`.

```php
use Justpilot\Billomat\Api\LetterFilter;
use Justpilot\Billomat\Api\OrderBy;
use Justpilot\Billomat\Model\Enum\LetterStatus;

$drafts = $billomat->letters->list(new LetterFilter(
    status: [LetterStatus::DRAFT],
    orderBy: OrderBy::desc('created'),
));
```

`listPage()` takes the same filters and returns a `Page` with pagination metadata; `iterateAll()` walks through all pages lazily. See [Pagination and filtering](../concepts/pagination-and-filtering.md).

#### `get(int $id): ?Letter`

Returns `null` on 404. `getOrFail()` throws a `NotFoundException` instead.

#### `create(LetterCreateOptions $options): Letter`

Only `clientId` is required. The actual letter text (`intro` plus `note`) and the layout template (`templateId`) are up to you.

```php
use Justpilot\Billomat\Api\LetterCreateOptions;

$opts = new LetterCreateOptions(
    clientId: 12345,
    subject: 'Update to our privacy policy',
    date: new DateTimeImmutable('2026-06-02'),
    intro: 'Dear Sir or Madam,',
    note: "We would like to inform you about …\n\nKind regards",
);

$letter = $billomat->letters->create($opts);
```

#### `update(int $id, LetterUpdateOptions $options): Letter`

Only advisable in status `DRAFT`. Fields left at `null` are not touched on the server.

#### `complete(int $id, ?int $templateId = null): void`

Completes the draft (Billomat now reports `COMPLETED`). Optionally forces a specific layout template.

#### `email(int $id, ?EmailOptions $options = null): void`

Sends the letter by email. Without options, the defaults from the configured template apply.

```php
use Justpilot\Billomat\Api\EmailOptions;

$mail = new EmailOptions(
    to: ['client@example.com'],
    subject: 'Our letter',
    body: 'Please find our letter attached as a PDF.',
);

$billomat->letters->email($letter->id, $mail);
```

#### `pdf(int $id, ?PdfType $type = null): LetterPdf` / `pdfContent(int $id, ?PdfType $type = null): string`

`pdf()` returns a `LetterPdf` model. `pdfContent()` appends `?format=pdf` and returns the binary PDF (`UnexpectedResponseException` if the body isn't a PDF).

```php
$pdf = $billomat->letters->pdf($letter->id);                 // model
$raw = $billomat->letters->pdfContent($letter->id);        // string (PDF binary)
```

#### `upload(int $id, string $base64Pdf): void`

**Letter-specific**: uploads a complete, externally generated letter PDF into the letter. Useful when the layout isn't built with Billomat templates (e.g. finished Word or InDesign exports). This method has **no** counterpart for invoices, credit notes or reminders.

#### `uploadSignature(int $id, string $base64Pdf): void`

Attaches a version signed by the recipient.

#### `delete(int $id): void`

Only works on letters in status `DRAFT`.

### Letter comments

```php
use Justpilot\Billomat\Api\LetterCommentCreateOptions;
use Justpilot\Billomat\Model\Enum\LetterCommentActionKey;

$comment = $billomat->letters->comments->create(
    $letter->id,
    new LetterCommentCreateOptions(
        comment: 'Letter sent by post on 2026-06-03.',
    ),
);

// Show only EMAIL and UPLOAD entries
$entries = $billomat->letters->comments->list(
    $letter->id,
    [LetterCommentActionKey::EMAIL, LetterCommentActionKey::UPLOAD],
);
```

As with credit notes, the endpoint mixes free-form user comments with system audit entries. Action keys are sent as a comma-separated string.

### Letter tags

```php
use Justpilot\Billomat\Api\LetterTagCreateOptions;

$billomat->letters->tags->create(
    $letter->id,
    new LetterTagCreateOptions(name: 'privacy'),
);

$tags = $billomat->letters->tags->list($letter->id);
$cloud = $billomat->letters->tags->cloud(); // aggregated, with count
```

There is no update. To rename a tag, delete it and create it again.

## Write model: `LetterCreateOptions`

Constructor: `new LetterCreateOptions(int $clientId, …)`. `clientId` is required and the first parameter. Every field is a constructor parameter with a default and is passed as a named argument. The object is immutable (`final readonly`); each field has a `with*()` method that returns a modified copy (e.g. `$opts->withLabel('New')`).

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `clientId` | `client_id` | `int` (required) | |
| `contactId` | `contact_id` | `?int` | |
| `address` | `address` | `?string` | multi-line address, overrides the client address |
| `numberPre` | `number_pre` | `?string` | |
| `number` | `number` | `?int` | manual numbering |
| `numberLength` | `number_length` | `?int` | |
| `date` | `date` | `?\DateTimeImmutable` | `Y-m-d` |
| `subject` | `subject` | `?string` | subject line |
| `label` | `label` | `?string` | |
| `intro` | `intro` | `?string` | salutation / introduction |
| `note` | `note` | `?string` | main letter text |
| `templateId` | `template_id` | `?int` | layout template |
| `supplierId` | `supplier_id` | `?int` | recipient supplier |
| `freeTextId` | `free_text_id` | `?int` | |

## Write model: `LetterUpdateOptions`

Mirrors `LetterCreateOptions`, but `clientId` is optional (nullable). Fields left at `null` are stripped from the payload. `Clear::Field` instead sends the field as JSON `null`, and Billomat empties it, e.g. `new LetterUpdateOptions(note: Clear::Field)`.

## Write model: `LetterCommentCreateOptions`

Constructor: `new LetterCommentCreateOptions(string $comment)`. The letter ID isn't part of the options: pass it as the first argument of `create()`, which adds `letter_id` to the payload.

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `comment` | `comment` | `string` (required) | |
| `actionkey` | `actionkey` | `?LetterCommentActionKey` | usually set by Billomat |

## Write model: `LetterTagCreateOptions`

The letter ID isn't part of the options: pass it as the first argument of `create()`, which adds `letter_id` to the payload.

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `name` | `name` | `string` (required) | |

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
| `body` | `body` | `?string` | email text (not the letter content) |
| `filename` | `filename` | `?string` | name of the PDF attachment |
| `attachments` | `attachments.attachment` | `list<array{filename,mimetype,base64file}>` | additional attachments |

Empty recipient arrays are left out, so Billomat falls back to the template.

## Read model: `Letter`

`final readonly class Letter`. Much leaner than `Invoice` or `CreditNote`: no total fields, no items.

| Property | Type |
|---|---|
| `id` | `int` |
| `clientId` | `int` |
| `contactId` | `?int` |
| `created` | `?\DateTimeImmutable` |
| `status` | `?LetterStatus` |
| `date` | `?\DateTimeImmutable` |
| `address`, `subject`, `label`, `intro` | `?string` |
| `templateId` | `?int` |
| `updated` | `?\DateTimeImmutable` |
| `supplierId` | `?int` |
| `customfield` | `?string` (free-form extra field, see [Custom meta attributes](../concepts/custom-meta-attributes.md)) |
| `extra` | `array<string, mixed>` (raw fields the SDK doesn't map yet, see [Models](../concepts/models.md)) |

## Read model: `LetterComment`

| Property | Type |
|---|---|
| `id` | `int` |
| `letterId` | `int` |
| `comment` | `?string` |
| `created` | `?\DateTimeImmutable` |
| `userId` | `?int` |
| `actionkey` | `?LetterCommentActionKey` |
| `actionkeyRaw` | `?string` (raw value for unknown keys) |
| `public` | `?bool` |
| `byClient` | `?bool` |
| `emailId` | `?int` |
| `clientId` | `?int` |
| `customfield` | `?string` (free-form extra field, see [Custom meta attributes](../concepts/custom-meta-attributes.md)) |
| `extra` | `array<string, mixed>` |

## Read model: `LetterTag`

| Property | Type |
|---|---|
| `id` | `int` |
| `letterId` | `int` |
| `name` | `string` |
| `customfield` | `?string` (free-form extra field, see [Custom meta attributes](../concepts/custom-meta-attributes.md)) |
| `extra` | `array<string, mixed>` |

## Read model: `LetterTagCloudEntry`

| Property | Type |
|---|---|
| `id` | `?int` |
| `name` | `string` |
| `count` | `int` |
| `extra` | `array<string, mixed>` |

## Read model: `LetterPdf`

`final class LetterPdf`.

| Property | Type |
|---|---|
| `id`, `letterId` | `int` |
| `created` | `?\DateTimeImmutable` |
| `filename`, `mimeType` | `string` |
| `fileSize` | `int` |
| `base64file` | `string` |

`getBinary(): string` decodes `base64file` back into raw bytes.

## Enums used

- [`LetterStatus`](../../src/Model/Enum/LetterStatus.php): `DRAFT`, `OPEN`, `COMPLETED`, `CLEARED`, `CANCELED`. After `complete()`, Billomat now returns `COMPLETED`. Note: no `PAID`, no `OVERDUE`; letters have no payment lifecycle.
- [`LetterCommentActionKey`](../../src/Model/Enum/LetterCommentActionKey.php): `CREATE`, `EDIT`, `OPEN`, `COMPLETE`, `CANCEL`, `CLEAR`, `CHANGE_STATUS`, `EMAIL`, `MAIL`, `COMMENT`, `UPLOAD`. The additional `UPLOAD` key marks a letter PDF uploaded via `upload()`.
- [`PdfType`](../../src/Model/Enum/PdfType.php): shared PDF variants for `pdf()`.

## Pitfalls

- **`delete()` isn't limited to `DRAFT`.** Billomat's API deletes letters in any status, also completed ones.
- **No line items.** Unlike invoices, offers, confirmations and credit notes, letters have no list of line items. To show amounts or tables, put them as formatted text into `note` or upload a finished PDF via `upload()`.
- **`upload()` replaces the layout completely.** The uploaded PDF is used as is when sending; `templateId` and `subject` are then ignored for the PDF. This doesn't apply to the accompanying email texts.
- **No status transitions after `complete()`.** The SDK has no `cancel()`, `clear()` or `undo()` for letters because Billomat's API doesn't support them (400/404). Up to 3.x the SDK had these methods; they always failed.
- **`update()` only in DRAFT.** Once the letter is completed, Billomat ignores most fields, and there is no way back to `DRAFT`. Check the letter before calling `complete()`.
- **Setting `number` manually conflicts with auto-numbering.** If the account uses automatic numbers, `complete()` fails with a `ValidationException`.
- **`actionkey` in the comment filter is CSV, not an array.** `list($id, [...])` sends multiple action keys as a comma-separated string, not as `actionkey[]=…`.
- **Tag cloud and list share the same endpoint.** `GET /letter-tags` without `letter_id` returns the aggregated cloud (`tag` wrapper); with `letter_id` it returns the actual tags (`letter-tag` wrapper). The SDK splits this into `cloud()` and `list()`.

## End-to-end example

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\EmailOptions;
use Justpilot\Billomat\Api\LetterCommentCreateOptions;
use Justpilot\Billomat\Api\LetterCreateOptions;
use Justpilot\Billomat\Api\LetterTagCreateOptions;
use Justpilot\Billomat\BillomatClient;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

// 1) Create the letter as a draft
$opts = new LetterCreateOptions(
    clientId: 12345,
    subject: 'Update to our privacy policy',
    date: new DateTimeImmutable('2026-06-02'),
    intro: 'Dear Sir or Madam,',
    note: <<<TEXT
            we would like to inform you that our privacy policy has been updated.
            The new version takes effect on 2026-07-01.

            Kind regards
            Justpilot GmbH
            TEXT,
);

$letter = $billomat->letters->create($opts);
printf("Letter #%d created (status: %s)\n",
    $letter->id,
    $letter->status?->label() ?? 'unknown',
);

// 2) Add a tag
$billomat->letters->tags->create(
    $letter->id,
    new LetterTagCreateOptions(name: 'privacy'),
);

// 3) Complete the draft
$billomat->letters->complete($letter->id);

// 4) Send by email
$mail = new EmailOptions(
    to: ['client@example.com'],
    subject: 'Our updated privacy policy',
    body: 'Please find our letter attached as a PDF.',
);
$billomat->letters->email($letter->id, $mail);

// 5) Add an audit note
$billomat->letters->comments->create(
    $letter->id,
    new LetterCommentCreateOptions(
        comment: 'Sent on ' . new DateTimeImmutable()->format('Y-m-d') . ' to the main contact.',
    ),
);

// 6) Save the PDF locally
$pdfBinary = $billomat->letters->pdfContent($letter->id);
file_put_contents("letter-{$letter->id}.pdf", $pdfBinary);
```
