<!-- Source: https://www.billomat.com/api/rechnungen/ -->

# Invoices (Rechnungen)

API wrapper for the Billomat invoice resource at `/invoices`.

## Access

```php
$billomat->invoices
```

`Justpilot\Billomat\Api\InvoicesApi`.

## Lifecycle

Billomat invoices have a status (`Justpilot\Billomat\Model\Enum\InvoiceStatus`):

```text
DRAFT ──(complete)──> OPEN ──(payments)──> PAID
                        │
                        ├──(due date passed)──> OVERDUE
                        │
                        └──(cancel)──> CANCELED ──(uncancel)──> OPEN/OVERDUE/PAID
```

What this means for the API:

- **`create()`** returns an invoice with status `DRAFT`. Only drafts can be changed with `update()`.
- **`complete()`** assigns the final `invoice_number`, generates the PDF and moves the invoice to `OPEN`, `OVERDUE` or `PAID` (depending on received payments).
- **`delete()`** works in any status, also on completed invoices. Use it for drafts; cancel completed invoices with `cancel()` instead (see below).
- **`cancel()`** marks a completed invoice as `CANCELED` (it does not delete it). `uncancel()` reverts the cancellation.

## Endpoints

| Method | HTTP | Path |
|---|---|---|
| `list($filters?)` | GET | `/invoices` |
| `listPage($filters?)` | GET | `/invoices` (one page with metadata) |
| `iterateAll($filters?, $pageSize?)` | GET | `/invoices` (lazy, across all pages) |
| `listGrouped($groupBy, $filters?)` | GET | `/invoices?group_by=…` |
| `get($id)` / `getOrFail($id)` | GET | `/invoices/{id}` |
| `create($options)` | POST | `/invoices` |
| `update($id, $options)` | PUT | `/invoices/{id}` |
| `complete($id, $templateId?)` | PUT | `/invoices/{id}/complete` |
| `delete($id)` | DELETE | `/invoices/{id}` |
| `cancel($id)` | PUT | `/invoices/{id}/cancel` |
| `uncancel($id)` | PUT | `/invoices/{id}/uncancel` |
| `email($id, $options?)` | POST | `/invoices/{id}/email` |
| `mail($id, $options?)` | POST | `/invoices/{id}/mail` |
| `uploadSignature($id, $base64Pdf)` | PUT | `/invoices/{id}/upload-signature` |
| `encash($id)` | PUT | `/invoices/{id}/encash` |
| `pdf($id, $type?)` | GET | `/invoices/{id}/pdf` |
| `pdfContent($id, $type?)` | GET | `/invoices/{id}/pdf?format=pdf` |

Sub-resources with their own pages: [items](invoice-items.md) (`$billomat->invoices->items`), [payments](invoice-payments.md) (`->payments`), [comments](invoice-comments.md) (`->comments`), [tags](invoice-tags.md) (`->tags`). See also [recurring invoices](recurrings.md).

## Methods

### `list(InvoiceFilter|array $filters = []): list<Invoice>`

```php
use Justpilot\Billomat\Api\InvoiceFilter;
use Justpilot\Billomat\Api\OrderBy;
use Justpilot\Billomat\Model\Enum\InvoiceStatus;

$invoices = $billomat->invoices->list(new InvoiceFilter(
    status: [InvoiceStatus::OPEN, InvoiceStatus::OVERDUE],
    from: new DateTimeImmutable('2026-01-01'),
    orderBy: OrderBy::desc('date')->thenAsc('invoice_number'),
    perPage: 20,
));

// The same filter built with withers:
$filter = new InvoiceFilter()
    ->withStatus(InvoiceStatus::OPEN, InvoiceStatus::OVERDUE)
    ->withPerPage(20);
```

The typed `InvoiceFilter` is the recommended way to filter. Multi-value fields (`status`, `paymentType`, `tags`) are lists and are sent comma-separated (Billomat combines them with OR), `from`/`to` are `DateTimeImmutable`, and the `orderBy` above becomes `order_by=date+DESC,invoice_number+ASC`. A plain array of Billomat parameters still works; array values in it are encoded as `key[]=…`.

