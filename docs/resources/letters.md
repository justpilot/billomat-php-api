<!-- Quelle: https://www.billomat.com/api/briefe/ -->

# Letters (Briefe)

API-Wrapper für freie Korrespondenz unter `/letters` und ihre zwei Sub-Ressourcen (`/letter-comments`, `/letter-tags`).

## Zugriff

```php
$billomat->letters         // Briefe selbst
$billomat->letters->comments  // Aktivitäts-Log / freie Kommentare
$billomat->letters->tags      // Schlagworte
```

## Modell

Ein Brief (Letter) ist freie, formgebundene Korrespondenz an einen Kunden — ohne Positionsliste, ohne Steuerberechnung, ohne Zahlungs-Lifecycle. Hauptzweck ist das Versenden per E-Mail oder postalisch (Pixelletter), das Hochladen eines extern erzeugten PDFs (`upload()`) und die Ablage im Kunden-Vorgangs-Verlauf.

Der Lifecycle ist statusgetrieben, aber schlanker als bei Invoices/CreditNotes:

```
DRAFT  ── complete() ──▶  OPEN  ── clear() ──▶  CLEARED   (erledigt)
  │                        │
  │                        ├── cancel() ──▶  CANCELED
  │                        │
  │                        └── undo() ──▶  DRAFT          (Rücksetzen)
  └── delete() (nur im DRAFT)
```

`undo()` ist die Letter-spezifische Möglichkeit, einen offenen Brief zurück in den Entwurfsstatus zu bringen — eine `uncancel()`-Variante gibt es bewusst nicht.

## Endpunkt-Übersicht

### `/letters`

| Methode | HTTP | Pfad |
|---|---|---|
| `list($filters?)` | GET | `/letters` |
| `get($id)` | GET | `/letters/{id}` |
| `create($options)` | POST | `/letters` |
| `update($id, $options)` | PUT | `/letters/{id}` |
| `delete($id)` | DELETE | `/letters/{id}` |
| `complete($id, $templateId?)` | PUT | `/letters/{id}/complete` |
| `cancel($id)` | PUT | `/letters/{id}/cancel` |
| `clear($id)` | PUT | `/letters/{id}/clear` |
| `undo($id)` | PUT | `/letters/{id}/undo` |
| `email($id, $options?)` | POST | `/letters/{id}/email` |
| `pdf($id, $type?, $rawPdf?)` | GET | `/letters/{id}/pdf` |
| `upload($id, $base64Pdf)` | PUT | `/letters/{id}/upload` |
| `uploadSignature($id, $base64Pdf)` | PUT | `/letters/{id}/upload-signature` |

### `/letter-comments`

| Methode | HTTP | Pfad |
|---|---|---|
| `list($letterId, $actionKeys?)` | GET | `/letter-comments?letter_id={id}` |
| `get($id)` | GET | `/letter-comments/{id}` |
| `create($options)` | POST | `/letter-comments` |
| `delete($id)` | DELETE | `/letter-comments/{id}` |

### `/letter-tags`

| Methode | HTTP | Pfad |
|---|---|---|
| `list($letterId)` | GET | `/letter-tags?letter_id={id}` |
| `cloud()` | GET | `/letter-tags` |
| `get($id)` | GET | `/letter-tags/{id}` |
| `create($options)` | POST | `/letter-tags` |
| `delete($id)` | DELETE | `/letter-tags/{id}` |

## Methoden

### Letters

#### `list(LetterFilter|array $filters = []): list<Letter>`

Billomat filtert u. a. nach `client_id`, `contact_id`, `supplier_id`, `status`, `from`, `to`, `tags`, …

Empfohlen ist der typisierte Filter `LetterFilter` mit den camelCase-Feldern `clientId`, `contactId`, `supplierId`, `status` (`list<LetterStatus>`), `from`/`to` (`DateTimeImmutable`), `label`, `intro` und `tags`; Mehrfachwerte sind Listen und werden kommasepariert gesendet, dazu kommen `orderBy`, `page`, `perPage` und `extra` für weitere Query-Parameter. Ein einfaches Array funktioniert weiterhin. Arrays werden 1:1 als Query-String durchgereicht, Array-Werte landen als `key[]=…`.

