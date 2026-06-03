<!-- Quelle: https://www.billomat.com/api/gutschriften/ -->

# Credit Notes (Gutschriften)

API-Wrapper für Gutschriften unter `/credit-notes` und ihre vier Sub-Ressourcen (`/credit-note-items`, `/credit-note-comments`, `/credit-note-tags`, `/credit-note-payments`).

## Zugriff

```php
$billomat->creditNotes         // Gutschriften selbst
$billomat->creditNoteItems     // Positionen
$billomat->creditNoteComments  // Aktivitäts-Log / freie Kommentare
$billomat->creditNoteTags      // Schlagworte
$billomat->creditNotePayments  // Auszahlungen an den Kunden
```

## Modell

Eine Gutschrift (Credit Note) ist eine Korrektur oder Erstattung an den Kunden. Sie kann eigenständig stehen oder über `invoiceId` an eine Original-Rechnung gehängt werden (Korrekturgutschrift). Der Lifecycle ist statusgetrieben:

```
DRAFT  ── complete() ──▶  OPEN  ── Payment (mark_credit_note_as_paid=1) ──▶  PAID
  │                        │
  │                        └── cancel() ──▶  CANCELED  ── uncancel() ──▶  OPEN
  └── delete() (nur im DRAFT erlaubt)
```

Nur Gutschriften im Status `DRAFT` sind voll editierbar — `update()` an einer abgeschlossenen Gutschrift wirft in der Regel `ValidationException`.

## Endpunkt-Übersicht

### `/credit-notes`

| Methode | HTTP | Pfad |
|---|---|---|
| `list($filters?)` | GET | `/credit-notes` |
| `get($id)` | GET | `/credit-notes/{id}` |
| `create($options)` | POST | `/credit-notes` |
| `update($id, $options)` | PUT | `/credit-notes/{id}` |
| `delete($id)` | DELETE | `/credit-notes/{id}` |
| `complete($id, $templateId?)` | PUT | `/credit-notes/{id}/complete` |
| `cancel($id)` | PUT | `/credit-notes/{id}/cancel` |
| `uncancel($id)` | PUT | `/credit-notes/{id}/uncancel` |
| `email($id, $options?)` | POST | `/credit-notes/{id}/email` |
| `pdf($id, $type?, $rawPdf?)` | GET | `/credit-notes/{id}/pdf` |
| `uploadSignature($id, $base64Pdf)` | PUT | `/credit-notes/{id}/upload-signature` |

### `/credit-note-items`

| Methode | HTTP | Pfad |
|---|---|---|
| `listByCreditNote($creditNoteId, $query?)` | GET | `/credit-note-items?credit_note_id={id}` |
| `get($id)` | GET | `/credit-note-items/{id}` |
| `create($creditNoteId, $options)` | POST | `/credit-note-items` |
| `update($id, $options)` | PUT | `/credit-note-items/{id}` |
| `delete($id)` | DELETE | `/credit-note-items/{id}` |

### `/credit-note-comments`

| Methode | HTTP | Pfad |
|---|---|---|
| `listByCreditNote($creditNoteId, $actionKeys?)` | GET | `/credit-note-comments?credit_note_id={id}` |
| `get($id)` | GET | `/credit-note-comments/{id}` |
| `create($options)` | POST | `/credit-note-comments` |
| `delete($id)` | DELETE | `/credit-note-comments/{id}` |

### `/credit-note-tags`

| Methode | HTTP | Pfad |
|---|---|---|
| `listByCreditNote($creditNoteId)` | GET | `/credit-note-tags?credit_note_id={id}` |
| `cloud()` | GET | `/credit-note-tags` |
| `get($id)` | GET | `/credit-note-tags/{id}` |
| `create($options)` | POST | `/credit-note-tags` |
| `delete($id)` | DELETE | `/credit-note-tags/{id}` |

### `/credit-note-payments`

| Methode | HTTP | Pfad |
|---|---|---|
| `list($filters?)` | GET | `/credit-note-payments` |
| `get($id)` | GET | `/credit-note-payments/{id}` |
| `create($options)` | POST | `/credit-note-payments` |
| `delete($id)` | DELETE | `/credit-note-payments/{id}` |

## Methoden

### Credit Notes

#### `list(array $filters = []): list<CreditNote>`