| Billomat filter | `InvoiceFilter` field |
|---|---|
| `client_id` / `contact_id` | `clientId` / `contactId` |
| `invoice_number` | `invoiceNumber` |
| `status` | `status` (`list<InvoiceStatus>`) |
| `payment_type` | `paymentType` (`list<PaymentType>`) |
| `from` / `to` | `from` / `to` |
| `label` / `intro` / `note` | `label` / `intro` / `note` |
| `tags` | `tags` |
| `article_id` | `articleId` |
| `order_by` | `orderBy` |
| `page` / `per_page` | `page` / `perPage` |

Other query parameters (e.g. filters on custom attributes) go into `extra: [...]`; they override fields with the same name.

### `listPage(InvoiceFilter|array $filters = []): Page<Invoice>` / `iterateAll(InvoiceFilter|array $filters = [], int $pageSize = 100): Generator<int, Invoice>`

`listPage()` returns one page plus the pagination metadata (`@page`, `@per_page`, `@total`) as `Justpilot\Billomat\Pagination\Page`. `iterateAll()` fetches page after page and yields the invoices one by one; `page` and `per_page` in the filter are overridden. Details: [Pagination](../advanced/pagination.md).

```php
foreach ($billomat->invoices->iterateAll(new InvoiceFilter(status: [InvoiceStatus::OPEN])) as $invoice) {
    // …
}
```

### `listGrouped(InvoiceGroupBy|array $groupBy, InvoiceFilter|array $filters = []): list<InvoiceGroup>`

Aggregated listing via `?group_by=…`. Instead of individual invoices, Billomat returns total rows, one per group.

```php
use Justpilot\Billomat\Model\Enum\InvoiceGroupBy;

// Revenue per client
$byClient = $billomat->invoices->listGrouped(InvoiceGroupBy::CLIENT);
foreach ($byClient as $group) {
    printf("Client %d: %s EUR gross\n", $group->clientId, $group->totalGross?->format(2) ?? '-');
}

// Combine several axes; the order defines the aggregation:
//   "client,year" → first by client, then by year.
$byClientYear = $billomat->invoices->listGrouped([
    InvoiceGroupBy::CLIENT,
    InvoiceGroupBy::YEAR,
]);

// Drill down into one group: Billomat includes the matching filter set
// in $group->invoiceParams (as an array, which list() accepts).
$first = $byClient[0];
$detail = $billomat->invoices->list($first->invoiceParams);
```

Which identifier field of `InvoiceGroup` is filled depends on `group_by`:

| `group_by` | Filled identifier field |
|---|---|
| `client` | `clientId` |
| `status` | `status` (`?InvoiceStatus`) |
| `day` / `week` / `month` / `year` | `day` / `week` / `month` / `year` (each `?string`) |

`totalGross` and `totalNet` (`?Money`) hold the aggregated totals.

### `get(int $id): ?Invoice` / `getOrFail(int $id): Invoice`

`get()` returns `null` on 404; `getOrFail()` throws a `NotFoundException` instead.

### `create(InvoiceCreateOptions $options): Invoice`

```php
use Justpilot\Billomat\Api\InvoiceCreateOptions;
use Justpilot\Billomat\Api\InvoiceItemCreateOptions;
use Justpilot\Billomat\Model\Money;

$opts = new InvoiceCreateOptions(clientId: 12345, title: 'Web development May 2026', dueDays: 14);

$item = new InvoiceItemCreateOptions(quantity: 8.0, unitPrice: Money::of('95'), title: 'Concept');
$opts = $opts->withItem($item);

$invoice = $billomat->invoices->create($opts);
// $invoice->status === InvoiceStatus::DRAFT
```

Items can be passed directly in the `create()` call via `items: […]` or `withItem()`; they end up under `invoice-items.invoice-item` in the payload. Alternatively, create an empty invoice and add items separately with `$billomat->invoices->items->create($invoiceId, …)`, see [Invoice items](invoice-items.md).

### `update(int $id, InvoiceUpdateOptions $options): Invoice`

Only works on `DRAFT` invoices. Items **cannot** be changed here; use `$billomat->invoices->items` for that.

### `complete(int $id, ?int $templateId = null): void`

Completes a `DRAFT` invoice.

```php
$billomat->invoices->complete($invoice->id);

// or with a specific template
$billomat->invoices->complete($invoice->id, templateId: 42);
```

