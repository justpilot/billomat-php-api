# Delivery Notes (Lieferscheine)

API-Wrapper für Lieferscheine unter `/delivery-notes` und ihre drei Sub-Ressourcen (`/delivery-note-items`, `/delivery-note-comments`, `/delivery-note-tags`).

## Zugriff

```php
$billomat->deliveryNotes          // Lieferscheine selbst
$billomat->deliveryNoteItems      // Positionen
$billomat->deliveryNoteComments   // Kommentare / Audit-Trail
$billomat->deliveryNoteTags       // Schlagworte
```

## Modell

Ein Lieferschein dokumentiert die physische Übergabe einer Lieferung. Er ist im Vergleich zur [Rechnung](invoices.md) wert-arm — Beträge erscheinen im Read-Modell zwar (für interne Auswertungen), das Dokument selbst hat aber keine Zahlungspflicht. Der Status-Lebenszyklus ist identisch zur [Auftragsbestätigung](confirmations.md):

```
DRAFT  ──complete()──▶  OPEN  ──clear()───▶  CLEARED
                         │
                         └──cancel()──▶  CANCELED
```

`undo()` setzt `CLEARED`/`CANCELED` zurück auf `OPEN`. Der Lieferschein kann an eine Quell-Rechnung (`invoiceId`) und/oder Auftragsbestätigung (`confirmationId`) gekoppelt werden.

## Endpunkt-Übersicht

### `/delivery-notes`

| Methode | HTTP | Pfad |
|---|---|---|
| `list($filters?)` | GET | `/delivery-notes` |
| `get($id)` | GET | `/delivery-notes/{id}` |
| `create($options)` | POST | `/delivery-notes` |
| `update($id, $options)` | PUT | `/delivery-notes/{id}` |
| `delete($id)` | DELETE | `/delivery-notes/{id}` |
| `complete($id, $templateId?)` | PUT | `/delivery-notes/{id}/complete` |
| `cancel($id)` | PUT | `/delivery-notes/{id}/cancel` |
| `clear($id)` | PUT | `/delivery-notes/{id}/clear` |
| `undo($id)` | PUT | `/delivery-notes/{id}/undo` |
| `email($id, $options?)` | POST | `/delivery-notes/{id}/email` |
| `uploadSignature($id, $base64Pdf)` | PUT | `/delivery-notes/{id}/upload-signature` |
| `pdf($id, $type?, $rawPdf?)` | GET | `/delivery-notes/{id}/pdf` |

### `/delivery-note-items`

| Methode | HTTP | Pfad |
|---|---|---|
| `listByDeliveryNote($deliveryNoteId, $query?)` | GET | `/delivery-note-items?delivery_note_id={id}` |
| `get($id)` | GET | `/delivery-note-items/{id}` |
| `create($deliveryNoteId, $options)` | POST | `/delivery-note-items` |
| `update($id, $options)` | PUT | `/delivery-note-items/{id}` |
| `delete($id)` | DELETE | `/delivery-note-items/{id}` |

### `/delivery-note-comments`

| Methode | HTTP | Pfad |
|---|---|---|
| `listByDeliveryNote($deliveryNoteId, $actionKeys?)` | GET | `/delivery-note-comments?delivery_note_id={id}` |
| `get($id)` | GET | `/delivery-note-comments/{id}` |
| `create($options)` | POST | `/delivery-note-comments` |
| `delete($id)` | DELETE | `/delivery-note-comments/{id}` |

### `/delivery-note-tags`

| Methode | HTTP | Pfad |
|---|---|---|
| `listByDeliveryNote($deliveryNoteId)` | GET | `/delivery-note-tags?delivery_note_id={id}` |
| `cloud()` | GET | `/delivery-note-tags` |
| `get($id)` | GET | `/delivery-note-tags/{id}` |
| `create($options)` | POST | `/delivery-note-tags` |
| `delete($id)` | DELETE | `/delivery-note-tags/{id}` |

## Methoden

### Delivery Notes

#### `list(array $filters = []): list<DeliveryNote>`

Filter laut Billomat-Doku: `client_id`, `contact_id`, `status`, `from`, `to`, `number_pre`, `tags`, `invoice_id`, `confirmation_id`, `order_by`. `array_is_list()` normalisiert Single-Treffer.

```php
$openDeliveries = $billomat->deliveryNotes->list([
    'status' => 'OPEN',
    'order_by' => 'date+DESC',
]);
```

#### `get(int $id): ?DeliveryNote`

Liefert `null` bei 404. Eingebettete `delivery-note-items` und `taxes` werden automatisch hydriert.