Filter werden 1:1 als Query-String an Billomat durchgereicht (`client_id`, `contact_id`, `status`, `from`, `to`, `tags`, `invoice_id`, …). Array-Werte landen als `key[]=…`.

```php
$drafts = $billomat->creditNotes->list([
    'status' => 'DRAFT',
    'order_by' => 'created+DESC',
]);
```

#### `get(int $id): ?CreditNote`

Liefert `null` bei 404. Eingebettete `credit-note-items` und `taxes` werden automatisch hydriert.

#### `create(CreditNoteCreateOptions $options): CreditNote`

Items können direkt im `create()`-Call per `addItem()` mitgegeben werden — sie landen unter `credit-note-items.credit-note-item` im Payload.

```php
use Justpilot\Billomat\Api\CreditNoteCreateOptions;
use Justpilot\Billomat\Api\CreditNoteItemCreateOptions;

$opts = new CreditNoteCreateOptions(clientId: 12345);
$opts->title = 'Korrektur Rechnung 2026-0042';
$opts->invoiceId = 9876;
$opts->date = new DateTimeImmutable('2026-06-02');

$item = new CreditNoteItemCreateOptions(quantity: 1.0, unitPrice: -19.90);
$item->title = 'Rückerstattung Hosting (Mai)';
$opts->addItem($item);

$creditNote = $billomat->creditNotes->create($opts);
```

#### `update(int $id, CreditNoteUpdateOptions $options): CreditNote`

Nur im Status `DRAFT` ratsam. Items werden hier **nicht** akzeptiert — die `CreditNoteItemsApi` benutzen.

#### `complete(int $id, ?int $templateId = null): bool`

Status `DRAFT → OPEN`. Optional kann eine konkrete `templateId` für das PDF mitgegeben werden, sonst greift das Account-Default-Template. Gibt `true` zurück, wenn Billomat mit 200 antwortet.

#### `cancel(int $id): bool` / `uncancel(int $id): bool`

`cancel()` setzt den Status auf `CANCELED`; `uncancel()` kehrt das zurück nach `OPEN`. Beides ist nur an abgeschlossenen Gutschriften sinnvoll.

#### `email(int $id, ?CreditNoteEmailOptions $options = null): bool`

Ohne Optionen verwendet Billomat alle Default-Werte des Kunden bzw. der hinterlegten E-Mail-Vorlage. Wirft bei fehlender Empfänger-Konfiguration `ValidationException`.

```php
use Justpilot\Billomat\Api\CreditNoteEmailOptions;

$mail = new CreditNoteEmailOptions();
$mail->to = ['kunde@example.com'];
$mail->bcc = ['buchhaltung@meinefirma.de'];
$mail->subject = 'Ihre Gutschrift';
$mail->body = 'Anbei die Gutschrift zur Rechnung 2026-0042.';

$billomat->creditNotes->email($creditNote->id, $mail);
```

#### `pdf(int $id, ?InvoicePdfType $type = null, bool $rawPdf = false): CreditNotePdf|string`

Liefert per Default ein `CreditNotePdf`-Modell (Metadaten + base64-Body). Mit `$rawPdf = true` wird `?format=pdf` gehängt und der binäre PDF-Inhalt zurückgegeben.

```php
$pdf = $billomat->creditNotes->pdf($creditNote->id);                 // Model
$raw = $billomat->creditNotes->pdf($creditNote->id, rawPdf: true);   // string (PDF binary)
```

#### `uploadSignature(int $id, string $base64Pdf): bool`

Hängt eine vom Kunden unterschriebene PDF-Version an die Gutschrift.

#### `delete(int $id): bool`

Funktioniert ausschließlich an Gutschriften im Status `DRAFT`.

### Credit Note Items

```php
use Justpilot\Billomat\Api\CreditNoteItemCreateOptions;

$item = $billomat->creditNoteItems->create(
    $creditNote->id,
    new CreditNoteItemCreateOptions(quantity: 1.0, unitPrice: -19.90),
);

$items = $billomat->creditNoteItems->listByCreditNote($creditNote->id);
$billomat->creditNoteItems->update($item->id, $newOpts);
$billomat->creditNoteItems->delete($item->id);
```

`create()` injiziert `credit_note_id` automatisch in den Payload — `CreditNoteItemCreateOptions` selbst hat dieses Feld nicht.

### Credit Note Comments

