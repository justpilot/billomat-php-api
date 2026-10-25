<!-- Quelle: https://www.billomat.com/api/auftragsbestaetigungen/ -->

# Confirmations (Auftragsbestätigungen)

API-Wrapper für Auftragsbestätigungen unter `/confirmations` und ihre drei Sub-Ressourcen (`/confirmation-items`, `/confirmation-comments`, `/confirmation-tags`).

## Zugriff

```php
$billomat->confirmations          // Auftragsbestätigungen selbst
$billomat->confirmations->items      // Positionen
$billomat->confirmations->comments   // Kommentare / Audit-Trail
$billomat->confirmations->tags       // Schlagworte
```

## Modell

Eine Auftragsbestätigung dokumentiert verbindlich einen Auftrag — typischerweise nach Annahme eines [Angebots](offers.md) und vor der eigentlichen Rechnung. Der Status-Lebenszyklus ist schlanker als beim Angebot, kennt aber keine Annahme/Ablehnung:

```
DRAFT  ──complete()──▶  OPEN  ──clear()───▶  CLEARED
                         │
                         └──cancel()──▶  CANCELED
```

`undo()` setzt `CLEARED`/`CANCELED` zurück auf `OPEN`. Voll editierbar ist die Auftragsbestätigung nur im Status `DRAFT`. Die Quelle aus einem Angebot wird über `offerId` referenziert.

## Endpunkt-Übersicht

### `/confirmations`

| Methode | HTTP | Pfad |
|---|---|---|
| `list($filters?)` | GET | `/confirmations` |
| `get($id)` | GET | `/confirmations/{id}` |
| `create($options)` | POST | `/confirmations` |
| `update($id, $options)` | PUT | `/confirmations/{id}` |
| `delete($id)` | DELETE | `/confirmations/{id}` |
| `complete($id, $templateId?)` | PUT | `/confirmations/{id}/complete` |
| `cancel($id)` | PUT | `/confirmations/{id}/cancel` |
| `clear($id)` | PUT | `/confirmations/{id}/clear` |
| `undo($id)` | PUT | `/confirmations/{id}/undo` |
| `email($id, $options?)` | POST | `/confirmations/{id}/email` |
| `uploadSignature($id, $base64Pdf)` | PUT | `/confirmations/{id}/upload-signature` |
| `pdf($id, $type?, $rawPdf?)` | GET | `/confirmations/{id}/pdf` |

### `/confirmation-items`

| Methode | HTTP | Pfad |
|---|---|---|
| `list($confirmationId, $query?)` | GET | `/confirmation-items?confirmation_id={id}` |
| `get($id)` | GET | `/confirmation-items/{id}` |
| `create($confirmationId, $options)` | POST | `/confirmation-items` |
| `update($id, $options)` | PUT | `/confirmation-items/{id}` |
| `delete($id)` | DELETE | `/confirmation-items/{id}` |

### `/confirmation-comments`

| Methode | HTTP | Pfad |
|---|---|---|
| `list($confirmationId, $actionKeys?)` | GET | `/confirmation-comments?confirmation_id={id}` |
| `get($id)` | GET | `/confirmation-comments/{id}` |
| `create($options)` | POST | `/confirmation-comments` |
| `delete($id)` | DELETE | `/confirmation-comments/{id}` |

### `/confirmation-tags`

| Methode | HTTP | Pfad |
|---|---|---|
| `list($confirmationId)` | GET | `/confirmation-tags?confirmation_id={id}` |
| `cloud()` | GET | `/confirmation-tags` |
| `get($id)` | GET | `/confirmation-tags/{id}` |
| `create($options)` | POST | `/confirmation-tags` |
| `delete($id)` | DELETE | `/confirmation-tags/{id}` |

## Methoden

### Confirmations

#### `list(ConfirmationFilter|array $filters = []): list<Confirmation>`

