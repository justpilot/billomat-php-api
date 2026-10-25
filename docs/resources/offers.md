<!-- Quelle: https://www.billomat.com/api/angebote/ -->

# Offers (Angebote)

API-Wrapper für Angebote unter `/offers` und ihre drei Sub-Ressourcen (`/offer-items`, `/offer-comments`, `/offer-tags`).

## Zugriff

```php
$billomat->offers          // Angebote selbst
$billomat->offers->items      // Positionen eines Angebots
$billomat->offers->comments   // Kommentare / Audit-Trail
$billomat->offers->tags       // Schlagworte
```

## Modell

Ein Angebot durchläuft einen festen Status-Lebenszyklus:

```
DRAFT  ──complete()──▶  OPEN  ──win()─────▶  ACCEPTED
                         │
                         ├──lose()────▶  REJECTED
                         ├──clear()───▶  CLEARED
                         └──cancel()──▶  CANCELED
```

`undo()` führt jeden dieser Folge-Status zurück auf `OPEN`. Voll editierbar ist ein Angebot nur im Status `DRAFT`; ab `complete()` wird die endgültige `offer_number` vergeben und die Bearbeitung auf wenige Felder beschränkt. Aus einem angenommenen Angebot entstehen üblicherweise eine [Auftragsbestätigung](confirmations.md) oder direkt eine Rechnung.

## Endpunkt-Übersicht

### `/offers`

| Methode | HTTP | Pfad |
|---|---|---|
| `list($filters?)` | GET | `/offers` |
| `get($id)` | GET | `/offers/{id}` |
| `create($options)` | POST | `/offers` |
| `update($id, $options)` | PUT | `/offers/{id}` |
| `delete($id)` | DELETE | `/offers/{id}` |
| `complete($id, $templateId?)` | PUT | `/offers/{id}/complete` |
| `cancel($id)` | PUT | `/offers/{id}/cancel` |
| `win($id)` | PUT | `/offers/{id}/win` |
| `lose($id)` | PUT | `/offers/{id}/lose` |
| `clear($id)` | PUT | `/offers/{id}/clear` |
| `undo($id)` | PUT | `/offers/{id}/undo` |
| `email($id, $options?)` | POST | `/offers/{id}/email` |
| `uploadSignature($id, $base64Pdf)` | PUT | `/offers/{id}/upload-signature` |
| `pdf($id, $type?, $rawPdf?)` | GET | `/offers/{id}/pdf` |

### `/offer-items`

| Methode | HTTP | Pfad |
|---|---|---|
| `list($offerId, $query?)` | GET | `/offer-items?offer_id={id}` |
| `get($id)` | GET | `/offer-items/{id}` |
| `create($offerId, $options)` | POST | `/offer-items` |
| `update($id, $options)` | PUT | `/offer-items/{id}` |
| `delete($id)` | DELETE | `/offer-items/{id}` |

### `/offer-comments`

| Methode | HTTP | Pfad |
|---|---|---|
| `list($offerId, $actionKeys?)` | GET | `/offer-comments?offer_id={id}` |
| `get($id)` | GET | `/offer-comments/{id}` |
| `create($options)` | POST | `/offer-comments` |
| `delete($id)` | DELETE | `/offer-comments/{id}` |

### `/offer-tags`

| Methode | HTTP | Pfad |
|---|---|---|
| `list($offerId)` | GET | `/offer-tags?offer_id={id}` |
| `cloud()` | GET | `/offer-tags` |
| `get($id)` | GET | `/offer-tags/{id}` |
| `create($options)` | POST | `/offer-tags` |
| `delete($id)` | DELETE | `/offer-tags/{id}` |

## Methoden

### Offers

#### `list(array $filters = []): list<Offer>`

Liefert Angebote mit optionalen Filtern (z. B. `client_id`, `status`, `from`/`to`, `number_pre`, `tags`, `order_by`). Billomat liefert bei genau einem Treffer das Objekt als Assoc-Array statt einer Liste; `array_is_list()` erkennt das und packt den Einzeltreffer in ein Array.