#### `create(DeliveryNoteCreateOptions $options): DeliveryNote`

Positionen können direkt im `create()`-Call mit `addItem()` mitgegeben werden — sie landen als `delivery-note-items.delivery-note-item` im Payload. Über `invoiceId` und/oder `confirmationId` lässt sich der Lieferschein an Vorgänger-Dokumente anhängen.

```php
use Justpilot\Billomat\Api\DeliveryNoteCreateOptions;
use Justpilot\Billomat\Api\DeliveryNoteItemCreateOptions;

$opts = new DeliveryNoteCreateOptions(clientId: 12345);
$opts->invoiceId = 999;
$opts->date = new DateTimeImmutable('today');
$opts->title = 'Lieferschein';

$opts->addItem(new DeliveryNoteItemCreateOptions(quantity: 3.0, unitPrice: 49.90));

$note = $billomat->deliveryNotes->create($opts);
```

#### `update(int $id, DeliveryNoteUpdateOptions $options): DeliveryNote`

Schmaler Subset, voll editierbar nur im `DRAFT`. Positionen sind hier nicht änderbar — dafür die `DeliveryNoteItemsApi` nutzen.

#### `complete(int $id, ?int $templateId = null): bool`

DRAFT → OPEN. Vergibt die endgültige `delivery_note_number` und erzeugt das PDF.

#### `delete(int $id): bool`

Nur im `DRAFT` erlaubt — danach `cancel()`.

#### Status-Übergänge

```php
$billomat->deliveryNotes->cancel($note->id);   // OPEN/CLEARED → CANCELED
$billomat->deliveryNotes->clear($note->id);    // OPEN          → CLEARED
$billomat->deliveryNotes->undo($note->id);     // {clear,cancel} → OPEN
```

Alle geben `true` bei HTTP 200 zurück. Ungültige Übergänge → `ValidationException` (422).

#### `email(int $id, ?DeliveryNoteEmailOptions $options = null): bool`

Versendet den Lieferschein per E-Mail. Ohne Options nutzt Billomat die Defaults aus dem Account; das PDF wird automatisch angehängt.

#### `uploadSignature(int $id, string $base64Pdf): bool`

Lädt eine unterschriebene PDF-Version hoch — typischer Use Case: der Fahrer fotografiert die unterschriebene Empfangsbestätigung, das PDF wird Base64-encodiert hochgeladen.

#### `pdf(int $id, ?InvoicePdfType $type = null, bool $rawPdf = false): DeliveryNotePdf|string`

Liefert `DeliveryNotePdf` (Base64-Container) oder mit `$rawPdf = true` die rohen Bytes.

```php
use Justpilot\Billomat\Model\Enum\InvoicePdfType;

$pdf = $billomat->deliveryNotes->pdf($note->id, InvoicePdfType::SIGNED);
file_put_contents('lieferschein.pdf', $pdf->getBinary());
```

### Delivery Note Items

```php
$items = $billomat->deliveryNoteItems->listByDeliveryNote($note->id);

$item = $billomat->deliveryNoteItems->create(
    $note->id,
    new DeliveryNoteItemCreateOptions(quantity: 2.0, unitPrice: 49.90),
);

$billomat->deliveryNoteItems->update($item->id, $opts);
$billomat->deliveryNoteItems->delete($item->id);
```

`create()` injiziert `delivery_note_id` automatisch — die Options-Klasse selbst hat dieses Feld nicht. `update()` verwendet dieselbe Klasse wie `create()`.

### Delivery Note Comments

```php
use Justpilot\Billomat\Api\DeliveryNoteCommentCreateOptions;
use Justpilot\Billomat\Model\Enum\DeliveryNoteCommentActionKey;

$comments = $billomat->deliveryNoteComments->listByDeliveryNote(
    $note->id,
    actionKeys: [DeliveryNoteCommentActionKey::COMPLETE, DeliveryNoteCommentActionKey::EMAIL],
);

$billomat->deliveryNoteComments->create(
    new DeliveryNoteCommentCreateOptions(
        deliveryNoteId: $note->id,
        comment: 'Übergeben an Lager Süd.',
    ),
);
```

### Delivery Note Tags

```php
use Justpilot\Billomat\Api\DeliveryNoteTagCreateOptions;

$tag = $billomat->deliveryNoteTags->create(
    new DeliveryNoteTagCreateOptions(deliveryNoteId: $note->id, name: 'speditionsversand'),
);

$tags  = $billomat->deliveryNoteTags->listByDeliveryNote($note->id);
$cloud = $billomat->deliveryNoteTags->cloud();
```