Filter laut Billomat-Doku: `client_id`, `contact_id`, `status`, `from`, `to`, `number_pre`, `tags`, `order_by` u. a. Wie bei `OffersApi` normalisiert `array_is_list()` Single-Treffer auf eine Liste.

Empfohlen ist der typisierte Filter `ConfirmationFilter` mit den camelCase-Feldern `clientId`, `contactId`, `confirmationNumber`, `status` (`list<ConfirmationStatus>`), `from`/`to` (`DateTimeImmutable`), `label`, `intro`, `note`, `tags` und `articleId`; Mehrfachwerte sind Listen und werden kommasepariert gesendet, dazu kommen `orderBy`, `page`, `perPage` und `extra` für weitere Query-Parameter (z. B. `number_pre`). Ein einfaches Array funktioniert weiterhin. Array-Werte serialisieren dort als `key[]=…`.

```php
use Justpilot\Billomat\Api\ConfirmationFilter;
use Justpilot\Billomat\Api\OrderBy;
use Justpilot\Billomat\Model\Enum\ConfirmationStatus;

$open = $billomat->confirmations->list(new ConfirmationFilter(
    status: [ConfirmationStatus::OPEN],
    orderBy: OrderBy::desc('date'),
));
```

#### `get(int $id): ?Confirmation`

Liefert `null` bei 404. Eingebettete `confirmation-items` und `taxes` werden automatisch hydriert.

#### `create(ConfirmationCreateOptions $options): Confirmation`

Positionen können direkt über `items: […]` oder `withItem()` im Payload landen (als `confirmation-items.confirmation-item`). Optional kann `offerId` gesetzt werden, um die Auftragsbestätigung an ein bestehendes Angebot zu hängen.

```php
use Justpilot\Billomat\Api\ConfirmationCreateOptions;
use Justpilot\Billomat\Api\ConfirmationItemCreateOptions;
use Justpilot\Billomat\Model\Money;

$opts = new ConfirmationCreateOptions(
    clientId: 12345,
    offerId: 678,
    title: 'Auftragsbestätigung',
    date: new DateTimeImmutable('today'),
    items: [
        new ConfirmationItemCreateOptions(quantity: 1.0, unitPrice: Money::of('99.00')),
    ],
);

$conf = $billomat->confirmations->create($opts);
```

#### `update(int $id, ConfirmationUpdateOptions $options): Confirmation`

Schmaler Subset, voll editierbar nur im `DRAFT`. Positionen werden hier nicht verarbeitet — `$billomat->confirmations->items` nutzen.

#### `complete(int $id, ?int $templateId = null): void`

DRAFT → OPEN. Vergibt die endgültige `confirmation_number` und erzeugt das PDF. Optional kann eine abweichende Template-ID übergeben werden.

#### `delete(int $id): void`

Nur im `DRAFT` erlaubt — danach kommt `cancel()` infrage.

#### Status-Übergänge

```php
$billomat->confirmations->cancel($conf->id);   // OPEN/CLEARED → CANCELED
$billomat->confirmations->clear($conf->id);    // OPEN          → CLEARED
$billomat->confirmations->undo($conf->id);     // {clear,cancel} → OPEN
```

Alle geben `true` bei HTTP 200 zurück. Ungültige Übergänge → `ValidationException` (422).

#### `email(int $id, ?EmailOptions $options = null): void`

Versendet die Auftragsbestätigung per E-Mail. Ohne Options nutzt Billomat die im Account hinterlegten Defaults und hängt das PDF automatisch an.

```php
use Justpilot\Billomat\Api\EmailOptions;

$email = new EmailOptions(to: ['kunde@example.com']);
$billomat->confirmations->email($conf->id, $email);
```

#### `uploadSignature(int $id, string $base64Pdf): void`

Lädt eine unterschriebene PDF-Version hoch. `$base64Pdf` ist der reine Base64-codierte PDF-Inhalt.