```php
use Justpilot\Billomat\Api\LetterFilter;
use Justpilot\Billomat\Api\OrderBy;
use Justpilot\Billomat\Model\Enum\LetterStatus;

$drafts = $billomat->letters->list(new LetterFilter(
    status: [LetterStatus::DRAFT],
    orderBy: OrderBy::desc('created'),
));
```

#### `get(int $id): ?Letter`

Liefert `null` bei 404.

#### `create(LetterCreateOptions $options): Letter`

Pflicht ist ausschließlich die `clientId`. Der eigentliche Brieftext (`intro` + `note`) und das Layout-Template (`templateId`) sind frei wählbar.

```php
use Justpilot\Billomat\Api\LetterCreateOptions;

$opts = new LetterCreateOptions(
    clientId: 12345,
    subject: 'Information zur DSGVO-Aktualisierung',
    date: new DateTimeImmutable('2026-06-02'),
    intro: 'Sehr geehrte Damen und Herren,',
    note: "Hiermit informieren wir Sie über …\n\nMit freundlichen Grüßen",
);

$letter = $billomat->letters->create($opts);
```

#### `update(int $id, LetterUpdateOptions $options): Letter`

Nur im Status `DRAFT` ratsam. Felder, die `null` bleiben, werden serverseitig nicht angefasst.

#### `complete(int $id, ?int $templateId = null): void`

Status `DRAFT → COMPLETED`. Optional kann ein konkretes Layout-Template forciert werden.

#### `cancel(int $id): void` / `clear(int $id): void` / `undo(int $id): void`

- `cancel()` → Status `CANCELED` (terminal aus Workflow-Sicht).
- `clear()` → Status `CLEARED` („erledigt“, etwa wenn der Brief bearbeitet/abgehakt ist).
- `undo()` → setzt einen `OPEN`-Brief zurück nach `DRAFT`, sodass er wieder editierbar wird.

#### `email(int $id, ?EmailOptions $options = null): void`

Versendet den Brief per E-Mail. Ohne Optionen greifen die Defaults aus der hinterlegten Vorlage.

```php
use Justpilot\Billomat\Api\EmailOptions;

$mail = new EmailOptions(
    to: ['kunde@example.com'],
    subject: 'Unser Anschreiben',
    body: 'Anbei unser Schreiben als PDF.',
);

$billomat->letters->email($letter->id, $mail);
```

#### `pdf(int $id, ?InvoicePdfType $type = null, bool $rawPdf = false): LetterPdf|string`

Per Default ein `LetterPdf`-Modell. Mit `$rawPdf = true` wird `?format=pdf` gehängt und das binäre PDF zurückgegeben.

```php
$pdf = $billomat->letters->pdf($letter->id);                 // Model
$raw = $billomat->letters->pdf($letter->id, rawPdf: true);   // string (PDF binary)
```

#### `upload(int $id, string $base64Pdf): void`

**Letter-spezifisch**: lädt ein komplett extern erzeugtes Brief-PDF in den Slot des Briefs. Praktisch, wenn das Layout nicht über Billomat-Templates läuft (z. B. fertige Word/InDesign-Exporte). Diese Methode hat **keine** Entsprechung bei Invoices/CreditNotes/Reminders.

#### `uploadSignature(int $id, string $base64Pdf): void`

Hängt eine vom Empfänger unterschriebene Variante an.

#### `delete(int $id): void`

Funktioniert ausschließlich an Briefen im Status `DRAFT`.

### Letter Comments