```php
$open = $billomat->offers->list([
    'status' => 'OPEN,ACCEPTED',
    'order_by' => 'date+DESC',
]);
```

#### `get(int $id): ?Offer`

Liefert `null` bei 404. Eingebettete `offer-items` und `taxes` werden automatisch hydriert.

#### `create(OfferCreateOptions $options): Offer`

Positionen können direkt im `create()`-Call über `items: […]` oder `withItem()` mitgegeben werden — sie landen als `offer-items.offer-item` im Payload. Neue Angebote starten im Status `DRAFT`.

```php
use Justpilot\Billomat\Api\OfferCreateOptions;
use Justpilot\Billomat\Api\OfferItemCreateOptions;

$opts = new OfferCreateOptions(
    clientId: 12345,
    title: 'Webhosting-Angebot',
    date: new DateTimeImmutable('2026-06-02'),
    validityDays: 30,
    items: [
        new OfferItemCreateOptions(quantity: 1.0, unitPrice: 99.00, title: 'Setup-Pauschale'),
    ],
);

$offer = $billomat->offers->create($opts);
```

#### `update(int $id, OfferUpdateOptions $options): Offer`

Schmaler Subset für Partial-Updates. Voll editierbar nur im Status `DRAFT`. Positionen lassen sich per PUT auf `/offers/{id}` nicht ändern — dafür die `$billomat->offers->items` verwenden. Die Klasse hat daher kein `items`-Feld.

#### `complete(int $id, ?int $templateId = null): void`

DRAFT → OPEN. Vergibt die endgültige `offer_number` und generiert das PDF. Optional kann eine abweichende Template-ID übergeben werden.

```php
$billomat->offers->complete($offer->id, templateId: 42);
```

#### `delete(int $id): void`

Erlaubt nur im Status `DRAFT`. Bei anderen Status liefert Billomat 422 → `ValidationException`.

#### Status-Übergänge

```php
$billomat->offers->cancel($offer->id);   // OPEN/ACCEPTED → CANCELED
$billomat->offers->win($offer->id);      // OPEN          → ACCEPTED
$billomat->offers->lose($offer->id);     // OPEN          → REJECTED
$billomat->offers->clear($offer->id);    // OPEN          → CLEARED
$billomat->offers->undo($offer->id);     // {win,lose,clear,cancel} → OPEN
```

Alle geben `true` bei HTTP 200 zurück. Ungültige Übergänge führen zu `ValidationException` (422) oder `HttpException`.

#### `email(int $id, ?EmailOptions $options = null): void`

Versendet das Angebot per E-Mail. Ohne Options nutzt Billomat alle Defaults (Absender, Betreff, Body, Vorlage); das PDF wird automatisch angehängt.

```php
use Justpilot\Billomat\Api\EmailOptions;

$email = new EmailOptions(
    to: ['kunde@example.com'],
    bcc: ['vertrieb@meinefirma.de'],
    subject: 'Ihr Angebot {NUMBER}',
);

$billomat->offers->email($offer->id, $email);
```

#### `uploadSignature(int $id, string $base64Pdf): void`

Lädt eine unterschriebene PDF-Version hoch. `$base64Pdf` ist der Base64-codierte PDF-Inhalt (ohne `data:`-Prefix).

#### `pdf(int $id, ?InvoicePdfType $type = null, bool $rawPdf = false): OfferPdf|string`

Standardmäßig liefert die Methode ein `OfferPdf`-Model mit Base64-Inhalt; mit `$rawPdf = true` umgeht sie die JSON-Schicht und gibt die rohen Binärbytes zurück. Der `$type`-Parameter teilt das `InvoicePdfType`-Enum mit dem Rechnungs-PDF (`SIGNED`, `PRINT`).