#### `pdf(int $id, ?InvoicePdfType $type = null, bool $rawPdf = false): ConfirmationPdf|string`

Liefert ein `ConfirmationPdf`-Model mit Base64-Inhalt; mit `$rawPdf = true` umgeht die Methode JSON und gibt die rohen Bytes zurück.

```php
$pdf = $billomat->confirmations->pdf($conf->id);
file_put_contents('ab.pdf', $pdf->getBinary());
```

### Confirmation Items

```php
$items = $billomat->confirmations->items->list($conf->id);

$item = $billomat->confirmations->items->create(
    $conf->id,
    new ConfirmationItemCreateOptions(quantity: 2.0, unitPrice: Money::of('49.90')),
);

$billomat->confirmations->items->update($item->id, $opts);
$billomat->confirmations->items->delete($item->id);
```

`create()` injiziert `confirmation_id` automatisch — die Options-Klasse selbst hat dieses Feld nicht. `update()` verwendet dieselbe Klasse wie `create()`.

### Confirmation Comments

```php
use Justpilot\Billomat\Api\ConfirmationCommentCreateOptions;
use Justpilot\Billomat\Model\Enum\ConfirmationCommentActionKey;

$comments = $billomat->confirmations->comments->list(
    $conf->id,
    actionKeys: [ConfirmationCommentActionKey::COMPLETE, ConfirmationCommentActionKey::EMAIL],
);

$billomat->confirmations->comments->create(
    new ConfirmationCommentCreateOptions(
        confirmationId: $conf->id,
        comment: 'Auftrag bestätigt am 02.06.',
    ),
);
```

### Confirmation Tags

```php
use Justpilot\Billomat\Api\ConfirmationTagCreateOptions;

$tag = $billomat->confirmations->tags->create(
    new ConfirmationTagCreateOptions(confirmationId: $conf->id, name: 'auftrag-2026'),
);

$tags  = $billomat->confirmations->tags->list($conf->id);
$cloud = $billomat->confirmations->tags->cloud();
```

## Write-Modell: `ConfirmationCreateOptions`

Konstruktor: `new ConfirmationCreateOptions(int $clientId, …)` — `clientId` ist Pflicht und erster Parameter. Alle Felder sind Konstruktor-Parameter mit Default und werden als benannte Argumente übergeben. Das Objekt ist unveränderlich (`final readonly`); jedes Feld hat eine `with*()`-Methode, die eine geänderte Kopie liefert (z. B. `$opts->withTitle('Neu')`).

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `clientId` | `client_id` | `int` | Pflicht (Konstruktor) |
| `contactId` | `contact_id` | `?int` | |
| `address` | `address` | `?string` | |
| `numberPre`, `number`, `numberLength` | analog | `?string`/`?int`/`?int` | |
| `date` | `date` | `?\DateTimeImmutable` | als `Y-m-d` serialisiert |
| `discountRate` | `discount_rate` | `?int` | |
| `discountDays` | `discount_days` | `?int` | |
| `discountDate` | `discount_date` | `?\DateTimeImmutable` | als `Y-m-d` serialisiert |
| `title`, `label`, `intro`, `note`, `reduction` | analog | `?string` | |
| `currencyCode` | `currency_code` | `?string` | |
| `netGross` | `net_gross` | `?NetGross` | |
| `quote` | `quote` | `?float` | |
| `offerId` | `offer_id` | `?int` | Quell-Angebot |
| `freeTextId` | `free_text_id` | `?int` | |
| `templateId` | `template_id` | `?int` | |

### Positionen

Positionen werden als Liste über den Konstruktor-Parameter `items` übergeben (`items: [new ConfirmationItemCreateOptions(…), …]`) und stehen danach in der Property `$items`. `withItem()` liefert eine Kopie mit einer zusätzlichen Position. `toArray()` rendert sie als `confirmation-items.confirmation-item`.

## Write-Modell: `ConfirmationUpdateOptions`

