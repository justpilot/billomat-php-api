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
  2. Create a new incoming, attach the same Base64 string via `$billomat->incomings->upload($incomingId, $base64)`, then call `$billomat->inboxDocuments->delete($id)`.
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

Lists the inbox documents. Filters according to the Billomat docs: `per_page`, `page`, `order_by`. The returned models **don't** contain the Base64 content: `base64file` is `null` in the list response. Call `get()` for the binary content.

```php
$pending = $billomat->inboxDocuments->list([
    'order_by' => 'created+DESC',
    'per_page' => 50,
]);
```

`listPage()` additionally returns the pagination metadata, `iterateAll(array $filters = [], int $pageSize = 100)` walks through all pages lazily, see [Pagination](../advanced/pagination.md).

### `get(int $id): ?InboxDocument`

Returns `null` on 404; `getOrFail(int $id): InboxDocument` throws a `NotFoundException` instead. Unlike `list()`, the detail response contains the Base64-encoded file content in `base64file`. `getBinary()` returns it directly as raw bytes.

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
    base64file: base64_encode((string) file_get_contents('/tmp/invoice-may.pdf')),
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
| `base64file` | `base64file` | `string` (required) | Content that is already Base64-encoded |
| `documentType` | `document_type` | `?string` | Document type according to Billomat's classification (e.g. `other`, `invoice`, `credit_note`); Billomat's default is `other` |
| `metadata` | `metadata` | `?array<string, mixed>` | Free-form additional data that Billomat stores as is |

## Read model: `InboxDocument`

`final readonly class InboxDocument`.

| Property | Type |
|---|---|
| `id` | `?int` |
| `created` | `?\DateTimeImmutable` |
| `filename` | `string` |
| `mimeType` | `string` |
| `fileSize` | `int` |
| `base64file` | `?string` |
| `updated` | `?\DateTimeImmutable` |
| `xrechnungExtractionsId` | `?int` (e-invoicing, read-only, not documented by Billomat) |
| `userId` | `?int` |
| `documentType` | `?string` |
| `pageCount` | `?int` |
| `fileUrl` | `?string` |
| `metadata` | `?string` |
| `customfield` | `?string` (free-form extra field, see [Custom meta attributes](../concepts/custom-meta-attributes.md)) |

Plus the helper `getBinary(): string`, which decodes `base64file` and returns the raw bytes, or an empty string if the field is empty or invalidly encoded. On `InboxDocument` instances from `list()`, `base64file` is usually `null`, so `getBinary()` returns an empty string as well.

## Enums used

None. The resource has no status enum.

## Pitfalls

- **E-invoicing (undocumented).** Billomat returns `xrechnung_extractions_id` (`InboxDocument::$xrechnungExtractionsId`) in API responses but doesn't describe it in the API docs. The SDK only reads it (type derived from observed values); writing isn't supported for lack of a specification.
- **`base64file` only via `get()`.** `list()` returns the metadata without binary content to keep responses small. To process the PDF content, load each document individually via `get($id)`.
- **The wire field is `mimetype`, not `mime_type`.** Unlike most other resources, there's no snake_case with an underscore here; Billomat expects `mimetype` as one word. The PHP property is still called `mimeType` (camelCase).
- **`metadata` has different types.** You write it as an array; the read model returns it as a raw `?string`.
- **No conversion to an incoming via the API.** The Billomat API has no `/inbox-documents/{id}/convert` or similar. To replicate the web UI behavior, call `$billomat->incomings->create()` plus `$billomat->incomings->upload()` yourself and delete the inbox document afterwards.
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
        base64file: base64_encode((string) file_get_contents($pdfPath)),
    ),
);

printf("Inbox: #%d, %d bytes\n", $doc->id, $doc->fileSize);

// 2) Later: process the inbox. Load each document individually,
//    turn it into an incoming and remove it from the inbox.
foreach ($billomat->inboxDocuments->list(['order_by' => 'created+ASC']) as $pending) {
    $full = $billomat->inboxDocuments->get($pending->id);
    if (null === $full) {
        continue;
    }

    // 2a) Create the incoming (you have to know supplier and amount yourself)
    $incomingOpts = new IncomingCreateOptions(supplierId: 4711, label: $full->filename);
    $incoming = $billomat->incomings->create($incomingOpts);

    // 2b) Attach the PDF from the inbox to the incoming (pass the Base64 string through)
    $billomat->incomings->upload(
        $incoming->id,
        $full->base64file ?? '',
    );

    // 2c) Remove the document from the inbox
    $billomat->inboxDocuments->delete($full->id);

    printf("→ #%d moved to incoming #%d\n", $full->id, $incoming->id);
}
```
