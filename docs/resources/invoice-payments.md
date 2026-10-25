<!-- Source: https://www.billomat.com/api/rechnungen/zahlungen/ -->

# Invoice Payments (Zahlungen)

API wrapper for invoice payments at `/invoice-payments`.

## Access

```php
$billomat->invoices->payments
```

The generic `Justpilot\Billomat\Api\PaymentsApi`, typed for `InvoicePayment` and `InvoicePaymentCreateOptions`.

## Endpoints

| Method | HTTP | Path |
|---|---|---|
| `list($invoiceId?, $filters?)` | GET | `/invoice-payments` |
| `get($id)` / `getOrFail($id)` | GET | `/invoice-payments/{id}` |
| `create($options)` | POST | `/invoice-payments` |
| `delete($id)` | DELETE | `/invoice-payments/{id}` |

There is no `update()` method; Billomat doesn't support editing a payment. To correct one, delete it and create a new one.

## Methods

### `list(?int $parentId = null, array $filters = []): list<InvoicePayment>`

`$parentId` is the invoice ID and is sent as `invoice_id`. Without it, the payments of all invoices are listed. Filters according to the Billomat docs:

| Filter | Description |
|---|---|
| `invoice_id` | Only payments for a specific invoice (set via `$parentId`) |
| `from` | Date from (`YYYY-MM-DD`) |
| `to` | Date to (`YYYY-MM-DD`) |
| `type` | Payment type, comma-separated values allowed (`BANK_TRANSFER,PAYPAL`) |
| `user_id` | ID of the user who entered the payment |

```php
use Justpilot\Billomat\Api\OrderBy;

$payments = $billomat->invoices->payments->list($invoice->id, [
    'order_by' => OrderBy::desc('date')->toQuery(),
]);
```

### `get(int $id): ?InvoicePayment` / `getOrFail(int $id): InvoicePayment`

`get()` returns `null` on 404; `getOrFail()` throws a `NotFoundException` instead.

### `create(InvoicePaymentCreateOptions $options): InvoicePayment`

```php
use Justpilot\Billomat\Api\InvoicePaymentCreateOptions;
use Justpilot\Billomat\Model\Enum\PaymentType;
use Justpilot\Billomat\Model\Money;

$opts = new InvoicePaymentCreateOptions(
    invoiceId: 98765,
    amount: Money::of('1190.00'),
    date: new DateTimeImmutable('2026-06-01'),
    type: PaymentType::BANK_TRANSFER,
    comment: 'Bank transfer received on 2026-06-01',
    markInvoiceAsPaid: true,
);

$payment = $billomat->invoices->payments->create($opts);
```

### `delete(int $id): void`

According to the Billomat docs, this resets the invoice status to `OPEN` or `OVERDUE` if the payment had set it to `PAID`.

## Write model: `InvoicePaymentCreateOptions`

Constructor: `new InvoicePaymentCreateOptions(int $invoiceId, Money $amount, …)`. Both values are required; the other fields are optional named arguments. The object is immutable (`final readonly`) and has a `with*()` method per field.

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `invoiceId` | `invoice_id` | `int` | Required (constructor) |
| `amount` | `amount` | `Money` | Required (constructor) |
| `date` | `date` | `?\DateTimeImmutable` | Default: today |
| `comment` | `comment` | `?string` | Free text |
| `transactionPurpose` | `transaction_purpose` | `?string` | Payment reference |
| `type` | `type` | `?PaymentType` | See the enum below |
| `markInvoiceAsPaid` | `mark_invoice_as_paid` | `bool` | Default `false`. With `true`, Billomat marks the invoice as `PAID` even if the amount doesn't exactly cover the open amount. |

`toArray()` drops `null` values but always keeps `invoice_id`, `amount` (as a string) and `mark_invoice_as_paid` (always sent as `1`/`0`).

## Read model: `InvoicePayment`

`final readonly class InvoicePayment`.

| Property | Type |
|---|---|
| `id` | `?int` |
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

## Pitfalls

- **No updates.** A payment can't be changed, only deleted and created again.
- **`markInvoiceAsPaid` overrides the open amount.** Only set the flag if you really want the invoice to switch to `PAID`, even for a partial payment.
- **Invoice status changes.** After `create()`, the invoice becomes `PAID` (if `amount` covers the open amount) or stays `OPEN`/`OVERDUE` (partial payment). After `delete()` it switches back.
- **Type filter.** When listing, `type` is a comma-separated field on the API side; you can pass one value or several separated by commas.

## End-to-end example

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\InvoicePaymentCreateOptions;
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
    invoiceId: $invoiceId,
    amount: Money::of('590.00'),
    type: PaymentType::BANK_TRANSFER,
    date: new DateTimeImmutable('today'),
    transactionPurpose: 'Invoice RE-2026-0042',
);

$payment = $billomat->invoices->payments->create($opts);
printf("Payment #%d recorded: %s EUR via %s\n", $payment->id, $payment->amount->format(2), $payment->type?->value);

// List all payments for the invoice
$paymentsForInvoice = $billomat->invoices->payments->list($invoiceId, [
    'order_by' => OrderBy::asc('date')->toQuery(),
]);

$sum = Money::sum(...array_map(static fn (InvoicePayment $p): Money => $p->amount, $paymentsForInvoice));
printf("Recorded so far: %s EUR from %d payments\n", $sum->format(2), count($paymentsForInvoice));

// Remove the last payment again
$billomat->invoices->payments->delete($payment->id);
```