```php
use Justpilot\Billomat\Api\LetterCommentCreateOptions;
use Justpilot\Billomat\Model\Enum\LetterCommentActionKey;

$comment = $billomat->letters->comments->create(
    new LetterCommentCreateOptions(
        letterId: $letter->id,
        comment: 'Brief postalisch versendet am 03.06.2026.',
    ),
);

// Nur EMAIL- und UPLOAD-Einträge anzeigen
$entries = $billomat->letters->comments->list(
    $letter->id,
    [LetterCommentActionKey::EMAIL, LetterCommentActionKey::UPLOAD],
);
```

Wie bei Credit Notes mischt der Endpunkt freie User-Kommentare mit System-Audit-Einträgen. ActionKeys werden als CSV-String übergeben.

### Letter Tags

```php
use Justpilot\Billomat\Api\LetterTagCreateOptions;

$billomat->letters->tags->create(
    new LetterTagCreateOptions(letterId: $letter->id, name: 'dsgvo'),
);

$tags = $billomat->letters->tags->list($letter->id);
$cloud = $billomat->letters->tags->cloud(); // aggregiert, mit count
```

Kein Update — zum Umbenennen wird gelöscht und neu angelegt.

## Write-Modell: `LetterCreateOptions`

Konstruktor: `new LetterCreateOptions(int $clientId, …)` — `clientId` ist Pflicht und erster Parameter. Alle Felder sind Konstruktor-Parameter mit Default und werden als benannte Argumente übergeben. Das Objekt ist unveränderlich (`final readonly`); jedes Feld hat eine `with*()`-Methode, die eine geänderte Kopie liefert (z. B. `$opts->withLabel('Neu')`).

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `clientId` | `client_id` | `int` (Pflicht) | |
| `contactId` | `contact_id` | `?int` | |
| `address` | `address` | `?string` | mehrzeilige Anschrift, überschreibt die Kunden-Adresse |
| `numberPre` | `number_pre` | `?string` | |
| `number` | `number` | `?int` | manuelle Nummerierung |
| `numberLength` | `number_length` | `?int` | |
| `date` | `date` | `?\DateTimeImmutable` | `Y-m-d` |
| `subject` | `subject` | `?string` | Betreffzeile |
| `label` | `label` | `?string` | |
| `intro` | `intro` | `?string` | Brief-Anrede / Einleitung |
| `note` | `note` | `?string` | Brief-Haupttext |
| `freeTextId` | `free_text_id` | `?int` | |
| `templateId` | `template_id` | `?int` | Layout-Template |

## Write-Modell: `LetterUpdateOptions`

Spiegelt `LetterCreateOptions` ohne `clientId`-Pflicht (jetzt nullable). Felder, die `null` bleiben, werden aus dem Payload gestrippt. Mit `Clear::Field` wird ein Feld dagegen als JSON-`null` gesendet und von Billomat geleert, z. B. `new LetterUpdateOptions(note: Clear::Field)`.

## Write-Modell: `LetterCommentCreateOptions`

Konstruktor: `new LetterCommentCreateOptions(int $letterId, string $comment)`.

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `letterId` | `letter_id` | `int` (Pflicht) | |
| `comment` | `comment` | `string` (Pflicht) | |
| `actionkey` | `actionkey` | `?LetterCommentActionKey` | wird in der Regel von Billomat gesetzt |

## Write-Modell: `LetterTagCreateOptions`

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `letterId` | `letter_id` | `int` (Pflicht) | |
| `name` | `name` | `string` (Pflicht) | |

## Write-Modell: `EmailOptions`

