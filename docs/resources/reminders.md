<!-- Quelle: https://www.billomat.com/api/mahnungen/ -->

# Reminders (Mahnungen)

API-Wrapper für Mahnungen unter `/reminders` und ihre zwei Sub-Ressourcen (`/reminder-items`, `/reminder-tags`).

## Zugriff

```php
$billomat->reminders      // Mahnungen selbst
$billomat->reminders->items  // Positionen einer Mahnung (read-only)
$billomat->reminders->tags   // Schlagworte
```

Die zugehörigen, frei wählbaren Mahn-Textbausteine liegen in einer eigenen Ressource: `$billomat->reminderTexts` (siehe `ReminderTextsApi`).

## Modell

Eine Mahnung (Reminder) referenziert **immer** eine zugrunde liegende Rechnung über `invoiceId` (Pflichtfeld bei `create`). Inhalt und Positionen werden vom System aus der Original-Rechnung plus dem gewählten Mahntext-Baustein (`reminderTextId`) generiert — die `reminder-items` sind dadurch nur lesbar.

Der Lifecycle ist statusgetrieben:

```
DRAFT  ── complete() ──▶  OPEN  ── Zahlung der Rechnung ──▶  PAID
  │                        │
  │                        ├── Fälligkeit überschritten ──▶  OVERDUE
  │                        │
  │                        └── cancel() ──▶  CANCELED
  └── delete() (nur im DRAFT)
```

Aus einer offenen Mahnung können weitere Mahnstufen entstehen — Billomat zählt die Mahnstufe intern hoch und nutzt dafür die als Mahnstufen in `reminderTexts` hinterlegten Vorlagen (siehe [Mahnstufen](settings-reminder-levels.md)).

## Endpunkt-Übersicht

### `/reminders`

| Methode | HTTP | Pfad |
|---|---|---|
| `list($filters?)` | GET | `/reminders` |
| `get($id)` | GET | `/reminders/{id}` |
| `create($options)` | POST | `/reminders` |
| `update($id, $options)` | PUT | `/reminders/{id}` |
| `delete($id)` | DELETE | `/reminders/{id}` |
| `complete($id, $templateId?)` | PUT | `/reminders/{id}/complete` |
| `cancel($id)` | PUT | `/reminders/{id}/cancel` |
| `email($id, $options?)` | POST | `/reminders/{id}/email` |
| `pdf($id, $type?, $rawPdf?)` | GET | `/reminders/{id}/pdf` |
| `uploadSignature($id, $base64Pdf)` | PUT | `/reminders/{id}/upload-signature` |

### `/reminder-items`

| Methode | HTTP | Pfad |
|---|---|---|
| `list($reminderId, $query?)` | GET | `/reminder-items?reminder_id={id}` |
| `get($id)` | GET | `/reminder-items/{id}` |

Read-only — es gibt **kein** `create`/`update`/`delete` und keine `ReminderItemCreateOptions`-Klasse. Items werden vom System aus der Rechnung erzeugt.

### `/reminder-tags`

| Methode | HTTP | Pfad |
|---|---|---|
| `list($reminderId)` | GET | `/reminder-tags?reminder_id={id}` |
| `cloud()` | GET | `/reminder-tags` |
| `get($id)` | GET | `/reminder-tags/{id}` |
| `create($options)` | POST | `/reminder-tags` |
| `delete($id)` | DELETE | `/reminder-tags/{id}` |

## Methoden

### Reminders

#### `list(array $filters = []): list<Reminder>`

Filter werden 1:1 als Query-String an Billomat durchgereicht (`client_id`, `contact_id`, `invoice_id`, `status`, `from`, `to`, `tags`, …). Array-Werte landen als `key[]=…`.

```php
$overdue = $billomat->reminders->list([
    'status' => 'OVERDUE',
    'order_by' => 'due_date+ASC',
]);
```

#### `get(int $id): ?Reminder`

Liefert `null` bei 404. Eingebettete `reminder-items` werden automatisch hydriert.

#### `create(ReminderCreateOptions $options): Reminder`

Pflicht ist ausschließlich die `invoiceId` — Adresse, Mahntext, Fälligkeit etc. zieht Billomat aus der Rechnung bzw. der Kunden-Konfiguration, sofern nicht überschrieben.

```php
use Justpilot\Billomat\Api\ReminderCreateOptions;

$opts = new ReminderCreateOptions(invoiceId: 9876);
$opts->reminderTextId = 42;
$opts->dueDays = 7;
$opts->subject = 'Zahlungserinnerung zu Rechnung 2026-0042';

$reminder = $billomat->reminders->create($opts);
```