Errors are thrown as exceptions. After `complete()`:

- `invoice_number` is set,
- a PDF has been generated (available via `pdf()`),
- the status changes to `OPEN`, `OVERDUE` or `PAID`.

### `delete(int $id): void`

Only for `DRAFT` invoices. Otherwise Billomat responds with 422 → `ValidationException`.

### `cancel(int $id): void` / `uncancel(int $id): void`

Cancels a completed invoice or reverts the cancellation.

### `email(int $id, ?EmailOptions $options = null): void`

Sends the invoice by email with the PDF attached. Without `$options`, Billomat takes all defaults from the account (sender, recipient from the client record, subject/body from the default template).

```php
use Justpilot\Billomat\Api\EmailOptions;

// 1) Minimal: use the defaults
$billomat->invoices->email($invoice->id);

// 2) With custom recipients and text
$opts = new EmailOptions(
    from: 'invoices@mycompany.example',
    to: ['client@example.com'],
    bcc: ['accounting@mycompany.example'],
    subject: 'Your invoice for June',
    body: "Dear Sir or Madam,\n\nplease find your invoice attached.\n",
    filename: 'invoice-2026-0042',
);

$billomat->invoices->email($invoice->id, $opts);
```

Requirements:

- The invoice must be completed with `complete()` (otherwise there is no PDF).
- `from` must be configured as a sender in the Billomat account, otherwise the API responds with a `ValidationException`.

### `mail(int $id, ?MailOptions $options = null): void`

Sends the invoice by post via the Pixelletter service. **This costs money**: Billomat charges postage and printing.

```php
use Justpilot\Billomat\Api\MailOptions;

$opts = new MailOptions(color: true, duplex: true, paperWeight: 90);

$billomat->invoices->mail($invoice->id, $opts);
```

The recipient address is taken from the address on the invoice; there is intentionally no separate address property.

### `uploadSignature(int $id, string $base64Pdf): void`

Uploads a signed PDF version of the invoice, replacing the PDF generated by Billomat.

```php
$signed = base64_encode(file_get_contents('/path/to/signed-invoice.pdf'));
$billomat->invoices->uploadSignature($invoice->id, $signed);
```

### `encash(int $id): void`

Hands an invoice over to Billomat's debt collection service (Inkasso). According to the Billomat docs, the invoice must be `OPEN` or `OVERDUE`.

```php
$billomat->invoices->encash($invoice->id);
```

### `pdf(int $id, ?PdfType $type = null): InvoicePdf` / `pdfContent(int $id, ?PdfType $type = null): string`

Two methods for the same endpoint:

```php
use Justpilot\Billomat\Model\Enum\PdfType;

// 1) pdf(): JSON with a Base64 PDF
$pdf = $billomat->invoices->pdf($invoice->id, PdfType::SIGNED);
// $pdf is an InvoicePdf; getBinary() decodes it
file_put_contents('invoice.pdf', $pdf->getBinary());

// 2) pdfContent(): application/pdf directly, returns the binary content as a string
$binary = $billomat->invoices->pdfContent($invoice->id, PdfType::SIGNED);
file_put_contents('invoice.pdf', $binary);
```

`type` is optional:

- `PdfType::SIGNED` (`signed`): signed version
- `PdfType::PRINT` (`print`): print version without background

Without `type`, Billomat returns the standard PDF. In `pdfContent()`, an empty body or a body without the `%PDF-` header throws an `UnexpectedResponseException`.

## Write model: `InvoiceCreateOptions`

Constructor: `new InvoiceCreateOptions(int $clientId, …)`. `clientId` is required and the first parameter. All other fields are constructor parameters with a default and are passed as named arguments. The object is immutable (`final readonly`); every field has a `with*()` method that returns a modified copy (e.g. `$opts->withTitle('New')`).

### Identification and numbering