```php
use Justpilot\Billomat\Api\CreditNoteCommentCreateOptions;
use Justpilot\Billomat\Model\Enum\CreditNoteCommentActionKey;

$comment = $billomat->creditNoteComments->create(
    new CreditNoteCommentCreateOptions(
        creditNoteId: $creditNote->id,
        comment: 'Kunde bestätigt Rückerstattung per Mail.',
    ),
);

// Nur EMAIL-/PAYMENT-Einträge des Aktivitäts-Logs
$entries = $billomat->creditNoteComments->listByCreditNote(
    $creditNote->id,
    [CreditNoteCommentActionKey::EMAIL, CreditNoteCommentActionKey::PAYMENT],
);
```

Billomat führt im selben Endpunkt sowohl freie User-Kommentare als auch System-Audit-Einträge (Status-Wechsel, Payment, E-Mail-Versand). Über `$actionKeys` lässt sich nach Typ filtern — die Werte werden als CSV (`actionkey=EMAIL,PAYMENT`) übergeben.

### Credit Note Tags

```php
use Justpilot\Billomat\Api\CreditNoteTagCreateOptions;

$billomat->creditNoteTags->create(
    new CreditNoteTagCreateOptions(creditNoteId: $creditNote->id, name: 'rückerstattung'),
);

$tags = $billomat->creditNoteTags->listByCreditNote($creditNote->id);
$cloud = $billomat->creditNoteTags->cloud(); // aggregiert, mit count
```

Ein Update gibt es nicht — zum Umbenennen muss der Tag gelöscht und neu angelegt werden.

### Credit Note Payments

```php
use Justpilot\Billomat\Api\CreditNotePaymentCreateOptions;
use Justpilot\Billomat\Model\Enum\InvoicePaymentType;

$payment = new CreditNotePaymentCreateOptions(
    creditNoteId: $creditNote->id,
    amount: 19.90,
);
$payment->type = InvoicePaymentType::BANK_TRANSFER;
$payment->date = new DateTimeImmutable('2026-06-05');
$payment->markCreditNoteAsPaid = true;

$billomat->creditNotePayments->create($payment);
```

Erst beim Anlegen einer Auszahlung mit `markCreditNoteAsPaid = true` (Wire-Wert `1`) wechselt die Gutschrift in den Status `PAID`. Ohne das Flag bleibt sie `OPEN` und behält einen `openAmount > 0`.

## Write-Modell: `CreditNoteCreateOptions`

Konstruktor: `new CreditNoteCreateOptions(int $clientId)`.

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `clientId` | `client_id` | `int` (Pflicht) | |
| `contactId` | `contact_id` | `?int` | |
| `address` | `address` | `?string` | mehrzeilige Anschrift, überschreibt Kunden-Adresse |
| `numberPre` | `number_pre` | `?string` | |
| `number` | `number` | `?int` | manuelle Nummerierung — kollidiert mit Auto-Numbering |
| `numberLength` | `number_length` | `?int` | |
| `date` | `date` | `?\DateTimeImmutable` | formatiert als `Y-m-d` |
| `title` | `title` | `?string` | |
| `label` | `label` | `?string` | |
| `intro` | `intro` | `?string` | |
| `note` | `note` | `?string` | |
| `reduction` | `reduction` | `?string` | absolut oder mit `%`-Suffix |
| `currencyCode` | `currency_code` | `?string` | ISO 4217, z. B. `EUR` |
| `netGross` | `net_gross` | `?NetGross` | `NET` / `GROSS` |
| `quote` | `quote` | `?float` | Umrechnungskurs für Fremdwährung |
| `invoiceId` | `invoice_id` | `?int` | Quell-Rechnung bei Korrekturgutschrift |
| `freeTextId` | `free_text_id` | `?int` | |
| `templateId` | `template_id` | `?int` | |

Items werden über `addItem(CreditNoteItemCreateOptions)` angehängt; `getItems()` listet sie auf. `toArray()` rendert sie unter `credit-note-items.credit-note-item`.

## Write-Modell: `CreditNoteUpdateOptions`

Spiegelt `CreditNoteCreateOptions` ohne `clientId`-Pflicht (jetzt nullable) und ohne Items-Helper. Felder, die `null` bleiben, werden aus dem Payload gestrippt und damit serverseitig nicht angefasst.

## Write-Modell: `CreditNoteItemCreateOptions`