#### `update(int $id, ReminderUpdateOptions $options): Reminder`

Nur im Status `DRAFT` ratsam. Die `invoiceId` ist hier nicht mehr änderbar — die Bindung an die Rechnung steht fest.

#### `complete(int $id, ?int $templateId = null): void`

Status `DRAFT → OPEN`. Mit optionaler `templateId` lässt sich ein konkretes Layout-Template forcieren.

#### `cancel(int $id): void`

Setzt den Status auf `CANCELED`. Eine `uncancel()`-Methode existiert für Mahnungen **nicht** — eine stornierte Mahnung muss ggf. neu angelegt werden.

#### `email(int $id, ?ReminderEmailOptions $options = null): void`

Ohne Optionen verwendet Billomat alle Default-Werte aus der hinterlegten E-Mail-Vorlage.

```php
use Justpilot\Billomat\Api\ReminderEmailOptions;

$mail = new ReminderEmailOptions();
$mail->to = ['kunde@example.com'];
$mail->subject = 'Erinnerung: offene Rechnung 2026-0042';

$billomat->reminders->email($reminder->id, $mail);
```

#### `pdf(int $id, ?InvoicePdfType $type = null, bool $rawPdf = false): ReminderPdf|string`

Per Default ein `ReminderPdf`-Modell (Metadaten + base64-Body). Mit `$rawPdf = true` wird `?format=pdf` gehängt und der binäre PDF-Inhalt direkt zurückgegeben.

```php
$pdf = $billomat->reminders->pdf($reminder->id);                 // Model
$raw = $billomat->reminders->pdf($reminder->id, rawPdf: true);   // string (PDF binary)
```

#### `uploadSignature(int $id, string $base64Pdf): void`

Hängt eine vom Kunden unterschriebene PDF-Variante an die Mahnung.

#### `delete(int $id): void`

Funktioniert ausschließlich an Mahnungen im Status `DRAFT`.

### Reminder Items (read-only)

```php
$items = $billomat->reminders->items->list($reminder->id);
$item = $billomat->reminders->items->get($itemId);
```

Es gibt **kein** `ReminderItemCreateOptions` und keine schreibenden Endpunkte. Items zeigen Original-Rechnungsbetrag und Verzugskosten, generiert aus dem hinterlegten Mahntext-Baustein.

### Reminder Tags

```php
use Justpilot\Billomat\Api\ReminderTagCreateOptions;

$billomat->reminders->tags->create(
    new ReminderTagCreateOptions(reminderId: $reminder->id, name: 'mahnstufe-2'),
);

$tags = $billomat->reminders->tags->list($reminder->id);
$cloud = $billomat->reminders->tags->cloud(); // aggregiert, mit count
```

Ein Update existiert nicht — zum Umbenennen wird gelöscht und neu angelegt.

## Write-Modell: `ReminderCreateOptions`

Konstruktor: `new ReminderCreateOptions(int $invoiceId)`.

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `invoiceId` | `invoice_id` | `int` (Pflicht) | Quell-Rechnung; nicht mehr änderbar nach Anlage |
| `contactId` | `contact_id` | `?int` | überschreibt den Kunden-Kontakt |
| `address` | `address` | `?string` | mehrzeilige Anschrift |
| `numberPre` | `number_pre` | `?string` | |
| `number` | `number` | `?int` | manuelle Nummerierung |
| `numberLength` | `number_length` | `?int` | |
| `date` | `date` | `?\DateTimeImmutable` | formatiert als `Y-m-d` |
| `dueDays` | `due_days` | `?int` | Fälligkeit in Tagen ab `date` |
| `dueDate` | `due_date` | `?\DateTimeImmutable` | absolute Fälligkeit (überschreibt `dueDays`) |
| `subject` | `subject` | `?string` | Briefkopf-Titel |
| `label` | `label` | `?string` | |
| `intro` | `intro` | `?string` | |
| `note` | `note` | `?string` | |
| `reminderTextId` | `reminder_text_id` | `?int` | ID aus `/reminder-texts` (Mahntext-Baustein) |
| `templateId` | `template_id` | `?int` | Layout-Template-ID |

## Write-Modell: `ReminderUpdateOptions`

Spiegelt `ReminderCreateOptions` **ohne** `invoiceId` — die Bindung an die Rechnung steht fest. Felder, die `null` bleiben, werden aus dem Payload gestrippt.

## Write-Modell: `ReminderTagCreateOptions`

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `reminderId` | `reminder_id` | `int` (Pflicht) | |
| `name` | `name` | `string` (Pflicht) | |