Spiegelt `ConfirmationCreateOptions` ohne `clientId`-Pflicht (jetzt optional) und ohne `items`. `discountRate` ist hier `?float` statt `?int`. `null` heißt „nicht senden“, der Wert bleibt unverändert. Mit `Clear::Field` (`Justpilot\Billomat\Api\Clear`) wird das Feld als JSON-`null` gesendet und von Billomat geleert, z. B. `new ConfirmationUpdateOptions(note: Clear::Field)`.

| Property | Billomat-Feld | Typ |
|---|---|---|
| `clientId` | `client_id` | `?int` |
| `contactId`, `numberPre`, `number`, `numberLength`, `freeTextId`, `templateId`, `offerId` | analog | `?int`/`?string` |
| `address`, `title`, `label`, `intro`, `note`, `reduction`, `currencyCode` | analog | `?string` |
| `date`, `discountDate` | analog | `?\DateTimeImmutable` |
| `discountRate` | `discount_rate` | `?float` |
| `discountDays` | `discount_days` | `?int` |
| `netGross` | `net_gross` | `?NetGross` |
| `quote` | `quote` | `?float` |

## Write-Modell: `ConfirmationItemCreateOptions`

Konstruktor: `new ConfirmationItemCreateOptions(float $quantity, Money $unitPrice)`. Beide Pflicht, werden auch bei `0` mitgeschickt.

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `quantity` | `quantity` | `float` | Pflicht (Konstruktor) |
| `unitPrice` | `unit_price` | `Money` | Pflicht (Konstruktor) |
| `type` | `type` | `?InvoiceItemType` | |
| `articleId` | `article_id` | `?int` | |
| `title`, `description`, `unit` | analog | `?string` | |
| `taxName`, `taxRate`, `taxChangedManually` | analog | `?string`/`?float`/`?bool` | |
| `reduction` | `reduction` | `?string` | |
| `position` | `position` | `?int` | |

`ItemsApi::update()` verwendet dieselbe Klasse — es gibt keinen separaten Update-Typ.

## Write-Modell: `ConfirmationCommentCreateOptions`

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `confirmationId` | `confirmation_id` | `int` | Pflicht (Konstruktor) |
| `comment` | `comment` | `string` | Pflicht (Konstruktor) |
| `actionkey` | `actionkey` | `?ConfirmationCommentActionKey` | typischerweise nur bei System-Aktionen |

## Write-Modell: `ConfirmationTagCreateOptions`

| Property | Billomat-Feld | Typ |
|---|---|---|
| `confirmationId` | `confirmation_id` | `int` (Pflicht) |
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

## Read-Modell: `Confirmation`

`final readonly class Confirmation`.

| Property | Typ |
|---|---|
| `id` | `?int` |
| `clientId` | `int` |
| `contactId` | `?int` |
| `created` | `?\DateTimeImmutable` |
| `confirmationNumber` | `?string` — erst nach `complete()` |
| `number`, `numberPre`, `numberLength` | `?int`/`?string`/`?int` |
| `status` | `?ConfirmationStatus` |
| `date` | `?\DateTimeImmutable` |
| `address` | `?string` |
| `title`, `label`, `intro`, `note`, `reduction` | `?string` |
| `totalGross`, `totalNet`, `totalGrossUnreduced`, `totalNetUnreduced` | `?Money` |
| `netGross` | `?NetGross` |
| `currencyCode` | `?string` |
| `quote` | `?float` |
| `offerId` | `?int` |
| `freeTextId`, `templateId` | `?int` |
| `taxes` | `list<TaxSummary>` (`name`, `rate`, `amount` als `Money`) |
| `items` | `list<ConfirmationItem>` |
| `updated` | `?\DateTimeImmutable` |
| `totalReduction` | `?Money` |
| `customfield` | `?string` (Freies Zusatzfeld, siehe [Custom-Meta-Attribute](../concepts/custom-meta-attributes.md)) |