Gemeinsame Klasse für den E-Mail-Versand aller Dokumenttypen (Rechnungen, Angebote, Auftragsbestätigungen, Gutschriften, Lieferscheine, Mahnungen, Briefe); die vollständige Beschreibung steht bei [Invoices](invoices.md#write-modell-emailoptions).

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `emailTemplateId` | `email_template_id` | `?int` | |
| `from` | `from` | `?string` | |
| `to` | `recipients.to` | `list<string>` | |
| `cc` | `recipients.cc` | `list<string>` | |
| `bcc` | `recipients.bcc` | `list<string>` | |
| `subject` | `subject` | `?string` | |
| `body` | `body` | `?string` | E-Mail-Text (nicht der Brief-Inhalt) |
| `filename` | `filename` | `?string` | Name des PDF-Anhangs |
| `attachments` | `attachments.attachment` | `list<array{filename,mimetype,base64file}>` | zusätzliche Anhänge |

Leere Empfänger-Arrays werden weggelassen, damit Billomat auf die Vorlage zurückfällt.

## Read-Modell: `Letter`

`final readonly class Letter`. Deutlich schmaler als `Invoice` oder `CreditNote` — keine Total-Felder, keine Items.

| Property | Typ |
|---|---|
| `id` | `?int` |
| `clientId` | `int` |
| `contactId` | `?int` |
| `created` | `?\DateTimeImmutable` |
| `status` | `?LetterStatus` |
| `date` | `?\DateTimeImmutable` |
| `address`, `subject`, `label`, `intro` | `?string` |
| `templateId` | `?int` |
| `updated` | `?\DateTimeImmutable` |
| `supplierId` | `?int` |
| `customfield` | `?string` (Freies Zusatzfeld, siehe [Custom-Meta-Attribute](../concepts/custom-meta-attributes.md)) |

## Read-Modell: `LetterComment`

| Property | Typ |
|---|---|
| `id` | `?int` |
| `letterId` | `int` |
| `comment` | `?string` |
| `created` | `?\DateTimeImmutable` |
| `userId` | `?int` |
| `actionkey` | `?LetterCommentActionKey` |
| `actionkeyRaw` | `?string` (Roh-Wert für unbekannte Keys) |
| `public` | `?bool` |
| `byClient` | `?bool` |
| `emailId` | `?int` |
| `clientId` | `?int` |
| `customfield` | `?string` (Freies Zusatzfeld, siehe [Custom-Meta-Attribute](../concepts/custom-meta-attributes.md)) |

## Read-Modell: `LetterTag`

| Property | Typ |
|---|---|
| `id` | `?int` |
| `letterId` | `int` |
| `name` | `string` |
| `customfield` | `?string` (Freies Zusatzfeld, siehe [Custom-Meta-Attribute](../concepts/custom-meta-attributes.md)) |

## Read-Modell: `LetterTagCloudEntry`

| Property | Typ |
|---|---|
| `id` | `?int` |
| `name` | `string` |
| `count` | `int` |

## Read-Modell: `LetterPdf`

`final class LetterPdf`.

| Property | Typ |
|---|---|
| `id`, `letterId` | `int` |
| `created` | `?\DateTimeImmutable` |
| `filename`, `mimeType` | `string` |
| `fileSize` | `int` |
| `base64file` | `string` |

`getBinary(): string` dekodiert `base64file` zurück in Roh-Bytes.

## Verwendete Enums

- [`LetterStatus`](../../src/Model/Enum/LetterStatus.php): `DRAFT`, `OPEN`, `COMPLETED`, `CLEARED`, `CANCELED`. Nach `complete()` liefert Billomat inzwischen `COMPLETED`. Beachte: kein `PAID`, kein `OVERDUE` — Briefe haben keinen Zahlungs-Lifecycle.
- [`LetterCommentActionKey`](../../src/Model/Enum/LetterCommentActionKey.php): `CREATE`, `EDIT`, `OPEN`, `COMPLETE`, `CANCEL`, `CLEAR`, `CHANGE_STATUS`, `EMAIL`, `MAIL`, `COMMENT`, `UPLOAD`. Der zusätzliche `UPLOAD`-Key markiert ein über `upload()` hochgeladenes Brief-PDF.
- [`InvoicePdfType`](../../src/Model/Enum/InvoicePdfType.php): wiederverwendet für die PDF-Varianten in `pdf()`.

## Stolpersteine

- **Kein Items-Konzept.** Im Gegensatz zu Invoices, Offers, Confirmations und CreditNotes haben Briefe keine Positionsliste — wer Beträge oder Tabellen darstellen will, muss sie als formatierten Text in `note` packen oder per `upload()` ein fertiges PDF einspielen.
- **`upload()` ersetzt das Layout vollständig.** Das hochgeladene PDF wird beim Versand 1:1 genutzt — `templateId` und `subject` werden für das PDF dann ignoriert. Für die E-Mail-Begleittexte gilt das aber nicht.
- **`undo()` statt `uncancel()`.** Briefe haben eine Rückkehr-Funktion von `OPEN` zu `DRAFT` (`/letters/{id}/undo`) — aber **keinen** Weg zurück aus `CANCELED`. Ein versehentlich stornierter Brief muss neu angelegt werden.
- **`clear()` ist nicht `cancel()`.** `clear()` markiert den Brief als „erledigt“ (`CLEARED`), z. B. wenn die intern angestoßene Aktion abgeschlossen ist — `cancel()` markiert ihn als „ungültig“.
- **`update()` nur im DRAFT.** Sobald der Brief auf `OPEN` steht, ignoriert Billomat die meisten Felder. Erst `undo()` aufrufen, dann editieren, dann `complete()`.
- **`number` manuell setzen kollidiert mit Auto-Numbering.** Wenn der Account auf automatische Nummern konfiguriert ist, schlägt `complete()` mit `ValidationException` fehl.
- **`actionkey` im Comment-Filter ist CSV, kein Array.** `list($id, [...])` übergibt mehrere ActionKeys als komma-separierten String, nicht als `actionkey[]=…`.
- **Tag-Cloud vs. Liste am selben Endpunkt.** `GET /letter-tags` ohne `letter_id` liefert die aggregierte Cloud (`tag`-Wrapper); mit `letter_id` die konkreten Tags (`letter-tag`-Wrapper). Der SDK trennt das in `cloud()` und `list()`.

## End-to-End-Beispiel

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\LetterCommentCreateOptions;
use Justpilot\Billomat\Api\LetterCreateOptions;
use Justpilot\Billomat\Api\EmailOptions;
use Justpilot\Billomat\Api\LetterTagCreateOptions;
use Justpilot\Billomat\BillomatClient;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

// 1) Brief als Entwurf anlegen
$opts = new LetterCreateOptions(
    clientId: 12345,
    subject: 'Information zur DSGVO-Aktualisierung',
    date: new DateTimeImmutable('2026-06-02'),
    intro: 'Sehr geehrte Damen und Herren,',
    note: <<<TEXT
            hiermit informieren wir Sie über die Aktualisierung unserer Datenschutzerklärung.
            Die neue Fassung tritt zum 01.07.2026 in Kraft.

            Mit freundlichen Grüßen
            Ihre Justpilot GmbH
            TEXT,
);

$letter = $billomat->letters->create($opts);
printf("Brief #%d angelegt (Status: %s)\n",
    $letter->id,
    $letter->status?->label() ?? 'unbekannt',
);

// 2) Tag setzen
$billomat->letters->tags->create(
    new LetterTagCreateOptions(letterId: $letter->id, name: 'dsgvo'),
);

// 3) Abschließen — DRAFT → OPEN
$billomat->letters->complete($letter->id);

// 4) Per E-Mail versenden
$mail = new EmailOptions(
    to: ['kunde@example.com'],
    subject: 'Unsere aktualisierte Datenschutzerklärung',
    body: 'Anbei finden Sie unser Schreiben als PDF.',
);
$billomat->letters->email($letter->id, $mail);

// 5) Audit-Notiz anhängen
$billomat->letters->comments->create(
    new LetterCommentCreateOptions(
        letterId: $letter->id,
        comment: 'Versand am ' . new DateTimeImmutable()->format('d.m.Y') . ' an Hauptkontakt.',
    ),
);

// 6) PDF lokal sichern
$pdfBinary = $billomat->letters->pdf($letter->id, rawPdf: true);
file_put_contents("brief-{$letter->id}.pdf", $pdfBinary);

// 7) Wenn das Schreiben intern abgehakt ist → CLEARED
$billomat->letters->clear($letter->id);
```