## Write-Modell: `DeliveryNoteCreateOptions`

Konstruktor: `new DeliveryNoteCreateOptions(int $clientId)` — `clientId` ist Pflicht.

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `clientId` | `client_id` | `int` | Pflicht (Konstruktor) |
| `contactId` | `contact_id` | `?int` | |
| `address` | `address` | `?string` | |
| `numberPre`, `number`, `numberLength` | analog | `?string`/`?int`/`?int` | |
| `date` | `date` | `?\DateTimeImmutable` | als `Y-m-d` serialisiert |
| `title`, `label`, `intro`, `note`, `reduction` | analog | `?string` | |
| `currencyCode` | `currency_code` | `?string` | |
| `netGross` | `net_gross` | `?NetGross` | |
| `quote` | `quote` | `?float` | |
| `invoiceId` | `invoice_id` | `?int` | Quell-Rechnung |
| `confirmationId` | `confirmation_id` | `?int` | Quell-Auftragsbestätigung |
| `freeTextId` | `free_text_id` | `?int` | |
| `templateId` | `template_id` | `?int` | |

Anders als bei `OfferCreateOptions`/`ConfirmationCreateOptions` gibt es hier **keine** `discountRate`/`discountDays`/`discountDate`/`validityDays` — der Lieferschein hat keinen eigenen Zahlungs- oder Gültigkeitskontext.

### Positionen

`addItem(DeliveryNoteItemCreateOptions $item): self` und `getItems(): list<DeliveryNoteItemCreateOptions>`. `toArray()` rendert sie als `delivery-note-items.delivery-note-item`.

## Write-Modell: `DeliveryNoteUpdateOptions`

Spiegelt `DeliveryNoteCreateOptions` ohne `clientId`-Pflicht (jetzt optional) und ohne Items-Helper. Felder, die nicht gesetzt werden, bleiben unberührt.

| Property | Billomat-Feld | Typ |
|---|---|---|
| `clientId` | `client_id` | `?int` |
| `contactId`, `numberPre`, `number`, `numberLength`, `invoiceId`, `confirmationId`, `freeTextId`, `templateId` | analog | `?int`/`?string` |
| `address`, `title`, `label`, `intro`, `note`, `reduction`, `currencyCode` | analog | `?string` |
| `date` | `date` | `?\DateTimeImmutable` |
| `netGross` | `net_gross` | `?NetGross` |
| `quote` | `quote` | `?float` |

## Write-Modell: `DeliveryNoteItemCreateOptions`

Konstruktor: `new DeliveryNoteItemCreateOptions(float $quantity, float $unitPrice)`. Beide Pflicht, werden auch bei `0` mitgeschickt.

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `quantity` | `quantity` | `float` | Pflicht (Konstruktor) |
| `unitPrice` | `unit_price` | `float` | Pflicht (Konstruktor) |
| `type` | `type` | `?InvoiceItemType` | |
| `articleId` | `article_id` | `?int` | |
| `title`, `description`, `unit` | analog | `?string` | |
| `taxName`, `taxRate`, `taxChangedManually` | analog | `?string`/`?float`/`?bool` | |
| `reduction` | `reduction` | `?string` | |
| `position` | `position` | `?int` | |

`DeliveryNoteItemsApi::update()` verwendet dieselbe Klasse — es gibt keinen separaten Update-Typ.

## Write-Modell: `DeliveryNoteCommentCreateOptions`

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `deliveryNoteId` | `delivery_note_id` | `int` | Pflicht (Konstruktor) |
| `comment` | `comment` | `string` | Pflicht (Konstruktor) |
| `actionkey` | `actionkey` | `?DeliveryNoteCommentActionKey` | typischerweise nur bei System-Aktionen |

## Write-Modell: `DeliveryNoteTagCreateOptions`

| Property | Billomat-Feld | Typ |
|---|---|---|
| `deliveryNoteId` | `delivery_note_id` | `int` (Pflicht) |
| `name` | `name` | `string` (Pflicht) |

## Write-Modell: `DeliveryNoteEmailOptions`

Strukturell identisch zu `OfferEmailOptions`/`ConfirmationEmailOptions`. Keine Pflichtfelder.

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `emailTemplateId` | `email_template_id` | `?int` | |
| `from` | `from` | `?string` | |
| `to`, `cc`, `bcc` | `recipients.{to,cc,bcc}` | `list<string>` | leere Arrays werden weggelassen |
| `subject`, `body`, `filename` | analog | `?string` | |
| `attachments` | `attachments.attachment[]` | `list<array{filename,mimetype,base64file}>` | |