```php
use Justpilot\Billomat\Model\Enum\InvoicePdfType;

$pdf = $billomat->offers->pdf($offer->id);              // OfferPdf
file_put_contents('angebot.pdf', $pdf->getBinary());

$bytes = $billomat->offers->pdf($offer->id, InvoicePdfType::SIGNED, rawPdf: true);
```

### Offer Items

```php
$items = $billomat->offers->items->list($offer->id);

$item = $billomat->offers->items->create(
    $offer->id,
    new OfferItemCreateOptions(quantity: 2.0, unitPrice: 49.90),
);

$billomat->offers->items->update($item->id, $opts);
$billomat->offers->items->delete($item->id);
```

`create()` injiziert `offer_id` automatisch in den Payload — `OfferItemCreateOptions` selbst hat dieses Feld nicht. `update()` verwendet dieselbe Klasse wie `create()`.

### Offer Comments

`list()` kann optional auf bestimmte `actionkey`-Werte gefiltert werden (kommasepariert an die API geschickt):

```php
use Justpilot\Billomat\Model\Enum\OfferCommentActionKey;

$comments = $billomat->offers->comments->list(
    $offer->id,
    actionKeys: [OfferCommentActionKey::EMAIL, OfferCommentActionKey::WIN],
);

$billomat->offers->comments->create(
    new OfferCommentCreateOptions(offerId: $offer->id, comment: 'Telefonisch zugesagt.'),
);
```

Kommentare zu System-Aktionen (`COMPLETE`, `WIN`, `EMAIL`, …) legt Billomat automatisch an.

### Offer Tags

```php
use Justpilot\Billomat\Api\OfferTagCreateOptions;

$tag = $billomat->offers->tags->create(
    new OfferTagCreateOptions(offerId: $offer->id, name: 'hot-lead'),
);

$tags  = $billomat->offers->tags->list($offer->id); // list<OfferTag>
$cloud = $billomat->offers->tags->cloud();                 // list<OfferTagCloudEntry>, aggregiert mit count
```

## Write-Modell: `OfferCreateOptions`

Konstruktor: `new OfferCreateOptions(int $clientId, …)` — `clientId` ist Pflicht und erster Parameter. Alle Felder sind Konstruktor-Parameter mit Default und werden als benannte Argumente übergeben. Das Objekt ist unveränderlich (`final readonly`); jedes Feld hat eine `with*()`-Methode, die eine geänderte Kopie liefert (z. B. `$opts->withTitle('Neu')`).

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `clientId` | `client_id` | `int` | Pflicht (Konstruktor) |
| `contactId` | `contact_id` | `?int` | |
| `address` | `address` | `?string` | Überschreibt Kundenadresse |
| `numberPre` | `number_pre` | `?string` | |
| `number` | `number` | `?int` | |
| `numberLength` | `number_length` | `?int` | |
| `date` | `date` | `?\DateTimeImmutable` | als `Y-m-d` serialisiert |
| `validityDays` | `validity_days` | `?int` | Gültigkeit in Tagen |
| `discountRate` | `discount_rate` | `?int` | |
| `discountDays` | `discount_days` | `?int` | |
| `discountDate` | `discount_date` | `?\DateTimeImmutable` | als `Y-m-d` serialisiert |
| `title` | `title` | `?string` | |
| `label` | `label` | `?string` | |
| `intro` | `intro` | `?string` | |
| `note` | `note` | `?string` | |
| `reduction` | `reduction` | `?string` | absolut oder mit `%` |
| `currencyCode` | `currency_code` | `?string` | ISO 4217 |
| `netGross` | `net_gross` | `?NetGross` | `NET`/`GROSS`/`SETTINGS` |
| `quote` | `quote` | `?float` | Währungs-Quote |
| `freeTextId` | `free_text_id` | `?int` | |
| `templateId` | `template_id` | `?int` | |

### Positionen

Positionen werden über den Konstruktor-Parameter `items` (`list<OfferItemCreateOptions>`) übergeben und stehen danach in der Property `$items`. `withItem()` liefert eine Kopie mit einer zusätzlichen Position:

```php
$opts = $opts->withItem(new OfferItemCreateOptions(quantity: 1.0, unitPrice: 99.0));
```

`toArray()` rendert sie als `offer-items.offer-item`.

## Write-Modell: `OfferUpdateOptions`

Spiegelt `OfferCreateOptions` ohne `clientId`-Pflicht (jetzt optional) und ohne `items`. `discountRate` ist hier `?float` statt `?int`. `null` heißt „nicht senden“, der Wert bleibt unverändert. Mit `Clear::Field` (`Justpilot\Billomat\Api\Clear`) wird das Feld als JSON-`null` gesendet und von Billomat geleert, z. B. `new OfferUpdateOptions(note: Clear::Field)`.

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `clientId` | `client_id` | `?int` | |
| `contactId` | `contact_id` | `?int` | |
| `address` | `address` | `?string` | |
| `numberPre`, `number`, `numberLength` | analog | `?string`/`?int`/`?int` | |
| `date` | `date` | `?\DateTimeImmutable` | |
| `validityDays` | `validity_days` | `?int` | |
| `discountRate` | `discount_rate` | `?float` | |
| `discountDays` | `discount_days` | `?int` | |
| `discountDate` | `discount_date` | `?\DateTimeImmutable` | |
| `title`, `label`, `intro`, `note`, `reduction` | analog | `?string` | |
| `netGross` | `net_gross` | `?NetGross` | |
| `currencyCode` | `currency_code` | `?string` | |
| `quote` | `quote` | `?float` | |
| `freeTextId`, `templateId` | analog | `?int` | |

## Write-Modell: `OfferItemCreateOptions`

Konstruktor: `new OfferItemCreateOptions(float $quantity, float $unitPrice)`. Beide Werte sind Pflicht und werden auch dann mitgeschickt, wenn sie `0` sind (siehe `ARRAY_FILTER_USE_BOTH` in `toArray()`).

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `quantity` | `quantity` | `float` | Pflicht (Konstruktor) |
| `unitPrice` | `unit_price` | `float` | Pflicht (Konstruktor) |
| `type` | `type` | `?InvoiceItemType` | `PRODUCT`/`SERVICE` |
| `articleId` | `article_id` | `?int` | |
| `title` | `title` | `?string` | |
| `description` | `description` | `?string` | |
| `unit` | `unit` | `?string` | |
| `taxName` | `tax_name` | `?string` | |
| `taxRate` | `tax_rate` | `?float` | |
| `taxChangedManually` | `tax_changed_manually` | `?bool` | siehe Stolpersteine |
| `reduction` | `reduction` | `?string` | absolut oder mit `%` |
| `position` | `position` | `?int` | |

`ItemsApi::update()` verwendet dieselbe Klasse — es gibt keinen separaten Update-Typ. Das heißt: der Konstruktor verlangt `quantity` und `unitPrice` auch dann, wenn du sie gar nicht ändern willst.

## Write-Modell: `OfferCommentCreateOptions`

Konstruktor: `new OfferCommentCreateOptions(int $offerId, string $comment)` — beide Pflicht.

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `offerId` | `offer_id` | `int` | Pflicht (Konstruktor) |
| `comment` | `comment` | `string` | Pflicht (Konstruktor) |
| `actionkey` | `actionkey` | `?OfferCommentActionKey` | typischerweise nur bei System-Aktionen gesetzt |

## Write-Modell: `OfferTagCreateOptions`

Konstruktor: `new OfferTagCreateOptions(int $offerId, string $name)` — beide Pflicht.

| Property | Billomat-Feld | Typ |
|---|---|---|
| `offerId` | `offer_id` | `int` (Pflicht) |
| `name` | `name` | `string` (Pflicht) |

## Write-Modell: `EmailOptions`

