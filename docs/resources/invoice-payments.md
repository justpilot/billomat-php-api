<!-- Quelle: https://www.billomat.com/api/rechnungen/zahlungen/ -->

# Invoice Payments (Zahlungen)

API-Wrapper für Rechnungszahlungen unter `/invoice-payments`.

## Zugriff

```php
$billomat->invoicePayments
```

`Justpilot\Billomat\Api\InvoicePaymentsApi`.

## Endpunkt-Übersicht

| Methode | HTTP | Pfad |
|---|---|---|
| `list($filters?)` | GET | `/invoice-payments` |
| `get($id)` | GET | `/invoice-payments/{id}` |
| `create($options)` | POST | `/invoice-payments` |
| `delete($id)` | DELETE | `/invoice-payments/{id}` |

Es gibt keine `update()`-Methode — Billomat unterstützt das Bearbeiten einer Zahlung nicht. Korrekturen erfolgen über Löschen + Neuanlage.

## Methoden

### `list(array $filters = []): list<InvoicePayment>`

Filter laut Billomat-Doku:

| Filter | Beschreibung |
|---|---|
| `invoice_id` | Nur Zahlungen zu einer bestimmten Rechnung |
| `from` | Datum von (`YYYY-MM-DD`) |
| `to` | Datum bis (`YYYY-MM-DD`) |
| `type` | Zahlungsart, CSV möglich (`BANK_TRANSFER,PAYPAL`) |
| `user_id` | Eingebende User-ID |

```php
$payments = $billomat->invoicePayments->list([
    'invoice_id' => $invoice->id,
    'order_by' => 'date+DESC',
]);
```

### `get(int $id): ?InvoicePayment`

Liefert `null` bei 404.

### `create(InvoicePaymentCreateOptions $options): InvoicePayment`

```php
use Justpilot\Billomat\Api\InvoicePaymentCreateOptions;
use Justpilot\Billomat\Model\Enum\InvoicePaymentType;

$opts = new InvoicePaymentCreateOptions(invoiceId: 98765, amount: 1190.00);
$opts->date = new DateTimeImmutable('2026-06-01');
$opts->type = InvoicePaymentType::BANK_TRANSFER;
$opts->comment = 'Überweisung Eingang vom 01.06.';
$opts->markInvoiceAsPaid = true;

$payment = $billomat->invoicePayments->create($opts);
```

### `delete(int $id): bool`

Setzt laut Billomat-Doku den Status der Rechnung zurück auf `OPEN` oder `OVERDUE`, wenn die Zahlung sie vorher auf `PAID` gesetzt hatte.

## Write-Modell: `InvoicePaymentCreateOptions`

Konstruktor: `new InvoicePaymentCreateOptions(int $invoiceId, float $amount)`. Beide Werte sind Pflicht.

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `invoiceId` | `invoice_id` | `int` | Pflicht (Konstruktor) |
| `amount` | `amount` | `float` | Pflicht (Konstruktor) |
| `date` | `date` | `?\DateTimeImmutable` | Default: heute |
| `comment` | `comment` | `?string` | Freitext |
| `transactionPurpose` | `transaction_purpose` | `?string` | Verwendungszweck |
| `type` | `type` | `?InvoicePaymentType` | siehe Enum unten |
| `markInvoiceAsPaid` | `mark_invoice_as_paid` | `bool` | Default `false`. Bei `true` markiert Billomat die Rechnung als `PAID`, auch wenn der Zahlbetrag den offenen Betrag nicht exakt deckt. |

`toArray()` filtert `null`-Werte heraus, erhält aber `invoice_id`, `amount` und `mark_invoice_as_paid` (das wird immer als `1`/`0` mitgeschickt).

## Read-Modell: `InvoicePayment`

`final readonly class InvoicePayment`.

| Property | Typ |
|---|---|
| `id` | `?int` |
| `invoiceId` | `int` |
| `date` | `?\DateTimeImmutable` |
| `amount` | `float` |
| `type` | `?InvoicePaymentType` |
| `comment` | `?string` |

## Verwendete Enums

[`InvoicePaymentType`](../../src/Model/Enum/InvoicePaymentType.php) bildet die Zahlungsarten ab — mit deutschsprachigen Labels via `label()`:

| Case | Wire-Wert | Label |
|---|---|---|
| `INVOICE_CORRECTION` | `INVOICE_CORRECTION` | Korrekturrechnung |
| `CREDIT_NOTE` | `CREDIT_NOTE` | Gutschrift |
| `BANK_CARD` | `BANK_CARD` | Bankkarte |
| `BANK_TRANSFER` | `BANK_TRANSFER` | Überweisung |
| `DEBIT` | `DEBIT` | Lastschrift |
| `CASH` | `CASH` | Barzahlung |
| `CHECK` | `CHECK` | Scheck |
| `PAYPAL` | `PAYPAL` | PayPal |
| `CREDIT_CARD` | `CREDIT_CARD` | Kreditkarte |
| `COUPON` | `COUPON` | Gutschein |
| `MISC` | `MISC` | Sonstiges |

## Stolpersteine

- **Keine Updates.** Eine Zahlung lässt sich nicht ändern — nur löschen und neu anlegen.
- **`markInvoiceAsPaid` überstimmt den offenen Betrag.** Setze das Flag nur, wenn du wirklich auf `PAID` flippen willst, auch bei Teilzahlung.
- **Statuswechsel der Rechnung.** Nach `create()` kann die Rechnung in `PAID` (`amount` deckt offenen Betrag) oder bleibt `OPEN`/`OVERDUE` (Teilzahlung). Bei `delete()` springt sie zurück.
- **Status-Filter.** Beim Listen ist `type` ein CSV-Feld auf API-Seite — du kannst entweder einen Wert oder mehrere kommagetrennt mitgeben.

## End-to-End-Beispiel

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\InvoicePaymentCreateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\Enum\InvoicePaymentType;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

$invoiceId = 98765;

// Zahlung anlegen
$opts = new InvoicePaymentCreateOptions(invoiceId: $invoiceId, amount: 590.00);
$opts->type = InvoicePaymentType::BANK_TRANSFER;
$opts->date = new DateTimeImmutable('today');
$opts->transactionPurpose = 'Rechnung RE-2026-0042';

$payment = $billomat->invoicePayments->create($opts);
printf("Zahlung #%d angelegt: %.2f EUR per %s\n", $payment->id, $payment->amount, $payment->type?->label());

// Alle Zahlungen zur Rechnung listen
$paymentsForInvoice = $billomat->invoicePayments->list([
    'invoice_id' => $invoiceId,
    'order_by' => 'date+ASC',
]);

$sum = array_sum(array_map(static fn($p) => $p->amount, $paymentsForInvoice));
printf("Bislang verbucht: %.2f EUR über %d Eingänge\n", $sum, count($paymentsForInvoice));

// Letzte Zahlung wieder entfernen (Storno)
$billomat->invoicePayments->delete($payment->id);
```
