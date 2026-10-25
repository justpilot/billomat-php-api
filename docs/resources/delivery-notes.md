<!-- Quelle: https://www.billomat.com/api/lieferscheine/ -->

# Delivery Notes (Lieferscheine)

API-Wrapper für Lieferscheine unter `/delivery-notes` und ihre drei Sub-Ressourcen (`/delivery-note-items`, `/delivery-note-comments`, `/delivery-note-tags`).

## Zugriff

```php
$billomat->deliveryNotes          // Lieferscheine selbst
$billomat->deliveryNotes->items      // Positionen
$billomat->deliveryNotes->comments   // Kommentare / Audit-Trail
$billomat->deliveryNotes->tags       // Schlagworte
```

## Modell

Ein Lieferschein dokumentiert die physische Übergabe einer Lieferung. Er ist im Vergleich zur [Rechnung](invoices.md) wert-arm — das Read-Modell enthält keine Summen- oder Preisfelder, und das Dokument selbst hat keine Zahlungspflicht. Der Status-Lebenszyklus ist identisch zur [Auftragsbestätigung](confirmations.md):

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
| `list($deliveryNoteId, $query?)` | GET | `/delivery-note-items?delivery_note_id={id}` |
| `get($id)` | GET | `/delivery-note-items/{id}` |
| `create($deliveryNoteId, $options)` | POST | `/delivery-note-items` |
| `update($id, $options)` | PUT | `/delivery-note-items/{id}` |
| `delete($id)` | DELETE | `/delivery-note-items/{id}` |

### `/delivery-note-comments`

| Methode | HTTP | Pfad |
|---|---|---|
| `list($deliveryNoteId, $actionKeys?)` | GET | `/delivery-note-comments?delivery_note_id={id}` |
| `get($id)` | GET | `/delivery-note-comments/{id}` |
| `create($options)` | POST | `/delivery-note-comments` |
| `delete($id)` | DELETE | `/delivery-note-comments/{id}` |

### `/delivery-note-tags`

| Methode | HTTP | Pfad |
|---|---|---|
| `list($deliveryNoteId)` | GET | `/delivery-note-tags?delivery_note_id={id}` |
| `cloud()` | GET | `/delivery-note-tags` |
| `get($id)` | GET | `/delivery-note-tags/{id}` |
| `create($options)` | POST | `/delivery-note-tags` |
| `delete($id)` | DELETE | `/delivery-note-tags/{id}` |

## Methoden

### Delivery Notes

#### `list(DeliveryNoteFilter|array $filters = []): list<DeliveryNote>`

Filter laut Billomat-Doku: `client_id`, `contact_id`, `status`, `from`, `to`, `number_pre`, `tags`, `invoice_id`, `confirmation_id`, `order_by`. `array_is_list()` normalisiert Single-Treffer.

Empfohlen ist der typisierte Filter `DeliveryNoteFilter` mit den camelCase-Feldern `clientId`, `contactId`, `deliveryNoteNumber`, `status` (`list<DeliveryNoteStatus>`), `from`/`to` (`DateTimeImmutable`), `label`, `intro`, `note`, `tags` und `articleId`; Mehrfachwerte sind Listen und werden kommasepariert gesendet, dazu kommen `orderBy`, `page`, `perPage` und `extra` für weitere Query-Parameter (z. B. `invoice_id`, `confirmation_id`, `number_pre`). Ein einfaches Array funktioniert weiterhin.

```php
use Justpilot\Billomat\Api\DeliveryNoteFilter;
use Justpilot\Billomat\Api\OrderBy;
use Justpilot\Billomat\Model\Enum\DeliveryNoteStatus;

$openDeliveries = $billomat->deliveryNotes->list(new DeliveryNoteFilter(
    status: [DeliveryNoteStatus::OPEN],
    orderBy: OrderBy::desc('date'),
));
```

#### `get(int $id): ?DeliveryNote`

Liefert `null` bei 404. Eingebettete `delivery-note-items` und `taxes` werden automatisch hydriert.

#### `create(DeliveryNoteCreateOptions $options): DeliveryNote`

Positionen können direkt im `create()`-Call über `items: […]` oder `withItem()` mitgegeben werden — sie landen als `delivery-note-items.delivery-note-item` im Payload. Über `invoiceId` und/oder `confirmationId` lässt sich der Lieferschein an Vorgänger-Dokumente anhängen.

```php
use Justpilot\Billomat\Api\DeliveryNoteCreateOptions;
use Justpilot\Billomat\Api\DeliveryNoteItemCreateOptions;

$opts = new DeliveryNoteCreateOptions(
    clientId: 12345,
    invoiceId: 999,
    date: new DateTimeImmutable('today'),
    title: 'Lieferschein',
    items: [
        new DeliveryNoteItemCreateOptions(quantity: 3.0, unitPrice: 49.90),
    ],
);

$note = $billomat->deliveryNotes->create($opts);
```

