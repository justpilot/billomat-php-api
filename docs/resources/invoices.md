# Invoices (Rechnungen)

API-Wrapper für die Billomat-Ressource „Rechnungen“ unter `/invoices`.

## Zugriff

```php
$billomat->invoices
```

`Justpilot\Billomat\Api\InvoicesApi`.

## Lifecycle

Billomat-Rechnungen haben einen Status (`Justpilot\Billomat\Model\Enum\InvoiceStatus`):

```text
DRAFT ──(complete)──> OPEN ──(payments)──> PAID
                        │
                        ├──(Fälligkeit überschritten)──> OVERDUE
                        │
                        └──(cancel)──> CANCELED ──(uncancel)──> OPEN/OVERDUE/PAID
```

Konsequenzen für die API:

- **`create()`** liefert eine Rechnung im Status `DRAFT`. Erst dann ist `update()` erlaubt.
- **`complete()`** vergibt die endgültige `invoice_number`, erzeugt das PDF und wechselt in `OPEN`/`OVERDUE`/`PAID` (abhängig von Zahlungseingängen).
- **`delete()`** funktioniert ausschließlich auf `DRAFT`-Rechnungen.
- **`cancel()`** auf einer abgeschlossenen Rechnung markiert sie als `CANCELED` (löscht sie nicht). `uncancel()` macht die Stornierung rückgängig.

## Endpunkt-Übersicht

| Methode | HTTP | Pfad |
|---|---|---|
| `list()` | GET | `/invoices` |
| `get($id)` | GET | `/invoices/{id}` |
| `create($options)` | POST | `/invoices` |
| `update($id, $options)` | PUT | `/invoices/{id}` |
| `complete($id, $templateId?)` | PUT | `/invoices/{id}/complete` |
| `delete($id)` | DELETE | `/invoices/{id}` |
| `cancel($id)` | PUT | `/invoices/{id}/cancel` |
| `uncancel($id)` | PUT | `/invoices/{id}/uncancel` |
| `pdf($id, $type?, $rawPdf=false)` | GET | `/invoices/{id}/pdf` |

## Methoden

### `list(array $filters = []): list<Invoice>`

```php
$invoices = $billomat->invoices->list([
    'status' => ['OPEN', 'OVERDUE'],
    'order_by' => 'date+DESC',
    'per_page' => 20,
]);
```

Filter siehe Billomat-Doku (`client_id`, `status`, `from`, `to`, `label`, `intro`, `note`, `order_by`, `per_page` …). Array-Werte werden korrekt als `key[]=…` codiert.

### `get(int $id): ?Invoice`

Liefert `null` bei 404.

### `create(InvoiceCreateOptions $options): Invoice`

```php
use Justpilot\Billomat\Api\InvoiceCreateOptions;
use Justpilot\Billomat\Api\InvoiceItemCreateOptions;

$opts = new InvoiceCreateOptions(clientId: 12345);
$opts->title = 'Webentwicklung Mai 2026';
$opts->dueDays = 14;

$item = new InvoiceItemCreateOptions(quantity: 8.0, unitPrice: 95.0);
$item->title = 'Konzeption';
$opts->addItem($item);

$invoice = $billomat->invoices->create($opts);
// $invoice->status === InvoiceStatus::DRAFT
```

Positionen können direkt im `create()`-Call mit `addItem()` mitgegeben werden — sie landen unter `invoice-items.invoice-item` im Payload. Alternativ: leere Rechnung anlegen und Positionen separat via `$billomat->invoiceItems->create($invoiceId, …)` ergänzen, siehe [Invoice Items](invoice-items.md).

### `update(int $id, InvoiceUpdateOptions $options): Invoice`

Funktioniert nur für `DRAFT`. Positionen sind hier **nicht** veränderbar — dafür die `InvoiceItemsApi` nutzen.

### `complete(int $id, ?int $templateId = null): bool`

Schließt eine `DRAFT`-Rechnung ab.

```php
$billomat->invoices->complete($invoice->id);

// oder mit eigener Vorlage
$billomat->invoices->complete($invoice->id, templateId: 42);
```

Rückgabe `true`, wenn Billomat HTTP 200 antwortet. Nach `complete()`:

- `invoice_number` ist gesetzt,
- ein PDF wurde generiert (über `pdf()` abrufbar),
- Status wechselt auf `OPEN`, `OVERDUE` oder `PAID`.

### `delete(int $id): bool`

Nur für `DRAFT`. Anderenfalls antwortet Billomat mit 422 → `ValidationException`.

### `cancel(int $id): bool` / `uncancel(int $id): bool`

Storniert eine bereits abgeschlossene Rechnung oder macht die Stornierung rückgängig.

### `pdf(int $id, ?InvoicePdfType $type = null, bool $rawPdf = false): InvoicePdf|string`

Zwei Modi:

