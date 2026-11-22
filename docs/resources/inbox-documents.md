<!-- Source: https://www.billomat.com/api/eingangsrechnungen/inbox/ -->

# Inbox Documents (Posteingangs-Dokumente)

API wrapper for the Billomat inbox under `/inbox-documents`: all PDFs and images Billomat has received by email, drag and drop or API but hasn't assigned to a document yet.

## Access

```php
$billomat->inboxDocuments        // uploaded documents not yet booked
```

`Justpilot\Billomat\Api\InboxDocumentsApi`.

## Model

Inbox documents are raw attachments: Billomat stores only file metadata (`filename`, `mimetype`, `filesize`) plus the binary content as a Base64 string. They have **no** business data: no supplier, no amount, no status.

- **Relation to incomings**: inbox documents are the typical first step towards an [incoming invoice](incomings.md). The Billomat web UI has a "convert to incoming invoice" workflow; the API offers **no** dedicated endpoint for it. The conversion therefore takes two steps:
  1. Read the inbox document (`get($id)` with Base64 content).
  2. Create a new incoming with the same Base64 string as `base64File` (`new IncomingCreateOptions(..., base64File: $base64)`), then call `$billomat->inboxDocuments->delete($id)`.
- **Relation to suppliers**: none directly. Inbox documents don't know a supplier; the assignment happens only when the incoming is created.

## Endpoint overview

### `/inbox-documents`

| Method | HTTP | Path |
|---|---|---|
| `list($filters?)` | GET | `/inbox-documents` |
| `listPage($filters?)` | GET | `/inbox-documents` (one page with metadata) |
| `iterateAll($filters?, $pageSize?)` | GET | `/inbox-documents` (all pages) |
| `get($id)` / `getOrFail($id)` | GET | `/inbox-documents/{id}` |
| `create($options)` | POST | `/inbox-documents` |
| `delete($id)` | DELETE | `/inbox-documents/{id}` |

There is **no** `update()` and **no** `convertToIncoming()`; the API doesn't support these operations.

## Methods

### `list(array $filters = []): list<InboxDocument>`

Lists the inbox documents. Filters according to the Billomat docs: `per_page`, `page`, `order_by`. The returned models **don't** contain the Base64 content: `base64File` is `null` in the list response. Call `get()` for the binary content.

```php
use Justpilot\Billomat\Api\OrderBy;

$pending = $billomat->inboxDocuments->list([
    'order_by' => OrderBy::desc('created')->toQuery(),
    'per_page' => 50,
]);
```

`listPage()` additionally returns the pagination metadata, `iterateAll(array $filters = [], int $pageSize = 100)` walks through all pages lazily, see [Pagination](../advanced/pagination.md).

### `get(int $id): ?InboxDocument`

Returns `null` on 404; `getOrFail(int $id): InboxDocument` throws a `NotFoundException` instead. Unlike `list()`, the detail response contains the Base64-encoded file content in `base64File`. `getBinary()` returns it directly as raw bytes.

```php
$doc = $billomat->inboxDocuments->getOrFail(987);
file_put_contents("/tmp/{$doc->filename}", $doc->getBinary());
```

### `create(InboxDocumentCreateOptions $options): InboxDocument`

Uploads a file to the inbox.

```php
use Justpilot\Billomat\Api\InboxDocumentCreateOptions;

$opts = new InboxDocumentCreateOptions(
    filename: 'invoice-may.pdf',
    mimeType: 'application/pdf',
    base64File: base64_encode((string) file_get_contents('/tmp/invoice-may.pdf')),
);

$doc = $billomat->inboxDocuments->create($opts);
```

### `delete(int $id): void`

Removes the document from the inbox.

## Write model: `InboxDocumentCreateOptions`

The three required fields are the first constructor parameters; `documentType` and `metadata` are optional named arguments. The object is `final readonly`; `withDocumentType()` and `withMetadata()` return a modified copy.

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `filename` | `filename` | `string` (required) | Including extension, e.g. `invoice.pdf` |
| `mimeType` | `mimetype` | `string` (required) | The property is `mimeType` (camelCase), the wire field is `mimetype` (all lower case) |
| `base64File` | `base64file` | `string` (required) | Content that is already Base64-encoded |
| `documentType` | `document_type` | `?InboxDocumentType` | Helps Billomat's text recognition; Billomat's default is `OTHER` |
| `metadata` | `metadata` | `?array<string, string>` | Key → value; only the keys of `InboxDocumentMetadataKey` (`SENDER_NAME`, `TOTAL_GROSS`, `IBAN`, …). Sent as `{"data": [{"key": …, "value": …}]}`; an unknown key throws `InvalidArgumentException` before the request |

## Read model: `InboxDocument`

`final readonly class InboxDocument`.