| Property | Billomat field | Type |
|---|---|---|
| `clientId` | `client_id` | `int` (required) |
| `contactId` | `contact_id` | `?int` |
| `address` | `address` | `?string` (default: the client's address) |
| `numberPre` | `number_pre` | `?string` |
| `number` | `number` | `?int` |
| `numberLength` | `number_length` | `?int` |

### Dates

`?\DateTimeImmutable`, serialized as `Y-m-d`.

| Property | Billomat field |
|---|---|
| `date` | `date` |
| `supplyDate` | `supply_date` |
| `supplyDateType` | `supply_date_type` (`SupplyDateType` enum) |
| `dueDate` | `due_date` |
| `discountDate` | `discount_date` |

| Property | Billomat field | Type |
|---|---|---|
| `dueDays` | `due_days` | `?int` |
| `discountRate` | `discount_rate` | `?int` |
| `discountDays` | `discount_days` | `?int` |

### Texts

| Property | Billomat field | Type |
|---|---|---|
| `title` | `title` | `?string` |
| `label` | `label` | `?string` |
| `intro` | `intro` | `?string` |
| `note` | `note` | `?string` |

### Amounts and terms

| Property | Billomat field | Type |
|---|---|---|
| `reduction` | `reduction` | `?string` (`"10"` or `"10%"`) |
| `currencyCode` | `currency_code` | `?string` |
| `netGross` | `net_gross` | `?NetGross` |
| `quote` | `quote` | `?float` (exchange rate) |
| `paymentTypes` | `payment_types` | `list<PaymentType>` (Billomat: comma-separated, e.g. `BANK_TRANSFER,CASH`) |

### References

| Property | Billomat field | Type | Purpose |
|---|---|---|---|
| `invoiceId` | `invoice_id` | `?int` | For correction invoices: ID of the corrected invoice |
| `offerId` | `offer_id` | `?int` | Source [offer](offers.md) |
| `confirmationId` | `confirmation_id` | `?int` | Source [confirmation](confirmations.md) |
| `recurringId` | `recurring_id` | `?int` | Source [recurring invoice](recurrings.md) |
| `freeTextId` | `free_text_id` | `?int` | Free text template for title/label/intro/note |
| `templateId` | `template_id` | `?int` | Template for PDF generation |

### Items

Items are passed via the constructor parameter `items` (`list<InvoiceItemCreateOptions>`) and are then available in the `$items` property. `withItem()` returns a copy with one more item:

```php
$opts = $opts->withItem(new InvoiceItemCreateOptions(quantity: 5.0, unitPrice: Money::of('25')));
```

`toArray()` renders them into `invoice-items.invoice-item`. Details: [Invoice items](invoice-items.md).

## Write model: `InvoiceUpdateOptions`

For partial updates; it has the same fields as `InvoiceCreateOptions` except `items`, all optional (named constructor arguments, `with*()` methods). `null` means "don't send", the value stays unchanged. `Clear::Field` (`Justpilot\Billomat\Api\Clear`) sends the field as JSON `null` so Billomat empties it, e.g. `new InvoiceUpdateOptions(note: Clear::Field)`. Items **cannot** be changed here.

| Property | Billomat field | Type |
|---|---|---|
| `clientId`, `contactId` | same | `int\|Clear\|null` |
| `number`, `numberPre`, `numberLength` | same | `int\|Clear\|null`, `string\|Clear\|null`, `int\|Clear\|null` |
| `date`, `supplyDate`, `dueDate`, `discountDate` | same | `\DateTimeImmutable\|Clear\|null` |
| `supplyDateType` | `supply_date_type` | `SupplyDateType\|Clear\|null` |
| `dueDays`, `discountDays` | same | `int\|Clear\|null` |
| `discountRate` | `discount_rate` | `float\|Clear\|null` |
| `address`, `title`, `label`, `intro`, `note` | same | `string\|Clear\|null` |
| `reduction` | `reduction` | `string\|Clear\|null` |
| `netGross` | `net_gross` | `NetGross\|Clear\|null` |
| `currencyCode` | `currency_code` | `string\|Clear\|null` |
| `quote` | `quote` | `float\|Clear\|null` |
| `invoiceId`, `offerId`, `confirmationId`, `recurringId` | same | `int\|Clear\|null` |
| `templateId`, `freeTextId` | same | `int\|Clear\|null` |
| `paymentTypes` | `payment_types` | `list<PaymentType>\|Clear` |

## Write model: `EmailOptions`

Payload for `email()`. One class shared by all document types: offers, confirmations, credit notes, delivery notes, reminders and letters use the same fields. All fields are optional; Billomat fills missing values from the selected template or the account defaults.

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `emailTemplateId` | `email_template_id` | `?int` | ID of an email template stored in the account |
| `from` | `from` | `?string` | Must be an approved sender in the account |
| `to` | `recipients.to` | `list<string>` | TO recipients |
| `cc` | `recipients.cc` | `list<string>` | CC recipients |
| `bcc` | `recipients.bcc` | `list<string>` | BCC recipients |
| `subject` | `subject` | `?string` | |
| `body` | `body` | `?string` | Plain-text body |
| `filename` | `filename` | `?string` | File name of the PDF attachment, without `.pdf` |
| `attachments` | `attachments.attachment[]` | `list<array{filename:string,mimetype:string,base64file:string}>` | Additional attachments |

`toArray()` drops empty recipient lists and `null` fields.

## Write model: `MailOptions`

Payload for `mail()` (Pixelletter), shared by all document types (invoices, offers, confirmations, credit notes, delivery notes, letters, reminders). Like the other options classes it is `final readonly` with named constructor arguments. The recipient address always comes from the document's address, so there is no `to` field.

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `color` | `color` | `?bool` | Serialized as `1`/`0` |
| `duplex` | `duplex` | `?bool` | Double-sided printing |
| `paperWeight` | `paper_weight` | `?int` | g/m², 80 or 90 |
| `attachments` | `attachments.attachment[]` | `list<array{filename:string,mimetype:string,base64file:string}>` | Printed along with the invoice |

## Read model: `Invoice`

`final readonly class Invoice`. The main fields:

| Property | Type |
|---|---|
| `id` | `int` |
| `clientId` | `int` |
| `contactId` | `?int` |
| `created` | `?\DateTimeImmutable` |
| `invoiceNumber` | `?string` (empty for `DRAFT`) |
| `number`, `numberPre`, `numberLength` | `?int`, `?string`, `?int` |
| `status` | `?InvoiceStatus` |
| `date`, `supplyDate`, `dueDate`, `discountDate` | `?\DateTimeImmutable` |
| `supplyDateType` | `?SupplyDateType` |
| `dueDays`, `discountDays` | `?int` |
| `address` | `?string` |
| `discountRate`, `discountAmount` | `?float`, `?Money` |
| `title`, `label`, `intro`, `note`, `reduction` | `?string` |
| `totalGross`, `totalNet` | `?Money` |
| `totalGrossUnreduced`, `totalNetUnreduced` | `?Money` |
| `paidAmount`, `openAmount` | `?Money` |
| `netGross` | `?NetGross` |
| `currencyCode` | `?string` |
| `paymentTypes` | `list<PaymentType>` (unknown values are dropped) |
| `quote` | `?float` |
| `invoiceId`, `offerId`, `confirmationId`, `recurringId` | `?int` |
| `taxes` | `list<TaxSummary>` (`name`, `rate`, `amount` as `Money`): tax summary per rate |
| `templateId` | `?int` |
| `items` | `list<InvoiceItem>`, if the API includes the items (only `GET /invoices/{id}`) |
| `updated` | `?\DateTimeImmutable` |
| `facturxReference` | `?string` (e-invoice, read-only, not documented by Billomat) |
| `totalReduction` | `?Money` |
| `digProceeded` | `?bool` |
| `customfield` | `?string` (free extra field, see [custom meta attributes](../concepts/custom-meta-attributes.md)) |
| `extra` | `array<string, mixed>`: fields the SDK doesn't map (yet), raw; see [models](../concepts/models.md) |

`Invoice::fromArray()` normalizes the `taxes` structure (Billomat returns single entries sometimes as an object, sometimes as a list) and the embedded `invoice-items`.

## Enums

- [`InvoiceStatus`](../../src/Model/Enum/InvoiceStatus.php): `DRAFT`, `OPEN`, `OVERDUE`, `PAID`, `CANCELED`. Has `label(): string` for German UI labels (e.g. "Bezahlt").
- [`PaymentType`](../../src/Model/Enum/PaymentType.php): accepted payment types, see [Invoice payments](invoice-payments.md#enums).
- [`PdfType`](../../src/Model/Enum/PdfType.php): `SIGNED` (`signed`), `PRINT` (`print`).
- [`InvoiceGroupBy`](../../src/Model/Enum/InvoiceGroupBy.php): `CLIENT`, `STATUS`, `DAY`, `WEEK`, `MONTH`, `YEAR`, the aggregation axes for `listGrouped()`.
- [`NetGross`](../../src/Model/Enum/NetGross.php): `NET`, `GROSS`, `SETTINGS`.
- [`SupplyDateType`](../../src/Model/Enum/SupplyDateType.php): `SUPPLY_DATE`, `DELIVERY_DATE`, `SUPPLY_TEXT`, `DELIVERY_TEXT`.

<!-- json-examples:start (generated by tools/generate-json-examples.php, run `composer docs:json`) -->
## JSON on the wire

Billomat's API documentation only shows XML. These are the JSON bodies the SDK sends and receives: requests as the SDK serializes them, responses recorded in the Billomat sandbox and anonymized ([contract fixtures](../../tests/Fixtures/Contract)). Billomat sends every value as a string; the read models cast them (see [Read models](../concepts/models.md)).

::: code-group
<<< @/snippets/json/invoices.create.json [POST /invoices]
<<< @/../tests/Fixtures/Contract/invoices/get.json [GET /invoices/{id} (response)]
<<< @/../tests/Fixtures/Contract/invoices/list.json [GET /invoices (response)]
:::
<!-- json-examples:end -->

## Pitfalls

- **E-invoice (undocumented).** Billomat returns `facturx_reference` (`Invoice::$facturxReference`) in API responses but doesn't describe it in the API docs. The SDK only reads it (types derived from observed values); writing it is not supported because there is no specification.
- **`update()` only on `DRAFT`.** Once `complete()` has run, a PUT no longer changes the invoice. Use `cancel()` plus a new invoice, or a correction invoice linked via `invoiceId`.
- **`delete()` isn't limited to `DRAFT`.** Billomat's API deletes documents in any status (the sandbox returns 200 for completed and canceled ones as well). Prefer `cancel()` for completed documents: issued documents have to be kept for bookkeeping (GoBD), and deleting one leaves a gap in the number sequence.
- **`pdf()` requires `complete()`.** A `DRAFT` invoice has no PDF yet; depending on the account the endpoint returns 404 or an error.
- **Single-item list quirk.** As with all resources, `list()` normalizes it automatically.
- **`pdfContent()` doesn't go through `getJson()`.** In a mock test, make the mock return a body that starts with `%PDF-`; otherwise `UnexpectedResponseException` is thrown. The HTTP status is still checked for errors.
- **`email()` requires `complete()`.** A `DRAFT` invoice has no PDF yet; depending on the account Billomat responds with a `ValidationException` or sends an empty email.
- **`mail()` costs money.** Pixelletter charges per letter; run tests only in the sandbox.
- **`EmailOptions::$from`** must be a verified sender address in the Billomat account. Otherwise you get a 422.

## End-to-end example

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\InvoiceCreateOptions;
use Justpilot\Billomat\Api\InvoiceItemCreateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\Enum\PdfType;
use Justpilot\Billomat\Model\Money;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

// 1) Create a draft
$opts = new InvoiceCreateOptions(
    clientId: 12345,
    title: 'Consulting June 2026',
    date: new DateTimeImmutable('today'),
    dueDays: 14,
);

$item1 = new InvoiceItemCreateOptions(quantity: 4.0, unitPrice: Money::of('120'), title: 'Workshop');
$opts = $opts->withItem($item1);

$item2 = new InvoiceItemCreateOptions(quantity: 2.0, unitPrice: Money::of('85'), title: 'Follow-up meeting');
$opts = $opts->withItem($item2);

$invoice = $billomat->invoices->create($opts);
printf("Draft #%d created, status %s\n", $invoice->id, $invoice->status?->value);

// 2) Complete
$billomat->invoices->complete($invoice->id);

// 3) Reload, now with an invoice number
$completed = $billomat->invoices->getOrFail($invoice->id);
printf(
    "Completed: %s (gross: %s %s)\n",
    $completed->invoiceNumber,
    $completed->totalGross?->format(2),
    $completed->currencyCode,
);

// 4) Save the PDF
$pdf = $billomat->invoices->pdfContent($invoice->id, PdfType::SIGNED);
file_put_contents(sprintf('invoice-%d.pdf', $invoice->id), $pdf);
```