## Read-Modell: `ConfirmationItem`

`final readonly`.

| Property | Typ |
|---|---|
| `id`, `confirmationId`, `articleId`, `position` | `?int` |
| `unit` | `?string` |
| `quantity`, `unitPrice` | `float`, `Money` |
| `taxName` | `?string` |
| `taxRate` | `?float` |
| `taxChangedManually` | `?bool` |
| `title`, `description`, `reduction` | `?string` |
| `type` | `?InvoiceItemType` |
| `totalGross`, `totalNet`, `totalGrossUnreduced`, `totalNetUnreduced` | `?Money` |
| `customfield` | `?string` (Freies Zusatzfeld, siehe [Custom-Meta-Attribute](../concepts/custom-meta-attributes.md)) |

## Read-Modell: `ConfirmationComment`

`final readonly`.

| Property | Typ |
|---|---|
| `id` | `?int` |
| `confirmationId` | `int` |
| `comment` | `?string` |
| `created` | `?\DateTimeImmutable` |
| `userId` | `?int` |
| `actionkey` | `?ConfirmationCommentActionKey` |
| `actionkeyRaw` | `?string` — Roh-Wert bei unbekanntem `actionkey` |
| `public` | `?bool` |
| `byClient` | `?bool` |
| `emailId` | `?int` |
| `clientId` | `?int` |
| `customfield` | `?string` (Freies Zusatzfeld, siehe [Custom-Meta-Attribute](../concepts/custom-meta-attributes.md)) |

## Read-Modell: `ConfirmationTag`

`final readonly`. Felder: `id` (`?int`), `confirmationId` (`int`), `name` (`string`), `customfield` (`?string`).

## Read-Modell: `ConfirmationTagCloudEntry`

`final readonly`. Aggregat: `id` (`?int`), `name` (`string`), `count` (`int`).

## Read-Modell: `ConfirmationPdf`

`final class ConfirmationPdf` (nicht `readonly`).

| Property | Typ |
|---|---|
| `id` | `int` |
| `confirmationId` | `int` |
| `created` | `?\DateTimeImmutable` |
| `filename` | `string` |
| `mimeType` | `string` — Default `application/pdf` |
| `fileSize` | `int` |
| `base64file` | `string` |

`getBinary(): string` decodiert `base64file`.

## Verwendete Enums

- [`ConfirmationStatus`](../../src/Model/Enum/ConfirmationStatus.php): `DRAFT`, `OPEN`, `COMPLETED`, `CLEARED`, `CANCELED`. Nach `complete()` liefert Billomat inzwischen `COMPLETED`. Hat `label()` für deutsche Bezeichnung. Beachten: **kein** `WON`/`LOST` wie beim Angebot.
- [`ConfirmationCommentActionKey`](../../src/Model/Enum/ConfirmationCommentActionKey.php): `CREATE`, `EDIT`, `OPEN`, `COMPLETE`, `CANCEL`, `CLEAR`, `CHANGE_STATUS`, `EMAIL`, `MAIL`, `COMMENT` — schlanker als bei Offers (`WIN`/`LOSE` fehlen).
- [`InvoiceItemType`](../../src/Model/Enum/InvoiceItemType.php): `PRODUCT`, `SERVICE` — von Rechnungspositionen geerbt.
- [`InvoicePdfType`](../../src/Model/Enum/InvoicePdfType.php): `SIGNED` (`signed`), `PRINT` (`print`) — Wire-Werte kleingeschrieben.
- [`NetGross`](../../src/Model/Enum/NetGross.php): `NET`, `GROSS`, `SETTINGS`.

## Stolpersteine