## Write-Modell: `ReminderEmailOptions`

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `emailTemplateId` | `email_template_id` | `?int` | |
| `from` | `from` | `?string` | |
| `to` | `recipients.to` | `list<string>` | |
| `cc` | `recipients.cc` | `list<string>` | |
| `bcc` | `recipients.bcc` | `list<string>` | |
| `subject` | `subject` | `?string` | |
| `body` | `body` | `?string` | |
| `filename` | `filename` | `?string` | Name des PDF-Anhangs |
| `attachments` | `attachments.attachment` | `list<array{filename,mimetype,base64file}>` | zusätzliche Anhänge |

Leere Empfänger-Arrays werden weggelassen, sodass Billomat auf die Vorlage zurückfällt.

### Hinweis zu Reminder-Items

Es gibt **keine** `ReminderItemCreateOptions` und keine `ReminderItemUpdateOptions`. Mahnungspositionen werden serverseitig aus der Original-Rechnung und dem Mahntext-Baustein erzeugt; sie sind read-only.

## Read-Modell: `Reminder`

`final readonly class Reminder`.

| Property | Typ |
|---|---|
| `id` | `?int` |
| `clientId` | `int` |
| `contactId` | `?int` |
| `invoiceId` | `?int` |
| `created` | `?\DateTimeImmutable` |
| `status` | `?ReminderStatus` |
| `date` | `?\DateTimeImmutable` |
| `dueDays` | `?int` |
| `dueDate` | `?\DateTimeImmutable` |
| `address`, `subject`, `label`, `intro`, `note` | `?string` |
| `totalGross` | `?float` |
| `currencyCode` | `?string` |
| `reminderTextId`, `templateId` | `?int` |
| `items` | `list<ReminderItem>` |
| `updated` | `?\DateTimeImmutable` |
| `paidAmount` | `?float` (Bereits bezahlter Betrag) |
| `openAmount` | `?float` (Offener Betrag) |
| `reminderLevel` | `?int` (Mahnstufe, siehe `ReminderTextsApi`) |
| `reminderLevelName` | `?string` (Name der Mahnstufe) |
| `isOld` | `?bool` |
| `customfield` | `?string` (Freies Zusatzfeld, siehe [Custom-Meta-Attribute](../concepts/custom-meta-attributes.md)) |

## Read-Modell: `ReminderItem`

`final readonly`. Schlanker als `InvoiceItem` — keine Steuer-Detailfelder, da Reminder-Items reine Anzeigewerte aus der Original-Rechnung sind.

| Property | Typ |
|---|---|
| `id` | `?int` |
| `reminderId` | `?int` |
| `articleId` | `?int` |
| `position` | `?int` |
| `unit` | `?string` |
| `quantity`, `unitPrice` | `float` |
| `title`, `description` | `?string` |
| `total` | `?float` |
| `isCharge` | `?bool` (Position ist eine Mahngebühr) |
| `customfield` | `?string` (Freies Zusatzfeld, siehe [Custom-Meta-Attribute](../concepts/custom-meta-attributes.md)) |

## Read-Modell: `ReminderTag`

| Property | Typ |
|---|---|
| `id` | `?int` |
| `reminderId` | `int` |
| `name` | `string` |
| `customfield` | `?string` (Freies Zusatzfeld, siehe [Custom-Meta-Attribute](../concepts/custom-meta-attributes.md)) |

## Read-Modell: `ReminderTagCloudEntry`

| Property | Typ |
|---|---|
| `id` | `?int` |
| `name` | `string` |
| `count` | `int` |

## Read-Modell: `ReminderPdf`

`final class ReminderPdf`.

| Property | Typ |
|---|---|
| `id`, `reminderId` | `int` |
| `created` | `?\DateTimeImmutable` |
| `filename`, `mimeType` | `string` |
| `fileSize` | `int` |
| `base64file` | `string` |

`getBinary(): string` dekodiert `base64file` zurück in Roh-Bytes.

## Verwendete Enums

- [`ReminderStatus`](../../src/Model/Enum/ReminderStatus.php): `DRAFT`, `OPEN`, `OVERDUE`, `PAID`, `CANCELED`. Beachte: `OVERDUE` setzt Billomat automatisch, sobald das `dueDate` überschritten ist — niemals manuell mitsenden.
- [`InvoicePdfType`](../../src/Model/Enum/InvoicePdfType.php): wiederverwendet für die PDF-Varianten in `pdf()`.

Es gibt **keinen** eigenen Comment-ActionKey-Enum für Reminders — Mahnungen besitzen schlicht keine `/reminder-comments`-Ressource. Aktivitäten werden über die Comments der zugrunde liegenden Rechnung gepflegt.