Konstruktor: `new CreditNoteItemCreateOptions(float $quantity, float $unitPrice)`. Beide Werte sind Pflicht und werden **auch dann mitgeschickt, wenn sie `0` sind** (Sonderfall im `toArray()`-Filter).

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `quantity` | `quantity` | `float` (Pflicht) | |
| `unitPrice` | `unit_price` | `float` (Pflicht) | negativ möglich, üblich bei Rückerstattung |
| `type` | `type` | `?InvoiceItemType` | wiederverwendet aus Invoice-Domain |
| `articleId` | `article_id` | `?int` | |
| `title` | `title` | `?string` | |
| `description` | `description` | `?string` | |
| `unit` | `unit` | `?string` | |
| `taxName` | `tax_name` | `?string` | |
| `taxRate` | `tax_rate` | `?float` | |
| `taxChangedManually` | `tax_changed_manually` | `?bool` | |
| `reduction` | `reduction` | `?string` | |
| `position` | `position` | `?int` | Sortierung in der PDF |

## Write-Modell: `CreditNoteCommentCreateOptions`

Konstruktor: `new CreditNoteCommentCreateOptions(int $creditNoteId, string $comment)`.

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `creditNoteId` | `credit_note_id` | `int` (Pflicht) | |
| `comment` | `comment` | `string` (Pflicht) | |
| `actionkey` | `actionkey` | `?CreditNoteCommentActionKey` | wird in der Regel von Billomat gesetzt; manuell nur für Audit-Marker sinnvoll |

## Write-Modell: `CreditNoteTagCreateOptions`

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `creditNoteId` | `credit_note_id` | `int` (Pflicht) | |
| `name` | `name` | `string` (Pflicht) | |

## Write-Modell: `CreditNotePaymentCreateOptions`

Konstruktor: `new CreditNotePaymentCreateOptions(int $creditNoteId, float $amount)`.

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `creditNoteId` | `credit_note_id` | `int` (Pflicht) | wird immer mitgeschickt |
| `amount` | `amount` | `float` (Pflicht) | wird immer mitgeschickt |
| `date` | `date` | `?\DateTimeImmutable` | `Y-m-d` |
| `comment` | `comment` | `?string` | |
| `transactionPurpose` | `transaction_purpose` | `?string` | Verwendungszweck der Überweisung |
| `type` | `type` | `?InvoicePaymentType` | dieselbe Enum wie bei Rechnungs-Zahlungen |
| `markCreditNoteAsPaid` | `mark_credit_note_as_paid` | `bool` | wird **immer** mitgesendet, als `1` oder `0` |

## Write-Modell: `CreditNoteEmailOptions`

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `emailTemplateId` | `email_template_id` | `?int` | |
| `from` | `from` | `?string` | |
| `to` | `recipients.to` | `list<string>` | |
| `cc` | `recipients.cc` | `list<string>` | |
| `bcc` | `recipients.bcc` | `list<string>` | |
| `subject` | `subject` | `?string` | |
| `body` | `body` | `?string` | |
| `filename` | `filename` | `?string` | Name des angehängten PDFs |
| `attachments` | `attachments.attachment` | `list<array{filename,mimetype,base64file}>` | zusätzliche Anhänge |

Leere Empfänger-Arrays werden im Payload weggelassen, damit Billomat den Default aus der E-Mail-Vorlage zieht.

## Read-Modell: `CreditNote`

`final readonly class CreditNote`.

| Property | Typ |
|---|---|
| `id` | `?int` |
| `clientId` | `int` |
| `contactId` | `?int` |
| `created` | `?\DateTimeImmutable` |
| `creditNoteNumber` | `?string` (formatierte Nummer inkl. Präfix) |
| `number`, `numberPre`, `numberLength` | `?int`, `?string`, `?int` |
| `status` | `?CreditNoteStatus` |
| `date` | `?\DateTimeImmutable` |
| `address`, `title`, `label`, `intro`, `note` | `?string` |
| `totalGross`, `totalNet` | `?float` |
| `netGross` | `?NetGross` |
| `reduction` | `?string` |
| `totalGrossUnreduced`, `totalNetUnreduced` | `?float` |
| `paidAmount`, `openAmount` | `?float` |
| `currencyCode` | `?string` |
| `quote` | `?float` |
| `invoiceId` | `?int` |
| `freeTextId`, `templateId` | `?int` |
| `taxes` | `list<array{name:string,rate:float,amount:float}>` |
| `customerportalUrl` | `?string` |
| `items` | `list<CreditNoteItem>` |