| Property | Type |
|---|---|
| `id` | `int` |
| `created` | `?\DateTimeImmutable` |
| `filename` | `string` |
| `mimeType` | `string` |
| `fileSize` | `int` |
| `base64File` | `?string` |
| `updated` | `?\DateTimeImmutable` |
| `xrechnungExtractionsId` | `?int` (e-invoicing, read-only, not documented by Billomat) |
| `userId` | `?int` |
| `documentType` | `?InboxDocumentType` |
| `pageCount` | `?int` |
| `fileUrl` | `?string` |
| `metadata` | `array<string, string>` (key → value, empty without metadata) |
| `customField` | `?string` (free-form extra field, see [Custom meta attributes](../concepts/custom-meta-attributes.md)) |
| `extra` | `array<string, mixed>` (fields the SDK doesn't map yet) |

Plus the helper `getBinary(): string`, which decodes `base64File` and returns the raw bytes, or an empty string if the field is empty or invalidly encoded. On `InboxDocument` instances from `list()`, `base64File` is usually `null`, so `getBinary()` returns an empty string as well.

## Enums used

- `InboxDocumentType`: `BANK_STATEMENT`, `CONTRACT`, `INVOICE`, `REMINDER`, `REMITTANCE_SLIP`, `TRAVEL_EXPENSE_REPORT`, `RECEIPT`, `FUEL_RECEIPT`, `ENERGY`, `OTHER`.
- `InboxDocumentMetadataKey`: the 31 metadata keys Billomat accepts (amounts, invoice and client number, dates, sender address, bank and tax details). `label()` returns the German description.

```php
use Justpilot\Billomat\Model\Enum\InboxDocumentMetadataKey as Key;
use Justpilot\Billomat\Model\Enum\InboxDocumentType;

$options = new InboxDocumentCreateOptions(
    filename: 'receipt.pdf',
    mimeType: 'application/pdf',
    base64File: base64_encode($pdf),
    documentType: InboxDocumentType::RECEIPT,
    metadata: [Key::SENDER_NAME->value => 'ACME GmbH', Key::TOTAL_GROSS->value => '119.00'],
);

$doc = $billomat->inboxDocuments->getOrFail($id);
$doc->metadata[Key::SENDER_NAME->value] ?? null;   // 'ACME GmbH'
```

<!-- json-examples:start (generated by tools/generate-json-examples.php, run `composer docs:json`) -->
## JSON on the wire

Billomat's API documentation only shows XML. These are the JSON bodies the SDK sends and receives: requests as the SDK serializes them, responses recorded in the Billomat sandbox and anonymized ([contract fixtures](../../tests/Fixtures/Contract)). Billomat sends every value as a string; the read models cast them (see [Read models](../concepts/models.md)).

::: code-group
<<< @/snippets/json/inbox-documents.create.json [POST /inbox-documents]
<<< @/../tests/Fixtures/Contract/inbox-documents/get.json [GET /inbox-documents/{id} (response)]
<<< @/../tests/Fixtures/Contract/inbox-documents/list.json [GET /inbox-documents (response)]
:::
<!-- json-examples:end -->

## Pitfalls

- **E-invoicing (undocumented).** Billomat returns `xrechnung_extractions_id` (`InboxDocument::$xrechnungExtractionsId`) in API responses but doesn't describe it in the API docs. The SDK only reads it (type derived from observed values); writing isn't supported for lack of a specification.
- **`base64File` only via `get()`.** `list()` returns the metadata without binary content to keep responses small. To process the PDF content, load each document individually via `get($id)`.
- **The wire field is `mimetype`, not `mime_type`.** Unlike most other resources, there's no snake_case with an underscore here; Billomat expects `mimetype` as one word. The PHP property is still called `mimeType` (camelCase).
- **`metadata` only takes known keys.** Billomat rejects any other key with HTTP 400 ("Unknown meta data field"), so the SDK checks the keys against `InboxDocumentMetadataKey` first. Values are strings; Billomat doesn't document their format (amounts, dates).
- **No conversion to an incoming via the API.** The Billomat API has no `/inbox-documents/{id}/convert` or similar. To replicate the web UI behavior, call `$billomat->incomings->create()` with the document's Base64 content as `base64File` yourself and delete the inbox document afterwards.
- **Base64-encode beforehand.** `InboxDocumentCreateOptions` doesn't encode the content; the string must already be Base64. Passing raw bytes produces an upload whose PDF is broken on download.
- **No update.** Corrections (e.g. a typo in the file name) are only possible by deleting and creating the document again.
- **`mimeType` fallback on broken responses.** `InboxDocument::fromArray()` falls back to `application/octet-stream` if Billomat leaves the field empty. It doesn't crash, but it's a warning sign worth logging.

## End-to-end example

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\InboxDocumentCreateOptions;
use Justpilot\Billomat\Api\IncomingCreateOptions;
use Justpilot\Billomat\Api\OrderBy;
use Justpilot\Billomat\BillomatClient;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

// 1) Upload a PDF to the inbox
$pdfPath = '/tmp/supplier-invoice.pdf';
$doc = $billomat->inboxDocuments->create(
    new InboxDocumentCreateOptions(
        filename: basename($pdfPath),
        mimeType: 'application/pdf',
        base64File: base64_encode((string) file_get_contents($pdfPath)),
    ),
);

printf("Inbox: #%d, %d bytes\n", $doc->id, $doc->fileSize);

// 2) Later: process the inbox. Load each document individually,
//    turn it into an incoming and remove it from the inbox.
foreach ($billomat->inboxDocuments->list(['order_by' => OrderBy::asc('created')->toQuery()]) as $pending) {
    $full = $billomat->inboxDocuments->get($pending->id);
    if (null === $full) {
        continue;
    }

    // 2a) Create the incoming with the PDF from the inbox (you have to know supplier and amount yourself)
    $incomingOpts = new IncomingCreateOptions(
        supplierId: 4711,
        label: $full->filename,
        base64File: $full->base64File,
    );
    $incoming = $billomat->incomings->create($incomingOpts);

    // 2b) Remove the document from the inbox
    $billomat->inboxDocuments->delete($full->id);

    printf("→ #%d moved to incoming #%d\n", $full->id, $incoming->id);
}
```