## Read-Modell: `DeliveryNote`

`final readonly class DeliveryNote`.

| Property | Typ |
|---|---|
| `id` | `?int` |
| `clientId` | `int` |
| `contactId` | `?int` |
| `created` | `?\DateTimeImmutable` |
| `deliveryNoteNumber` | `?string` — erst nach `complete()` |
| `number`, `numberPre`, `numberLength` | `?int`/`?string`/`?int` |
| `status` | `?DeliveryNoteStatus` |
| `date` | `?\DateTimeImmutable` |
| `address` | `?string` |
| `title`, `label`, `intro`, `note`, `reduction` | `?string` |
| `totalGross`, `totalNet`, `totalGrossUnreduced`, `totalNetUnreduced` | `?float` |
| `netGross` | `?NetGross` |
| `currencyCode` | `?string` |
| `quote` | `?float` |
| `invoiceId` | `?int` |
| `confirmationId` | `?int` |
| `freeTextId`, `templateId` | `?int` |
| `taxes` | `list<array{name:string,rate:float,amount:float}>` |
| `customerportalUrl` | `?string` |
| `items` | `list<DeliveryNoteItem>` |

## Read-Modell: `DeliveryNoteItem`

`final readonly`.

| Property | Typ |
|---|---|
| `id`, `deliveryNoteId`, `articleId`, `position` | `?int` |
| `unit` | `?string` |
| `quantity`, `unitPrice` | `float` |
| `taxName` | `?string` |
| `taxRate` | `?float` |
| `taxChangedManually` | `?bool` |
| `title`, `description`, `reduction` | `?string` |
| `type` | `?InvoiceItemType` |
| `totalGross`, `totalNet`, `totalGrossUnreduced`, `totalNetUnreduced` | `?float` |
| `created` | `?\DateTimeImmutable` |

## Read-Modell: `DeliveryNoteComment`

`final readonly`.

| Property | Typ |
|---|---|
| `id` | `?int` |
| `deliveryNoteId` | `int` |
| `comment` | `?string` |
| `created` | `?\DateTimeImmutable` |
| `userId` | `?int` |
| `actionkey` | `?DeliveryNoteCommentActionKey` |
| `actionkeyRaw` | `?string` — Roh-Wert bei unbekanntem `actionkey` |

## Read-Modell: `DeliveryNoteTag`

`final readonly`. Felder: `id` (`?int`), `deliveryNoteId` (`int`), `name` (`string`).

## Read-Modell: `DeliveryNoteTagCloudEntry`

`final readonly`. Aggregat: `id` (`?int`), `name` (`string`), `count` (`int`).

## Read-Modell: `DeliveryNotePdf`

`final class DeliveryNotePdf` (nicht `readonly`).

| Property | Typ |
|---|---|
| `id` | `int` |
| `deliveryNoteId` | `int` |
| `created` | `?\DateTimeImmutable` |
| `filename` | `string` |
| `mimeType` | `string` — Default `application/pdf` |
| `fileSize` | `int` |
| `base64file` | `string` |

`getBinary(): string` decodiert `base64file`.

## Verwendete Enums

- [`DeliveryNoteStatus`](../../src/Model/Enum/DeliveryNoteStatus.php): `DRAFT`, `OPEN`, `CLEARED`, `CANCELED`. Hat `label()` für deutsche Bezeichnung.
- [`DeliveryNoteCommentActionKey`](../../src/Model/Enum/DeliveryNoteCommentActionKey.php): `CREATE`, `EDIT`, `OPEN`, `COMPLETE`, `CANCEL`, `CLEAR`, `CHANGE_STATUS`, `EMAIL`, `MAIL`, `COMMENT`.
- [`InvoiceItemType`](../../src/Model/Enum/InvoiceItemType.php): `PRODUCT`, `SERVICE` — von Rechnungspositionen geerbt.
- [`InvoicePdfType`](../../src/Model/Enum/InvoicePdfType.php): `SIGNED` (`signed`), `PRINT` (`print`) — Wire-Werte kleingeschrieben.
- [`NetGross`](../../src/Model/Enum/NetGross.php): `NET`, `GROSS`, `SETTINGS`.

## Stolpersteine