#### `update(int $id, DeliveryNoteUpdateOptions $options): DeliveryNote`

Schmaler Subset, voll editierbar nur im `DRAFT`. Positionen sind hier nicht änderbar — dafür die `$billomat->deliveryNotes->items` nutzen.

#### `complete(int $id, ?int $templateId = null): void`

DRAFT → OPEN. Vergibt die endgültige `delivery_note_number` und erzeugt das PDF.

#### `delete(int $id): void`

Nur im `DRAFT` erlaubt — danach `cancel()`.

#### Status-Übergänge

```php
$billomat->deliveryNotes->cancel($note->id);   // OPEN/CLEARED → CANCELED
$billomat->deliveryNotes->clear($note->id);    // OPEN          → CLEARED
$billomat->deliveryNotes->undo($note->id);     // {clear,cancel} → OPEN
```

Alle geben `true` bei HTTP 200 zurück. Ungültige Übergänge → `ValidationException` (422).

#### `email(int $id, ?EmailOptions $options = null): void`

Versendet den Lieferschein per E-Mail. Ohne Options nutzt Billomat die Defaults aus dem Account; das PDF wird automatisch angehängt.

#### `uploadSignature(int $id, string $base64Pdf): void`

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
$items = $billomat->deliveryNotes->items->list($note->id);

$item = $billomat->deliveryNotes->items->create(
    $note->id,
    new DeliveryNoteItemCreateOptions(quantity: 2.0, unitPrice: 49.90),
);

$billomat->deliveryNotes->items->update($item->id, $opts);
$billomat->deliveryNotes->items->delete($item->id);
```

`create()` injiziert `delivery_note_id` automatisch — die Options-Klasse selbst hat dieses Feld nicht. `update()` verwendet dieselbe Klasse wie `create()`.

### Delivery Note Comments

```php
use Justpilot\Billomat\Api\DeliveryNoteCommentCreateOptions;
use Justpilot\Billomat\Model\Enum\DeliveryNoteCommentActionKey;

$comments = $billomat->deliveryNotes->comments->list(
    $note->id,
    actionKeys: [DeliveryNoteCommentActionKey::COMPLETE, DeliveryNoteCommentActionKey::EMAIL],
);

$billomat->deliveryNotes->comments->create(
    new DeliveryNoteCommentCreateOptions(
        deliveryNoteId: $note->id,
        comment: 'Übergeben an Lager Süd.',
    ),
);
```

### Delivery Note Tags

```php
use Justpilot\Billomat\Api\DeliveryNoteTagCreateOptions;

$tag = $billomat->deliveryNotes->tags->create(
    new DeliveryNoteTagCreateOptions(deliveryNoteId: $note->id, name: 'speditionsversand'),
);