Gemeinsame Klasse für den E-Mail-Versand aller Dokumenttypen (Rechnungen, Angebote, Auftragsbestätigungen, Gutschriften, Lieferscheine, Mahnungen, Briefe); die vollständige Beschreibung steht bei [Invoices](invoices.md#write-modell-emailoptions). Keine Pflichtfelder; ohne explizite Werte fällt Billomat auf die im Account hinterlegten Defaults zurück.

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `emailTemplateId` | `email_template_id` | `?int` | |
| `from` | `from` | `?string` | Absender-Override |
| `to` | `recipients.to` | `list<string>` | leeres Array → weggelassen |
| `cc` | `recipients.cc` | `list<string>` | |
| `bcc` | `recipients.bcc` | `list<string>` | |
| `subject` | `subject` | `?string` | |
| `body` | `body` | `?string` | |
| `filename` | `filename` | `?string` | Anhängender Dateiname |
| `attachments` | `attachments.attachment[]` | `list<array{filename,mimetype,base64file}>` | zusätzliche Anhänge |

## Read-Modell: `Offer`

`final readonly class Offer`. Die wichtigsten Felder:

| Property | Typ |
|---|---|
| `id` | `?int` |
| `clientId` | `int` |
| `contactId` | `?int` |
| `created` | `?\DateTimeImmutable` |
| `offerNumber` | `?string` — Wird erst beim `complete()` gesetzt |
| `number`, `numberPre`, `numberLength` | `?int`, `?string`, `?int` |
| `status` | `?OfferStatus` |
| `date` | `?\DateTimeImmutable` |
| `validityDays` | `?int` |
| `address` | `?string` |
| `title`, `label`, `intro`, `note`, `reduction` | `?string` |
| `totalGross`, `totalNet` | `?float` |
| `totalGrossUnreduced`, `totalNetUnreduced` | `?float` |
| `netGross` | `?NetGross` |
| `currencyCode` | `?string` |
| `quote` | `?float` |
| `freeTextId`, `templateId` | `?int` |
| `taxes` | `list<array{name:string,rate:float,amount:float}>` |
| `items` | `list<OfferItem>` — eingebettet aus `offer-items.offer-item` |
| `updated` | `?\DateTimeImmutable` |
| `validityDate` | `?\DateTimeImmutable` (Gültig bis, nur Datum) |
| `totalReduction` | `?float` |
| `customfield` | `?string` (Freies Zusatzfeld, siehe [Custom-Meta-Attribute](../concepts/custom-meta-attributes.md)) |

## Read-Modell: `OfferItem`

`final readonly`. Felder analog zu `InvoiceItem`, mit `offerId` statt `invoiceId`.

| Property | Typ |
|---|---|
| `id`, `offerId`, `articleId`, `position` | `?int` |
| `unit` | `?string` |
| `quantity`, `unitPrice` | `float` |
| `taxName` | `?string` |
| `taxRate` | `?float` |
| `taxChangedManually` | `?bool` |
| `title`, `description`, `reduction` | `?string` |
| `type` | `?InvoiceItemType` |
| `totalGross`, `totalNet`, `totalGrossUnreduced`, `totalNetUnreduced` | `?float` |
| `optional` | `?bool` (Optionale Position) |
| `customfield` | `?string` (Freies Zusatzfeld, siehe [Custom-Meta-Attribute](../concepts/custom-meta-attributes.md)) |

## Read-Modell: `OfferComment`

`final readonly`.

| Property | Typ |
|---|---|
| `id` | `?int` |
| `offerId` | `int` |
| `comment` | `?string` |
| `created` | `?\DateTimeImmutable` |
| `userId` | `?int` |
| `actionkey` | `?OfferCommentActionKey` |
| `actionkeyRaw` | `?string` — Roh-Wert, falls Billomat einen unbekannten `actionkey` liefert |
| `public` | `?bool` |
| `byClient` | `?bool` |
| `emailId` | `?int` |
| `clientId` | `?int` |
| `customfield` | `?string` (Freies Zusatzfeld, siehe [Custom-Meta-Attribute](../concepts/custom-meta-attributes.md)) |

## Read-Modell: `OfferTag`

`final readonly`. Felder: `id` (`?int`), `offerId` (`int`), `name` (`string`), `customfield` (`?string`).

## Read-Modell: `OfferTagCloudEntry`

`final readonly`. Aggregat der Tag-Cloud: `id` (`?int`), `name` (`string`), `count` (`int`).

## Read-Modell: `OfferPdf`

`final class OfferPdf` (nicht `readonly`, da als Container für Binärdaten gedacht).

| Property | Typ |
|---|---|
| `id` | `int` |
| `offerId` | `int` |
| `created` | `?\DateTimeImmutable` |
| `filename` | `string` |
| `mimeType` | `string` — Default `application/pdf` |
| `fileSize` | `int` |
| `base64file` | `string` |

`getBinary(): string` decodiert `base64file` und liefert die rohen PDF-Bytes (`''` bei ungültigem Base64).

## Verwendete Enums

- [`OfferStatus`](../../src/Model/Enum/OfferStatus.php): `DRAFT`, `OPEN`, `ACCEPTED`, `REJECTED`, `CLEARED`, `CANCELED`. Hat `label()` für deutsche Bezeichnung.
- [`OfferCommentActionKey`](../../src/Model/Enum/OfferCommentActionKey.php): `CREATE`, `EDIT`, `OPEN`, `COMPLETE`, `CANCEL`, `WIN`, `LOSE`, `CLEAR`, `CHANGE_STATUS`, `EMAIL`, `MAIL`, `COMMENT`.
- [`InvoiceItemType`](../../src/Model/Enum/InvoiceItemType.php): `PRODUCT`, `SERVICE` — von Rechnungspositionen geerbt.
- [`InvoicePdfType`](../../src/Model/Enum/InvoicePdfType.php): `SIGNED` (`signed`), `PRINT` (`print`) — Wire-Werte kleingeschrieben.
- [`NetGross`](../../src/Model/Enum/NetGross.php): `NET`, `GROSS`, `SETTINGS`.

## Stolpersteine

- **Status-Übergänge sind streng.** `win()`/`lose()`/`clear()`/`cancel()` nur aus `OPEN`. Wer aus einem `DRAFT` direkt einen Versuch wagt, bekommt 422. Reihenfolge: erst `complete()`, dann Status setzen.
- **`update()` ändert keine Positionen.** Billomat ignoriert eingebettete `offer-items` beim PUT auf `/offers/{id}`. Stets die `$billomat->offers->items` verwenden.
- **`delete()` nur im `DRAFT`.** Sobald `complete()` gelaufen ist, ist das Angebot nummeriert und nicht mehr löschbar — nur noch `cancel()` ist möglich.
- **`OfferItemCreateOptions` ist auch der Update-Typ.** Es gibt keinen separaten `OfferItemUpdateOptions`-Typ. Der Konstruktor verlangt also immer `quantity` und `unitPrice`, auch wenn du nur den Titel ändern willst. Werte ggf. aus dem bestehenden `OfferItem` übernehmen.
- **`array_is_list`-Normalisierung in `list()`.** Bei genau einem Treffer liefert Billomat `offer` als Objekt statt Liste — die Methode erkennt das und packt das Single-Result selbst in ein Array.
- **`tax_changed_manually` ist Pflicht für manuelle `taxRate`-Werte.** Ohne das Flag ignoriert Billomat den explizit gesetzten Steuersatz und nimmt den Default des Artikels/Kunden.
- **`pdf()` mit `$rawPdf = true` umgeht JSON komplett.** Die Methode liest den Body per `getRaw()` als Binär-String. HTTP-Fehler kommen trotzdem als SDK-Exceptions (`NotFoundException`, `AuthenticationException`, …), weil `BillomatHttpClient` sie bereits beim Request wirft.
- **`uploadSignature()` erwartet reines Base64**, kein Data-URI-Prefix. Wer einen `data:application/pdf;base64,...`-String reinwirft, bekommt eine kaputte PDF im Billomat-Backend.
- **`OfferPdf` ist nicht `readonly`** — alle anderen Read-Models sind es. Pragmatische Ausnahme, damit der Container Binärdaten halten kann.
- **`OfferCommentActionKey` wird wire-seitig auf Großschreibung erwartet** (`WIN`, `LOSE`, …); das Enum spiegelt die exakten Werte. `tryFrom()` mappt unbekannte Werte auf `null`, der Roh-String landet zusätzlich in `actionkeyRaw`.

## End-to-End-Beispiel

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\OfferCommentCreateOptions;
use Justpilot\Billomat\Api\OfferCreateOptions;
use Justpilot\Billomat\Api\EmailOptions;
use Justpilot\Billomat\Api\OfferItemCreateOptions;
use Justpilot\Billomat\Api\OfferTagCreateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\Enum\InvoiceItemType;
use Justpilot\Billomat\Model\Enum\OfferCommentActionKey;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

// 1) Angebot anlegen (Status: DRAFT) mit zwei Positionen
$opts = new OfferCreateOptions(
    clientId: 12345,
    title: 'Webhosting & Beratung',
    date: new DateTimeImmutable('today'),
    validityDays: 30,
    intro: 'Vielen Dank für Ihre Anfrage.',
);

$setup = new OfferItemCreateOptions(
    quantity: 1.0, unitPrice: 250.00,
    title: 'Setup-Pauschale',
    type: InvoiceItemType::SERVICE,
);
$opts = $opts->withItem($setup);

$hosting = new OfferItemCreateOptions(
    quantity: 12.0, unitPrice: 19.90,
    title: 'Hosting Basic (12 Monate)',
    unit: 'Monat',
);
$opts = $opts->withItem($hosting);

$offer = $billomat->offers->create($opts);
printf("Angebot #%d (DRAFT) angelegt, Brutto: %.2f %s\n",
    $offer->id,
    $offer->totalGross ?? 0.0,
    $offer->currencyCode ?? 'EUR',
);

// 2) Tag setzen
$billomat->offers->tags->create(
    new OfferTagCreateOptions(offerId: $offer->id, name: 'hot-lead'),
);

// 3) Abschließen → OPEN, Nummer wird vergeben
$billomat->offers->complete($offer->id);

$opened = $billomat->offers->get($offer->id);
printf("Status: %s, Angebotsnummer: %s\n",
    $opened?->status?->label() ?? '?',
    $opened?->offerNumber ?? '?',
);

// 4) Versenden — Defaults vom Billomat-Account
$email = new EmailOptions(to: ['kunde@example.com'], bcc: ['vertrieb@meinefirma.de']);
$billomat->offers->email($offer->id, $email);

// 5) Kunde nimmt an → win()
$billomat->offers->win($offer->id);

$billomat->offers->comments->create(
    new OfferCommentCreateOptions(
        offerId: $offer->id,
        comment: 'Bestätigung per Telefon am 02.06.',
    ),
);

// 6) PDF herunterladen (signierte Version, falls vorhanden)
$pdf = $billomat->offers->pdf($offer->id);
file_put_contents("angebot-{$opened?->offerNumber}.pdf", $pdf->getBinary());

// 7) Audit-Trail einsehen — nur Statuswechsel
$status = $billomat->offers->comments->list(
    $offer->id,
    actionKeys: [
        OfferCommentActionKey::COMPLETE,
        OfferCommentActionKey::WIN,
        OfferCommentActionKey::EMAIL,
    ],
);

foreach ($status as $c) {
    printf("[%s] %s: %s\n",
        $c->created?->format('Y-m-d H:i') ?? '?',
        $c->actionkey?->value ?? $c->actionkeyRaw ?? '-',
        $c->comment ?? '',
    );
}
```
