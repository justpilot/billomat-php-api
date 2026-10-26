<!-- Source: https://www.billomat.com/api/rechnungen/zahlungen/ -->

# Invoice Payments (Zahlungen)

API wrapper for invoice payments at `/invoice-payments`.

## Access

```php
$billomat->invoices->payments
```

The generic `Justpilot\Billomat\Api\PaymentsApi`, typed for `InvoicePayment`, `InvoicePaymentCreateOptions` and `InvoicePaymentFilter`.

## Endpoints

| Method | HTTP | Path |
|---|---|---|
| `list($invoiceId?, $filters?)` | GET | `/invoice-payments` |
| `listPage($invoiceId?, $filters?)` | GET | `/invoice-payments` (one page with metadata) |
| `iterateAll($invoiceId?, $filters?, $pageSize?)` | GET | `/invoice-payments` (all pages) |
| `get($id)` / `getOrFail($id)` | GET | `/invoice-payments/{id}` |
| `create($invoiceId, $options)` | POST | `/invoice-payments` |
| `delete($id)` | DELETE | `/invoice-payments/{id}` |

There is no `update()` method; Billomat doesn't support editing a payment. To correct one, delete it and create a new one.

## Methods

### `list(?int $parentId = null, InvoicePaymentFilter|array $filters = []): list<InvoicePayment>`

`$parentId` is the invoice ID and is sent as `invoice_id`; it wins over an `invoice_id` key in an array filter. Without it, the payments of all invoices are listed. The typed filter `InvoicePaymentFilter` (generated from the API spec) has these fields:

| Property | Billomat filter | Type | Description |
|---|---|---|---|
| `from` | `from` | `?\DateTimeImmutable` | Payments from this date |
| `to` | `to` | `?\DateTimeImmutable` | Payments up to this date |
| `type` | `type` | `list<PaymentType>` | Payment types, OR-combined (sent as `BANK_TRANSFER,PAYPAL`) |
| `userId` | `user_id` | `?int` | ID of the user who entered the payment |
| `orderBy` | `order_by` | `?OrderBy` | Sorting |
| `page` / `perPage` | `page` / `per_page` | `?int` | Pagination |
| `extra` | | `array` | Further query parameters |

```php
use Justpilot\Billomat\Api\InvoicePaymentFilter;
use Justpilot\Billomat\Api\OrderBy;
use Justpilot\Billomat\Model\Enum\PaymentType;

$payments = $billomat->invoices->payments->list($invoice->id, new InvoicePaymentFilter(
    type: [PaymentType::BANK_TRANSFER, PaymentType::PAYPAL],
    orderBy: OrderBy::desc('date'),
));
```

A raw array (`['from' => '2026-01-01']`) still works. A filter of another resource (e.g. `CreditNotePaymentFilter`) throws an `InvalidArgumentException`.

### `listPage(?int $parentId = null, InvoicePaymentFilter|array $filters = []): Page<InvoicePayment>` / `iterateAll(?int $parentId = null, InvoicePaymentFilter|array $filters = [], int $pageSize = 100): Generator<int, InvoicePayment>`

Like `list()`, with pagination metadata or as a lazy iterator over all pages. See [Pagination](../advanced/pagination.md).

### `get(int $id): ?InvoicePayment` / `getOrFail(int $id): InvoicePayment`

`get()` returns `null` on 404; `getOrFail()` throws a `NotFoundException` instead.

### `create(int $invoiceId, InvoicePaymentCreateOptions $options): InvoicePayment`

```php
use Justpilot\Billomat\Api\InvoicePaymentCreateOptions;
use Justpilot\Billomat\Model\Enum\PaymentType;
use Justpilot\Billomat\Model\Money;

$opts = new InvoicePaymentCreateOptions(
    amount: Money::of('1190.00'),
    date: new DateTimeImmutable('2026-06-01'),
    type: PaymentType::BANK_TRANSFER,
    comment: 'Bank transfer received on 2026-06-01',
    markInvoiceAsPaid: true,
);

$payment = $billomat->invoices->payments->create($invoice->id, $opts);
```

The invoice ID is the first argument; the SDK adds it to the payload as `invoice_id`.

### `delete(int $id): void`

According to the Billomat docs, this resets the invoice status to `OPEN` or `OVERDUE` if the payment had set it to `PAID`.

## Write model: `InvoicePaymentCreateOptions`

Constructor: `new InvoicePaymentCreateOptions(Money $amount, …)`. `amount` is required; the other fields are optional named arguments. The object is immutable (`final readonly`) and has a `with*()` method per field.

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `amount` | `amount` | `Money` | Required (constructor) |
| `date` | `date` | `?\DateTimeImmutable` | Default: today |
| `comment` | `comment` | `?string` | Free text |
| `transactionPurpose` | `transaction_purpose` | `?string` | Payment reference |
| `type` | `type` | `?PaymentType` | See the enum below |
| `markInvoiceAsPaid` | `mark_invoice_as_paid` | `bool` | Default `false`. With `true`, Billomat marks the invoice as `PAID` even if the amount doesn't exactly cover the open amount. |