$tags  = $billomat->deliveryNotes->tags->list($note->id);
$cloud = $billomat->deliveryNotes->tags->cloud();
```

## Write-Modell: `DeliveryNoteCreateOptions`

Konstruktor: `new DeliveryNoteCreateOptions(int $clientId, …)` — `clientId` ist Pflicht und erster Parameter. Alle Felder sind Konstruktor-Parameter mit Default und werden als benannte Argumente übergeben. Das Objekt ist unveränderlich (`final readonly`); jedes Feld hat eine `with*()`-Methode, die eine geänderte Kopie liefert (z. B. `$opts->withTitle('Neu')`).

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

Positionen werden als Liste über den Konstruktor-Parameter `items` übergeben (`items: [new DeliveryNoteItemCreateOptions(…), …]`) und stehen danach in der Property `$items`. `withItem()` liefert eine Kopie mit einer zusätzlichen Position. `toArray()` rendert sie als `delivery-note-items.delivery-note-item`.

## Write-Modell: `DeliveryNoteUpdateOptions`

Spiegelt `DeliveryNoteCreateOptions` ohne `clientId`-Pflicht (jetzt optional) und ohne `items`. `null` heißt „nicht senden“, der Wert bleibt unverändert. Mit `Clear::Field` (`Justpilot\Billomat\Api\Clear`) wird das Feld als JSON-`null` gesendet und von Billomat geleert, z. B. `new DeliveryNoteUpdateOptions(note: Clear::Field)`.

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

`ItemsApi::update()` verwendet dieselbe Klasse — es gibt keinen separaten Update-Typ.

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

## Write-Modell: `EmailOptions`

Gemeinsame Klasse für den E-Mail-Versand aller Dokumenttypen (Rechnungen, Angebote, Auftragsbestätigungen, Gutschriften, Lieferscheine, Mahnungen, Briefe); die vollständige Beschreibung steht bei [Invoices](invoices.md#write-modell-emailoptions). Keine Pflichtfelder.

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
| `title`, `label`, `intro`, `note` | `?string` |
| `invoiceId` | `?int` |
| `confirmationId` | `?int` |
| `freeTextId`, `templateId` | `?int` |
| `taxes` | `list<array{name:string,rate:float,amount:float}>` |
| `items` | `list<DeliveryNoteItem>` |
| `updated` | `?\DateTimeImmutable` |
| `offerId` | `?int` |
| `customfield` | `?string` (Freies Zusatzfeld, siehe [Custom-Meta-Attribute](../concepts/custom-meta-attributes.md)) |

## Read-Modell: `DeliveryNoteItem`

`final readonly`.

| Property | Typ |
|---|---|
| `id`, `deliveryNoteId`, `articleId`, `position` | `?int` |
| `unit` | `?string` |
| `quantity` | `float` |
| `taxChangedManually` | `?bool` |
| `title`, `description` | `?string` |
| `type` | `?InvoiceItemType` |
| `customfield` | `?string` (Freies Zusatzfeld, siehe [Custom-Meta-Attribute](../concepts/custom-meta-attributes.md)) |

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
| `public` | `?bool` |
| `byClient` | `?bool` |
| `emailId` | `?int` |
| `clientId` | `?int` |
| `customfield` | `?string` (Freies Zusatzfeld, siehe [Custom-Meta-Attribute](../concepts/custom-meta-attributes.md)) |

## Read-Modell: `DeliveryNoteTag`

`final readonly`. Felder: `id` (`?int`), `deliveryNoteId` (`int`), `name` (`string`), `customfield` (`?string`).

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

- [`DeliveryNoteStatus`](../../src/Model/Enum/DeliveryNoteStatus.php): `DRAFT`, `OPEN`, `CREATED`, `CLEARED`, `CANCELED`. Nach `complete()` liefert Billomat inzwischen `CREATED`. Hat `label()` für deutsche Bezeichnung.
- [`DeliveryNoteCommentActionKey`](../../src/Model/Enum/DeliveryNoteCommentActionKey.php): `CREATE`, `EDIT`, `OPEN`, `COMPLETE`, `CANCEL`, `CLEAR`, `CHANGE_STATUS`, `EMAIL`, `MAIL`, `COMMENT`.
- [`InvoiceItemType`](../../src/Model/Enum/InvoiceItemType.php): `PRODUCT`, `SERVICE` — von Rechnungspositionen geerbt.
- [`InvoicePdfType`](../../src/Model/Enum/InvoicePdfType.php): `SIGNED` (`signed`), `PRINT` (`print`) — Wire-Werte kleingeschrieben.
- [`NetGross`](../../src/Model/Enum/NetGross.php): `NET`, `GROSS`, `SETTINGS`.

## Stolpersteine

- **Lieferschein ist wert-arm.** Billomat liefert im Read-Modell keine Summen- oder Preisfelder, und das Dokument löst keine Forderung aus. Wer eine Zahlung erwartet, ist hier falsch — `InvoicesApi` oder `IncomingsApi` ist gemeint.
- **Kein `discount`/`validity`.** Anders als `OfferCreateOptions`/`ConfirmationCreateOptions` hat `DeliveryNoteCreateOptions` keine `discountRate`/`discountDays`/`discountDate`/`validityDays`-Felder. Wer aus Copy-Paste-Reflex sucht, findet sie nicht.
- **`update()` ändert keine Positionen.** Billomat ignoriert eingebettete `delivery-note-items` beim PUT. Stets die `$billomat->deliveryNotes->items` verwenden.
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
use Justpilot\Billomat\Api\EmailOptions;
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
$opts = new DeliveryNoteCreateOptions(
    clientId: 12345,
    invoiceId: 999,
    date: new DateTimeImmutable('today'),
    title: 'Lieferschein',
    intro: 'Bitte bei Empfang gegenzeichnen.',
);

$item = new DeliveryNoteItemCreateOptions(
    quantity: 3.0, unitPrice: 49.90,
    title: 'Hardware-Paket',
    type: InvoiceItemType::PRODUCT,
    unit: 'Stück',
);
$opts = $opts->withItem($item);

$note = $billomat->deliveryNotes->create($opts);
printf("Lieferschein #%d angelegt (DRAFT)\n", $note->id);

// 2) Tag setzen
$billomat->deliveryNotes->tags->create(
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
$email = new EmailOptions(to: ['spedition@example.com'], subject: 'Lieferschein {NUMBER}');
$billomat->deliveryNotes->email($note->id, $email);

// 5) Unterschriebene Empfangsbestätigung hochladen (z. B. vom Mobil-Workflow)
$signed = file_get_contents('/var/spool/scans/' . $opened?->deliveryNoteNumber . '.pdf');
if (false !== $signed) {
    $billomat->deliveryNotes->uploadSignature($note->id, base64_encode($signed));
}

// 6) Vermerk und als erledigt markieren
$billomat->deliveryNotes->comments->create(
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