- **Schlankerer Lebenszyklus als beim Angebot.** Es gibt kein `win()`/`lose()` — eine Auftragsbestätigung wird entweder als erledigt (`clear()`) markiert oder storniert (`cancel()`). Wer aus Gewohnheit `win()` aufrufen will, sucht den falschen Endpoint.
- **`update()` ändert keine Positionen.** Billomat ignoriert eingebettete `confirmation-items` beim PUT. Stets die `$billomat->confirmations->items` verwenden.
- **`delete()` nur im `DRAFT`.** Nach `complete()` ist nur noch `cancel()` möglich.
- **`ConfirmationItemCreateOptions` ist auch der Update-Typ.** Der Konstruktor verlangt `quantity` und `unitPrice` auch beim Update, an dem sich diese Werte gar nicht ändern sollen.
- **`array_is_list`-Normalisierung in `list()`.** Bei genau einem Treffer liefert Billomat `confirmation` als Objekt — das SDK packt das selbst in eine Liste.
- **`offerId`-Verknüpfung ist informativ.** Die Bestätigung wird nicht automatisch mit dem Angebots-Status synchronisiert — `OfferStatus::WON` muss man separat über `OffersApi::win()` setzen.
- **`tax_changed_manually` ist Pflicht für manuelle `taxRate`-Werte.** Ohne das Flag nimmt Billomat den Default des Artikels/Kunden.
- **`ConfirmationPdf` ist nicht `readonly`** — pragmatische Ausnahme, damit der Container Binärdaten halten kann.
- **`uploadSignature()` erwartet reines Base64**, kein `data:`-Prefix.

## End-to-End-Beispiel

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\ConfirmationCommentCreateOptions;
use Justpilot\Billomat\Api\ConfirmationCreateOptions;
use Justpilot\Billomat\Api\EmailOptions;
use Justpilot\Billomat\Api\ConfirmationItemCreateOptions;
use Justpilot\Billomat\Api\ConfirmationTagCreateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\Enum\InvoiceItemType;
use Justpilot\Billomat\Model\Money;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

// 1) Auftragsbestätigung aus Angebot anlegen
$opts = new ConfirmationCreateOptions(
    clientId: 12345,
    offerId: 678,
    title: 'Auftragsbestätigung Webhosting',
    date: new DateTimeImmutable('today'),
    intro: 'Wir bestätigen Ihren Auftrag wie folgt.',
);

$item = new ConfirmationItemCreateOptions(
    quantity: 12.0, unitPrice: Money::of('19.90'),
    title: 'Hosting Basic (12 Monate)',
    unit: 'Monat',
    type: InvoiceItemType::SERVICE,
);
$opts = $opts->withItem($item);

$conf = $billomat->confirmations->create($opts);
printf("AB #%d angelegt (DRAFT)\n", $conf->id);

// 2) Tag setzen
$billomat->confirmations->tags->create(
    new ConfirmationTagCreateOptions(confirmationId: $conf->id, name: 'auftrag-2026'),
);

// 3) Abschließen → OPEN, Nummer wird vergeben
$billomat->confirmations->complete($conf->id);

$opened = $billomat->confirmations->get($conf->id);
printf("Status: %s, Bestätigungsnummer: %s\n",
    $opened?->status?->label() ?? '?',
    $opened?->confirmationNumber ?? '?',
);

// 4) Versenden — Default-Vorlage
$email = new EmailOptions(to: ['kunde@example.com']);
$billomat->confirmations->email($conf->id, $email);

// 5) Internen Vermerk hinterlegen
$billomat->confirmations->comments->create(
    new ConfirmationCommentCreateOptions(
        confirmationId: $conf->id,
        comment: 'Auftrag abgewickelt — bereit für Rechnungsstellung.',
    ),
);

// 6) Sobald die Rechnung raus ist: Auftrag als erledigt markieren
$billomat->confirmations->clear($conf->id);

// 7) PDF herunterladen
$pdf = $billomat->confirmations->pdf($conf->id);
file_put_contents("ab-{$opened?->confirmationNumber}.pdf", $pdf->getBinary());
```