```php
use Justpilot\Billomat\Model\Enum\InvoicePdfType;

// 1) Standardmodus: JSON mit Base64-PDF
$pdf = $billomat->invoices->pdf($invoice->id, InvoicePdfType::SIGNED);
// $pdf ist InvoicePdf — mit getBinary() zum Dekodieren
file_put_contents('rechnung.pdf', $pdf->getBinary());

// 2) Raw-Modus: direkt application/pdf, Rückgabe ist der Binärinhalt als String
$binary = $billomat->invoices->pdf($invoice->id, InvoicePdfType::SIGNED, rawPdf: true);
file_put_contents('rechnung.pdf', $binary);
```

`type` ist optional:

- `InvoicePdfType::SIGNED` (`signed`) — signierte Version
- `InvoicePdfType::PRINT` (`print`) — Druckversion ohne Hintergrund

Ohne `type` liefert Billomat das Standard-PDF.

## Write-Modell: `InvoiceCreateOptions`

Konstruktor: `new InvoiceCreateOptions(int $clientId)` — `clientId` ist Pflicht.

### Identifikation & Nummerierung

| Property | Billomat-Feld | Typ |
|---|---|---|
| `clientId` | `client_id` | `int` (Pflicht) |
| `contactId` | `contact_id` | `?int` |
| `address` | `address` | `?string` (Default: Adresse des Kunden) |
| `numberPre` | `number_pre` | `?string` |
| `number` | `number` | `?int` |
| `numberLength` | `number_length` | `?int` |

### Datumsangaben

`?\DateTimeImmutable` — werden automatisch zu `Y-m-d` serialisiert.

| Property | Billomat-Feld |
|---|---|
| `date` | `date` |
| `supplyDate` | `supply_date` |
| `supplyDateType` | `supply_date_type` (`SupplyDateType`-Enum) |
| `dueDate` | `due_date` |
| `discountDate` | `discount_date` |

| Property | Billomat-Feld | Typ |
|---|---|---|
| `dueDays` | `due_days` | `?int` |
| `discountRate` | `discount_rate` | `?int` |
| `discountDays` | `discount_days` | `?int` |

### Beschreibungstexte

| Property | Billomat-Feld | Typ |
|---|---|---|
| `title` | `title` | `?string` |
| `label` | `label` | `?string` |
| `intro` | `intro` | `?string` |
| `note` | `note` | `?string` |

### Beträge & Konditionen

| Property | Billomat-Feld | Typ |
|---|---|---|
| `reduction` | `reduction` | `?string` (`"10"` oder `"10%"`) |
| `currencyCode` | `currency_code` | `?string` |
| `netGross` | `net_gross` | `?NetGross` |
| `quote` | `quote` | `?float` (Währungskurs) |
| `paymentTypes` | `payment_types` | `?string` (CSV, z. B. `BANK_TRANSFER,CASH`) |

### Verknüpfungen

| Property | Billomat-Feld | Typ | Zweck |
|---|---|---|---|
| `invoiceId` | `invoice_id` | `?int` | Bei Korrekturrechnungen: ID der korrigierten Rechnung |
| `offerId` | `offer_id` | `?int` | Quell-Angebot (Ressource nicht im SDK) |
| `confirmationId` | `confirmation_id` | `?int` | Quell-Auftragsbestätigung (nicht im SDK) |
| `recurringId` | `recurring_id` | `?int` | Quell-Abo (nicht im SDK) |
| `freeTextId` | `free_text_id` | `?int` | Vorlage für title/label/intro/note |
| `templateId` | `template_id` | `?int` | Vorlage für die PDF-Erzeugung |

### Positionen

Positionen werden nicht über eine Property, sondern über `addItem()` angehängt:

```php
$opts->addItem(new InvoiceItemCreateOptions(quantity: 5.0, unitPrice: 25.0));
```

`getItems(): list<InvoiceItemCreateOptions>` listet die hinzugefügten Positionen. `toArray()` rendert sie in `invoice-items.invoice-item`. Details: [Invoice Items](invoice-items.md).

## Write-Modell: `InvoiceUpdateOptions`

Schmaler Subset für Partial-Updates. Felder, die nicht gesetzt werden, bleiben unberührt. Positionen lassen sich hier **nicht** ändern.

| Property | Billomat-Feld | Typ |
|---|---|---|
| `date`, `supplyDate`, `dueDate` | analog | `?\DateTimeImmutable` |
| `supplyDateType` | `supply_date_type` | `?SupplyDateType` |
| `dueDays` | `due_days` | `?int` |
| `title`, `label`, `intro`, `note` | analog | `?string` |
| `reduction` | `reduction` | `?string` |
| `netGross` | `net_gross` | `?NetGross` |
| `currencyCode` | `currency_code` | `?string` |
| `quote` | `quote` | `?float` |
| `paymentTypes` | `payment_types` | `?string` |

## Read-Modell: `Invoice`

`final readonly class Invoice`. Die wichtigsten Felder:

