<!-- Quelle: https://www.billomat.com/api/rechnungen/ -->

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
| `listGrouped($groupBy, $filters?)` | GET | `/invoices?group_by=…` |
| `get($id)` | GET | `/invoices/{id}` |
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
| `pdf($id, $type?, $rawPdf=false)` | GET | `/invoices/{id}/pdf` |

Verwandte Ressourcen mit eigener Doku: [Positionen](invoice-items.md), [Zahlungen](invoice-payments.md), [Kommentare](invoice-comments.md), [Schlagworte](invoice-tags.md), [Abo-Rechnungen](recurrings.md).

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

### `listGrouped(InvoiceGroupBy|array $groupBy, array $filters = []): list<InvoiceGroup>`

Aggregiertes Listing über `?group_by=…`. Statt einzelner Rechnungen liefert Billomat Summen-Zeilen — eine pro Gruppe.

```php
use Justpilot\Billomat\Model\Enum\InvoiceGroupBy;

// Umsatz pro Kunde
$byClient = $billomat->invoices->listGrouped(InvoiceGroupBy::CLIENT);
foreach ($byClient as $group) {
    printf("Kunde %d: %.2f EUR brutto\n", $group->clientId, $group->totalGross);
}

// Mehrere Achsen kombinieren — Reihenfolge bestimmt die Aggregation:
//   "client,year" → zuerst nach Kunde, dann nach Jahr.
$byClientYear = $billomat->invoices->listGrouped([
    InvoiceGroupBy::CLIENT,
    InvoiceGroupBy::YEAR,
]);

// Drill-down auf eine konkrete Gruppe — Billomat liefert das passende
// Filterset in $group->invoiceParams gleich mit.
$first = $byClient[0];
$detail = $billomat->invoices->list($first->invoiceParams);
```

Welche Felder im `InvoiceGroup` gefüllt sind, hängt vom `group_by`-Parameter ab:

| `group_by` | Gefülltes Identifier-Feld |
|---|---|
| `client` | `clientId` |
| `status` | `status` |
| `day` / `week` / `month` / `year` | `day` / `week` / `month` / `year` (jeweils `?string`) |

`totalGross` und `totalNet` enthalten die Aggregat-Summen.

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

### `email(int $id, ?InvoiceEmailOptions $options = null): bool`

Versendet die Rechnung als PDF-Anhang per E-Mail. Ohne `$options` zieht Billomat alle Defaults aus dem Account (Absender, Empfänger aus den Kunden-Stammdaten, Betreff/Body aus der Default-Vorlage).

```php
use Justpilot\Billomat\Api\InvoiceEmailOptions;

// 1) Minimal: Defaults verwenden
$billomat->invoices->email($invoice->id);

// 2) Mit eigenen Empfängern und individuellem Text
$opts = new InvoiceEmailOptions();
$opts->from = 'rechnungen@meinefirma.de';
$opts->to = ['kunde@example.com'];
$opts->bcc = ['buchhaltung@meinefirma.de'];
$opts->subject = 'Ihre Rechnung vom Juni';
$opts->body = "Sehr geehrte Damen und Herren,\n\nim Anhang Ihre Rechnung.\n";
$opts->filename = 'rechnung-2026-0042';

$billomat->invoices->email($invoice->id, $opts);
```

Voraussetzungen:

- Die Rechnung muss `complete()`-d sein (sonst existiert kein PDF).
- `from` muss im Billomat-Account als Absender hinterlegt sein, sonst antwortet die API mit `ValidationException`.

### `mail(int $id, ?InvoiceMailOptions $options = null): bool`

Verschickt die Rechnung postalisch über den Pixelletter-Service. **Kostenpflichtig** — Billomat verrechnet Porto + Druck.

```php
use Justpilot\Billomat\Api\InvoiceMailOptions;

$opts = new InvoiceMailOptions();
$opts->color = true;
$opts->duplex = true;
$opts->paperWeight = '90';

$billomat->invoices->mail($invoice->id, $opts);
```

Die Empfängeradresse wird automatisch aus der auf der Rechnung hinterlegten Adresse übernommen — eine separate Adress-Property gibt es bewusst nicht.

### `uploadSignature(int $id, string $base64Pdf): bool`

Lädt eine unterschriebene PDF-Version der Rechnung hoch und ersetzt damit das von Billomat erzeugte PDF.