The invoice ID isn't part of the options; `create()` takes it as an argument. `toArray()` drops `null` values but always keeps `amount` (as a string) and `mark_invoice_as_paid` (always sent as `1`/`0`).

## Read model: `InvoicePayment`

`final readonly class InvoicePayment`.

| Property | Type |
|---|---|
| `id` | `int` |
| `invoiceId` | `int` |
| `created` | `?\DateTimeImmutable` |
| `date` | `?\DateTimeImmutable` |
| `amount` | `Money` |
| `type` | `?PaymentType` |
| `comment` | `?string` |
| `userId` | `?int` |
| `transactionPurpose` | `?string` |
| `currencyCode` | `?string` |
| `quote` | `?float` |
| `paymentGroup` | `?string` |
| `finapiTransactionId` | `?string` (finAPI bank reconciliation) |
| `finapiTransactionAmount` | `?Money` (finAPI bank reconciliation) |
| `documentTitle` | `?string` |
| `customfield` | `?string` (free extra field, see [custom meta attributes](../concepts/custom-meta-attributes.md)) |
| `extra` | `array<string, mixed>`: fields the SDK doesn't map (yet), raw; see [models](../concepts/models.md) |

## Enums

[`PaymentType`](../../src/Model/Enum/PaymentType.php) maps the payment types, with German labels via `label()`:

| Case | Wire value | Label |
|---|---|---|
| `INVOICE_CORRECTION` | `INVOICE_CORRECTION` | Korrekturrechnung (correction invoice) |
| `CREDIT_NOTE` | `CREDIT_NOTE` | Gutschrift (credit note) |
| `BANK_CARD` | `BANK_CARD` | Bankkarte (bank card) |
| `BANK_TRANSFER` | `BANK_TRANSFER` | Überweisung (bank transfer) |
| `DEBIT` | `DEBIT` | Lastschrift (direct debit) |
| `CASH` | `CASH` | Barzahlung (cash) |
| `CHECK` | `CHECK` | Scheck (check) |
| `PAYPAL` | `PAYPAL` | PayPal |
| `CREDIT_CARD` | `CREDIT_CARD` | Kreditkarte (credit card) |
| `COUPON` | `COUPON` | Gutschein (voucher) |
| `MISC` | `MISC` | Sonstiges (other) |

The same enum is used for the accepted payment types of documents (`paymentTypes`, `list<PaymentType>`).

<!-- json-examples:start (generated by tools/generate-json-examples.php, run `composer docs:json`) -->
## JSON on the wire

Billomat's API documentation only shows XML. These are the JSON bodies the SDK sends and receives: requests as the SDK serializes them, responses recorded in the Billomat sandbox and anonymized ([contract fixtures](../../tests/Fixtures/Contract)). Billomat sends every value as a string; the read models cast them (see [Read models](../concepts/models.md)).

::: code-group
<<< @/snippets/json/invoice-payments.create.json [POST /invoice-payments]
<<< @/../tests/Fixtures/Contract/invoice-payments/get.json [GET /invoice-payments/{id} (response)]
<<< @/../tests/Fixtures/Contract/invoice-payments/list.json [GET /invoice-payments (response)]
:::
<!-- json-examples:end -->

## Pitfalls

- **No updates.** A payment can't be changed, only deleted and created again.
- **`markInvoiceAsPaid` overrides the open amount.** Only set the flag if you really want the invoice to switch to `PAID`, even for a partial payment.
- **Invoice status changes.** After `create()`, the invoice becomes `PAID` (if `amount` covers the open amount) or stays `OPEN`/`OVERDUE` (partial payment). After `delete()` it switches back.
- **Type filter.** When listing, `type` is a comma-separated field on the API side; `InvoicePaymentFilter::$type` takes a list of `PaymentType` cases and joins them.

## End-to-end example

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\InvoicePaymentCreateOptions;
use Justpilot\Billomat\Api\InvoicePaymentFilter;
use Justpilot\Billomat\Api\OrderBy;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\Enum\PaymentType;
use Justpilot\Billomat\Model\InvoicePayment;
use Justpilot\Billomat\Model\Money;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

$invoiceId = 98765;

// Record a payment
$opts = new InvoicePaymentCreateOptions(
    amount: Money::of('590.00'),
    type: PaymentType::BANK_TRANSFER,
    date: new DateTimeImmutable('today'),
    transactionPurpose: 'Invoice RE-2026-0042',
);

$payment = $billomat->invoices->payments->create($invoiceId, $opts);
printf("Payment #%d recorded: %s EUR via %s\n", $payment->id, $payment->amount->format(2), $payment->type?->value);

// List all payments for the invoice
$paymentsForInvoice = $billomat->invoices->payments->list($invoiceId, new InvoicePaymentFilter(
    orderBy: OrderBy::asc('date'),
));

$sum = Money::sum(...array_map(static fn (InvoicePayment $p): Money => $p->amount, $paymentsForInvoice));
printf("Recorded so far: %s EUR from %d payments\n", $sum->format(2), count($paymentsForInvoice));

// Remove the last payment again
$billomat->invoices->payments->delete($payment->id);
```