| Property | Typ |
|---|---|
| `id` | `?int` |
| `clientId` | `int` |
| `contactId` | `?int` |
| `created` | `?\DateTimeImmutable` |
| `invoiceNumber` | `?string` (leer bei `DRAFT`) |
| `number`, `numberPre`, `numberLength` | `?int`, `?string`, `?int` |
| `status` | `?InvoiceStatus` |
| `date`, `supplyDate`, `dueDate`, `discountDate` | `?\DateTimeImmutable` |
| `supplyDateType` | `?SupplyDateType` |
| `dueDays`, `discountDays` | `?int` |
| `address` | `?string` |
| `discountRate`, `discountAmount` | `?float` |
| `title`, `label`, `intro`, `note`, `reduction` | `?string` |
| `totalGross`, `totalNet` | `?float` |
| `totalGrossUnreduced`, `totalNetUnreduced` | `?float` |
| `paidAmount`, `openAmount` | `?float` |
| `netGross` | `?NetGross` |
| `currencyCode`, `paymentTypes` | `?string` |
| `quote` | `?float` |
| `invoiceId`, `offerId`, `confirmationId`, `recurringId` | `?int` |
| `taxes` | `list<array{name:string,rate:float,amount:float}>` — Steuer-Zusammenfassung |
| `customerportalUrl` | `?string` |
| `templateId` | `?int` |
| `items` | `list<InvoiceItem>` — falls die API die Positionen mitliefert |

`Invoice::fromArray()` normalisiert die `taxes`-Struktur (Billomat liefert einzelne Einträge mal als Objekt, mal als Liste) und die mitgelieferten `invoice-items`.

## Verwendete Enums

- [`InvoiceStatus`](../../src/Model/Enum/InvoiceStatus.php): `DRAFT`, `OPEN`, `OVERDUE`, `PAID`, `CANCELED`. Hat `label(): string` für UI-Labels (z. B. „Bezahlt“).
- [`InvoicePdfType`](../../src/Model/Enum/InvoicePdfType.php): `SIGNED` (`signed`), `PRINT` (`print`).
- [`NetGross`](../../src/Model/Enum/NetGross.php): `NET`, `GROSS`, `SETTINGS`.
- [`SupplyDateType`](../../src/Model/Enum/SupplyDateType.php): `SUPPLY_DATE`, `DELIVERY_DATE`, `SUPPLY_TEXT`, `DELIVERY_TEXT`.

## Stolpersteine

- **`update()` nur auf `DRAFT`.** Sobald `complete()` lief, ändert ein PUT die Rechnung nicht mehr. Stattdessen `cancel()` + neue Rechnung, oder Korrekturrechnung mit `invoiceId`-Verknüpfung.
- **`delete()` nur auf `DRAFT`.** Eine abgeschlossene Rechnung wird `cancel()`-d, nicht gelöscht (Buchhaltungsgründe).
- **`pdf()` setzt `complete()` voraus.** Bei `DRAFT`-Rechnungen existiert noch kein PDF — der Endpoint liefert je nach Account 404 oder einen Fehler.
- **Single-Item-List-Quirk.** Wie bei allen Ressourcen normalisiert `list()` automatisch.
- **`pdf(rawPdf: true)` umgeht das `getJson()`-Dispatching.** Wenn du den Aufruf in einem Mock-Test einbaust, achte darauf, dass der Mock auch `application/pdf` zurückgibt — der HTTP-Status wird trotzdem auf Fehler geprüft.

## End-to-End-Beispiel

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\InvoiceCreateOptions;
use Justpilot\Billomat\Api\InvoiceItemCreateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\Enum\InvoicePdfType;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

// 1) Entwurf anlegen
$opts = new InvoiceCreateOptions(clientId: 12345);
$opts->title = 'Beratung Juni 2026';
$opts->date = new DateTimeImmutable('today');
$opts->dueDays = 14;

$item1 = new InvoiceItemCreateOptions(quantity: 4.0, unitPrice: 120.0);
$item1->title = 'Workshop';
$opts->addItem($item1);

$item2 = new InvoiceItemCreateOptions(quantity: 2.0, unitPrice: 85.0);
$item2->title = 'Nachgespräch';
$opts->addItem($item2);

$invoice = $billomat->invoices->create($opts);
printf("Entwurf #%d angelegt, Status %s\n", $invoice->id, $invoice->status?->label());

// 2) Abschließen
$billomat->invoices->complete($invoice->id);

// 3) Frisch laden, jetzt mit Rechnungsnummer
$completed = $billomat->invoices->get($invoice->id);
printf(
    "Abgeschlossen: %s%d (Brutto: %.2f %s)\n",
    $completed->numberPre,
    $completed->number,
    $completed->totalGross,
    $completed->currencyCode,
);

// 4) PDF speichern
$pdf = $billomat->invoices->pdf($invoice->id, InvoicePdfType::SIGNED, rawPdf: true);
file_put_contents(sprintf('rechnung-%d.pdf', $invoice->id), $pdf);
```