## Stolpersteine

- **`invoiceId` ist Pflicht und unveränderlich.** Eine Mahnung ohne Rechnungsbezug existiert nicht; nach `create()` ist die Bindung fix und in `ReminderUpdateOptions` nicht mehr enthalten.
- **Items sind read-only.** Wer Positionen ergänzen will, muss den Mahntext-Baustein (`/reminder-texts`) anpassen oder eine andere `reminderTextId` setzen, bevor `complete()` aufgerufen wird.
- **Kein `uncancel()`.** Anders als bei Invoices oder Credit Notes lässt sich eine stornierte Mahnung nicht reaktivieren — Billomat exposed den Endpunkt schlicht nicht.
- **Kein `/reminder-comments`-Endpunkt.** Aktivitäts-Log und freie Notizen liegen am übergeordneten Rechnungs-Workflow, nicht an der Mahnung selbst.
- **`dueDate` schlägt `dueDays`.** Werden beide gesetzt, gewinnt das absolute `due_date`. Konsistenter ist es, nur eines der beiden Felder zu pflegen.
- **`reminderTextId` ohne Eintrag in `/reminder-texts` führt zu 422.** Vor dem `create()` sicherstellen, dass der Mahntext-Baustein wirklich existiert (per `$billomat->reminderTexts->list()`).
- **Status `OVERDUE` ist read-only.** Er entsteht serverseitig — ein `update()` mit `status = OVERDUE` wirkt nicht, das Feld kommt im Write-Payload gar nicht vor.
- **Mehrere Mahnstufen für dieselbe Rechnung sind möglich.** Eine zweite Mahnung zur selben Rechnung erzeugt automatisch die nächsthöhere Mahnstufe gemäß den in `reminderTexts` (`ReminderTextsApi`) konfigurierten Mahnstufen.
- **Tag-Cloud vs. Liste am selben Endpunkt.** `GET /reminder-tags` ohne `reminder_id` liefert die aggregierte Cloud (`tag`-Wrapper); mit `reminder_id` die konkreten Tags (`reminder-tag`-Wrapper). Der SDK trennt das in `cloud()` und `list()`.

## End-to-End-Beispiel

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\ReminderCreateOptions;
use Justpilot\Billomat\Api\ReminderEmailOptions;
use Justpilot\Billomat\Api\ReminderTagCreateOptions;
use Justpilot\Billomat\BillomatClient;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

// 1) Offene Rechnung herauspicken
$openInvoices = $billomat->invoices->list([
    'client_id' => 12345,
    'status' => 'OPEN',
    'order_by' => 'due_date+ASC',
]);

if ([] === $openInvoices) {
    exit('Keine offene Rechnung zu mahnen.');
}

$invoice = $openInvoices[0];

// 2) Mahntext-Baustein wählen (z. B. erste Stufe)
$reminderTexts = $billomat->reminderTexts->list();
$firstStage = $reminderTexts[0] ?? null;

// 3) Mahnung als Entwurf anlegen
$opts = new ReminderCreateOptions(invoiceId: $invoice->id);
$opts->reminderTextId = $firstStage?->id;
$opts->dueDays = 7;
$opts->subject = 'Zahlungserinnerung zu Rechnung ' . ($invoice->invoiceNumber ?? '');

$reminder = $billomat->reminders->create($opts);
printf("Mahnung #%d angelegt (Status: %s)\n",
    $reminder->id,
    $reminder->status?->label() ?? 'unbekannt',
);

// 4) Tag setzen (z. B. Mahnstufe), bevor abgeschlossen wird
$billomat->reminders->tags->create(
    new ReminderTagCreateOptions(reminderId: $reminder->id, name: 'mahnstufe-1'),
);

// 5) Abschließen — DRAFT → OPEN
$billomat->reminders->complete($reminder->id);

// 6) Per E-Mail versenden
$mail = new ReminderEmailOptions();
$mail->to = ['kunde@example.com'];
$mail->subject = 'Erinnerung: noch offene Rechnung';
$mail->body = "Guten Tag,\n\nbitte begleichen Sie unsere offene Rechnung.\n\nViele Grüße";
$billomat->reminders->email($reminder->id, $mail);

// 7) PDF lokal sichern
$pdfBinary = $billomat->reminders->pdf($reminder->id, rawPdf: true);
file_put_contents("mahnung-{$reminder->id}.pdf", $pdfBinary);

// 8) Generierte Reminder-Items inspizieren (read-only)
foreach ($billomat->reminders->items->list($reminder->id) as $item) {
    printf("- %s: %.2f\n", $item->title ?? 'Position', $item->total ?? 0.0);
}
```