## Read-Modell: `CreditNoteItem`

| Property | Typ |
|---|---|
| `id` | `?int` |
| `creditNoteId` | `?int` |
| `articleId` | `?int` |
| `position` | `?int` |
| `unit` | `?string` |
| `quantity`, `unitPrice` | `float` |
| `taxName`, `taxRate`, `taxChangedManually` | `?string`, `?float`, `?bool` |
| `title`, `description`, `reduction` | `?string` |
| `type` | `?InvoiceItemType` |
| `totalGross`, `totalNet` | `?float` |
| `totalGrossUnreduced`, `totalNetUnreduced` | `?float` |
| `created` | `?\DateTimeImmutable` |

## Read-Modell: `CreditNoteComment`

| Property | Typ |
|---|---|
| `id` | `?int` |
| `creditNoteId` | `int` |
| `comment` | `?string` |
| `created` | `?\DateTimeImmutable` |
| `userId` | `?int` |
| `actionkey` | `?CreditNoteCommentActionKey` |
| `actionkeyRaw` | `?string` (Roh-Wert, falls Billomat einen unbekannten Key liefert) |

## Read-Modell: `CreditNoteTag`

| Property | Typ |
|---|---|
| `id` | `?int` |
| `creditNoteId` | `int` |
| `name` | `string` |

## Read-Modell: `CreditNoteTagCloudEntry`

| Property | Typ |
|---|---|
| `id` | `?int` |
| `name` | `string` |
| `count` | `int` |

## Read-Modell: `CreditNotePayment`

| Property | Typ |
|---|---|
| `id` | `?int` |
| `creditNoteId` | `int` |
| `date` | `?\DateTimeImmutable` |
| `amount` | `float` |
| `type` | `?InvoicePaymentType` |
| `comment`, `transactionPurpose` | `?string` |
| `created` | `?\DateTimeImmutable` |
| `userId` | `?int` |

## Read-Modell: `CreditNotePdf`

`final class CreditNotePdf` (nicht `readonly`, da als Wrapper konzipiert).

| Property | Typ |
|---|---|
| `id`, `creditNoteId` | `int` |
| `created` | `?\DateTimeImmutable` |
| `filename`, `mimeType` | `string` |
| `fileSize` | `int` |
| `base64file` | `string` |

`getBinary(): string` dekodiert `base64file` zurück in Roh-Bytes.

## Verwendete Enums

- [`CreditNoteStatus`](../../src/Model/Enum/CreditNoteStatus.php): `DRAFT`, `OPEN`, `PAID`, `CANCELED`.
- [`CreditNoteCommentActionKey`](../../src/Model/Enum/CreditNoteCommentActionKey.php): `CREATE`, `EDIT`, `OPEN`, `COMPLETE`, `CANCEL`, `UNCANCEL`, `CHANGE_STATUS`, `PAYMENT`, `EMAIL`, `MAIL`, `COMMENT`.
- [`NetGross`](../../src/Model/Enum/NetGross.php): `NET`, `GROSS`.
- [`InvoiceItemType`](../../src/Model/Enum/InvoiceItemType.php): wiederverwendet für Item-Typen.
- [`InvoicePaymentType`](../../src/Model/Enum/InvoicePaymentType.php): wiederverwendet für Zahlungsarten (`BANK_TRANSFER`, `PAYPAL`, `CASH`, …).
- [`InvoicePdfType`](../../src/Model/Enum/InvoicePdfType.php): `pdf()` akzeptiert dieselben PDF-Varianten wie bei Invoices.

## Stolpersteine