```php
$signed = base64_encode(file_get_contents('/path/to/signed-invoice.pdf'));
$billomat->invoices->uploadSignature($invoice->id, $signed);
```

### `encash(int $id): bool`

Übergibt eine Rechnung an das Inkasso-Verfahren von Billomat. Laut Billomat-Doku muss die Rechnung dafür im Status `OPEN` oder `OVERDUE` sein.

```php
$billomat->invoices->encash($invoice->id);
```

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

## Write-Modell: `InvoiceEmailOptions`

Payload für `email()`. Alle Felder sind optional — Billomat füllt fehlende Werte aus der gewählten Vorlage oder den Account-Defaults.

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `emailTemplateId` | `email_template_id` | `?int` | ID einer im Account hinterlegten E-Mail-Vorlage |
| `from` | `from` | `?string` | Muss im Account als Absender freigegeben sein |
| `to` | `recipients.to` | `list<string>` | TO-Empfänger |
| `cc` | `recipients.cc` | `list<string>` | CC-Empfänger |
| `bcc` | `recipients.bcc` | `list<string>` | BCC-Empfänger |
| `subject` | `subject` | `?string` | |
| `body` | `body` | `?string` | Plain-Text-Body |
| `filename` | `filename` | `?string` | Dateiname des PDF-Anhangs, ohne `.pdf` |
| `attachments` | `attachments.attachment[]` | `list<array{filename:string,mimetype:string,base64file:string}>` | Zusätzliche Anhänge |

`toArray()` filtert leere Empfängerlisten und `null`-Felder heraus.

## Write-Modell: `InvoiceMailOptions`

Payload für `mail()` (Pixelletter). Empfängeradresse kommt fix aus der Rechnungsadresse — daher kein `to`-Feld.

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `color` | `color` | `?bool` | Wird als `1`/`0` serialisiert |
| `duplex` | `duplex` | `?bool` | Doppelseitiger Druck |
| `paperWeight` | `paper_weight` | `?string` | g/m² als String, z. B. `"80"` |
| `attachments` | `attachments.attachment[]` | `list<array{filename:string,mimetype:string,base64file:string}>` | |

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
- [`InvoiceGroupBy`](../../src/Model/Enum/InvoiceGroupBy.php): `CLIENT`, `STATUS`, `DAY`, `WEEK`, `MONTH`, `YEAR` — Aggregationsachsen für `listGrouped()`.
- [`NetGross`](../../src/Model/Enum/NetGross.php): `NET`, `GROSS`, `SETTINGS`.
- [`SupplyDateType`](../../src/Model/Enum/SupplyDateType.php): `SUPPLY_DATE`, `DELIVERY_DATE`, `SUPPLY_TEXT`, `DELIVERY_TEXT`.

## Stolpersteine

- **`update()` nur auf `DRAFT`.** Sobald `complete()` lief, ändert ein PUT die Rechnung nicht mehr. Stattdessen `cancel()` + neue Rechnung, oder Korrekturrechnung mit `invoiceId`-Verknüpfung.
- **`delete()` nur auf `DRAFT`.** Eine abgeschlossene Rechnung wird `cancel()`-d, nicht gelöscht (Buchhaltungsgründe).
- **`pdf()` setzt `complete()` voraus.** Bei `DRAFT`-Rechnungen existiert noch kein PDF — der Endpoint liefert je nach Account 404 oder einen Fehler.
- **Single-Item-List-Quirk.** Wie bei allen Ressourcen normalisiert `list()` automatisch.
- **`pdf(rawPdf: true)` umgeht das `getJson()`-Dispatching.** Wenn du den Aufruf in einem Mock-Test einbaust, achte darauf, dass der Mock auch `application/pdf` zurückgibt — der HTTP-Status wird trotzdem auf Fehler geprüft.
- **`email()` setzt `complete()` voraus.** Bei `DRAFT`-Rechnungen existiert noch kein PDF; Billomat antwortet je nach Konto mit `ValidationException` oder schickt eine leere Mail.
- **`mail()` ist kostenpflichtig.** Pixelletter rechnet pro Brief ab — Tests besser nur in der Sandbox laufen lassen.
- **`InvoiceEmailOptions::$from`** muss eine im Billomat-Account verifizierte Absenderadresse sein. Sonst kommt 422 zurück.

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