- **Lieferschein ist wert-arm.** Beträge sind im Read-Modell enthalten (vom Backend errechnet), aber das Dokument löst keine Forderung aus. Wer eine Zahlung erwartet, ist hier falsch — `InvoicesApi` oder `IncomingsApi` ist gemeint.
- **Kein `discount`/`validity`.** Anders als `OfferCreateOptions`/`ConfirmationCreateOptions` hat `DeliveryNoteCreateOptions` keine `discountRate`/`discountDays`/`discountDate`/`validityDays`-Felder. Wer aus Copy-Paste-Reflex sucht, findet sie nicht.
- **`update()` ändert keine Positionen.** Billomat ignoriert eingebettete `delivery-note-items` beim PUT. Stets die `DeliveryNoteItemsApi` verwenden.
- **`delete()` nur im `DRAFT`.** Nach `complete()` ist nur noch `cancel()` möglich.
- **`DeliveryNoteItemCreateOptions` ist auch der Update-Typ.** Der Konstruktor verlangt `quantity` und `unitPrice`, selbst wenn du nur den Titel ändern willst.
- **`array_is_list`-Normalisierung in `list()`.** Bei genau einem Treffer liefert Billomat `delivery-note` als Objekt — das SDK packt das selbst in eine Liste.
- **Doppelte Quell-Referenz möglich.** Sowohl `invoiceId` als auch `confirmationId` können gleichzeitig gesetzt sein — Billomat synchronisiert die Status der verlinkten Dokumente aber **nicht** automatisch.
- **`tax_changed_manually` ist Pflicht für manuelle `taxRate`-Werte.** Ohne das Flag nimmt Billomat den Default des Artikels/Kunden.
- **`DeliveryNotePdf` ist nicht `readonly`** — pragmatische Ausnahme für den Binärdaten-Container.
- **`uploadSignature()` erwartet reines Base64**, kein `data:`-Prefix. Typischer Anwendungsfall: vom Fahrer signiertes PDF aus dem Mobil-Workflow zurückspielen.

## End-to-End-Beispiel

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\DeliveryNoteCommentCreateOptions;
use Justpilot\Billomat\Api\DeliveryNoteCreateOptions;
use Justpilot\Billomat\Api\DeliveryNoteEmailOptions;
use Justpilot\Billomat\Api\DeliveryNoteItemCreateOptions;
use Justpilot\Billomat\Api\DeliveryNoteTagCreateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\Enum\InvoiceItemType;
use Justpilot\Billomat\Model\Enum\InvoicePdfType;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

// 1) Lieferschein zu einer bestehenden Rechnung anlegen
$opts = new DeliveryNoteCreateOptions(clientId: 12345);
$opts->invoiceId = 999;
$opts->date = new DateTimeImmutable('today');
$opts->title = 'Lieferschein';
$opts->intro = 'Bitte bei Empfang gegenzeichnen.';

$item = new DeliveryNoteItemCreateOptions(quantity: 3.0, unitPrice: 49.90);
$item->title = 'Hardware-Paket';
$item->type = InvoiceItemType::PRODUCT;
$item->unit = 'Stück';
$opts->addItem($item);

$note = $billomat->deliveryNotes->create($opts);
printf("Lieferschein #%d angelegt (DRAFT)\n", $note->id);

// 2) Tag setzen
$billomat->deliveryNoteTags->create(
    new DeliveryNoteTagCreateOptions(deliveryNoteId: $note->id, name: 'speditionsversand'),
);

// 3) Abschließen → OPEN, Nummer wird vergeben
$billomat->deliveryNotes->complete($note->id);

$opened = $billomat->deliveryNotes->get($note->id);
printf("Status: %s, Lieferscheinnummer: %s\n",
    $opened?->status?->label() ?? '?',
    $opened?->deliveryNoteNumber ?? '?',
);

// 4) PDF an Spedition mailen
$email = new DeliveryNoteEmailOptions();
$email->to = ['spedition@example.com'];
$email->subject = 'Lieferschein {NUMBER}';
$billomat->deliveryNotes->email($note->id, $email);

// 5) Unterschriebene Empfangsbestätigung hochladen (z. B. vom Mobil-Workflow)
$signed = file_get_contents('/var/spool/scans/' . $opened?->deliveryNoteNumber . '.pdf');
if (false !== $signed) {
    $billomat->deliveryNotes->uploadSignature($note->id, base64_encode($signed));
}

// 6) Vermerk und als erledigt markieren
$billomat->deliveryNoteComments->create(
    new DeliveryNoteCommentCreateOptions(
        deliveryNoteId: $note->id,
        comment: 'Empfang quittiert durch Lager Süd am 02.06.',
    ),
);
$billomat->deliveryNotes->clear($note->id);

// 7) Signierte PDF-Version archivieren
$pdf = $billomat->deliveryNotes->pdf($note->id, InvoicePdfType::SIGNED);
file_put_contents("ls-{$opened?->deliveryNoteNumber}.pdf", $pdf->getBinary());
```