- **`update()` nur im DRAFT.** Sobald die Gutschrift abgeschlossen ist, ignoriert Billomat die meisten Felder oder antwortet mit 422. Vor dem Editieren stets `cancel()` → `update()` → `complete()` in Erwägung ziehen.
- **Items im Update-Payload werden ignoriert.** `CreditNoteUpdateOptions` hat bewusst kein `addItem()`. Positionsänderungen laufen über die `CreditNoteItemsApi`.
- **`markCreditNoteAsPaid` ist immer im Payload.** Anders als die anderen Felder wird `mark_credit_note_as_paid` nicht weggefiltert — der Default `false` wird als `0` mitgesendet. Beim Setzen auf `true` flippt der Status auf `PAID`, sobald die Summe der Payments den Bruttobetrag erreicht.
- **`quantity` und `unitPrice` bleiben im Payload, auch wenn sie `0` sind.** `CreditNoteItemCreateOptions::toArray()` whitelistet sie explizit gegen den `array_filter`.
- **Negative Beträge bei Items.** Eine Gutschrift gleicht eine Rechnung aus — die Praxis ist, im `unitPrice` einen negativen Wert anzugeben (z. B. `-19.90`). Billomat akzeptiert das ohne Umweg.
- **`number` manuell setzen kollidiert mit Auto-Numbering.** Wenn der Account auf automatische Nummern eingestellt ist, führt ein manuelles `number` zu einer `ValidationException` bei `complete()`.
- **`uncancel()` setzt zurück auf `OPEN`, nicht auf `DRAFT`.** Eine versehentlich stornierte Gutschrift kann reaktiviert werden, aber sie ist danach nicht wieder editierbar.
- **`actionkey` im Comment-Filter ist CSV, kein Array.** `listByCreditNote($id, [...])` übergibt mehrere ActionKeys als komma-separierten String, nicht als `actionkey[]=…`.
- **Tag-Cloud vs. Liste am selben Endpunkt.** `GET /credit-note-tags` ohne `credit_note_id` liefert die aggregierte Cloud (`tag`-Wrapper); mit `credit_note_id` die konkreten Tags (`credit-note-tag`-Wrapper). Der SDK trennt das in `cloud()` und `listByCreditNote()`.

## End-to-End-Beispiel

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\CreditNoteCreateOptions;
use Justpilot\Billomat\Api\CreditNoteEmailOptions;
use Justpilot\Billomat\Api\CreditNoteItemCreateOptions;
use Justpilot\Billomat\Api\CreditNotePaymentCreateOptions;
use Justpilot\Billomat\Api\CreditNoteTagCreateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\Enum\InvoicePaymentType;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

// 1) Korrekturgutschrift zur Rechnung 9876 anlegen
$opts = new CreditNoteCreateOptions(clientId: 12345);
$opts->invoiceId = 9876;
$opts->title = 'Korrektur Rechnung 2026-0042';
$opts->date = new DateTimeImmutable('2026-06-02');
$opts->intro = 'Wir erstatten Ihnen den anteiligen Hosting-Betrag für Mai.';

$item = new CreditNoteItemCreateOptions(quantity: 1.0, unitPrice: -19.90);
$item->title = 'Rückerstattung Webhosting Basic (Mai)';
$opts->addItem($item);

$creditNote = $billomat->creditNotes->create($opts);
printf("Gutschrift #%d angelegt (Status: %s)\n",
    $creditNote->id,
    $creditNote->status?->label() ?? 'unbekannt',
);

// 2) Abschließen — DRAFT → OPEN
$billomat->creditNotes->complete($creditNote->id);

// 3) Tag setzen
$billomat->creditNoteTags->create(
    new CreditNoteTagCreateOptions(creditNoteId: $creditNote->id, name: 'rückerstattung'),
);

// 4) Per E-Mail versenden
$mail = new CreditNoteEmailOptions();
$mail->to = ['kunde@example.com'];
$mail->bcc = ['buchhaltung@meinefirma.de'];
$mail->subject = 'Ihre Gutschrift zur Rechnung 2026-0042';
$mail->body = "Guten Tag,\n\nanbei die Gutschrift.\n\nViele Grüße";
$billomat->creditNotes->email($creditNote->id, $mail);

// 5) Auszahlung verbuchen — OPEN → PAID
$payment = new CreditNotePaymentCreateOptions(
    creditNoteId: $creditNote->id,
    amount: 19.90,
);
$payment->type = InvoicePaymentType::BANK_TRANSFER;
$payment->date = new DateTimeImmutable('2026-06-05');
$payment->transactionPurpose = 'Erstattung Hosting Mai';
$payment->markCreditNoteAsPaid = true;

$billomat->creditNotePayments->create($payment);

// 6) PDF abholen
$pdfBinary = $billomat->creditNotes->pdf($creditNote->id, rawPdf: true);
file_put_contents("gutschrift-{$creditNote->id}.pdf", $pdfBinary);
```
