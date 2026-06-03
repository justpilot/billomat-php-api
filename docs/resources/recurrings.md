<!-- Quelle: https://www.billomat.com/api/abo-rechnungen/ -->

# Recurrings (Abo-Rechnungen)

API-Wrapper für Abo-Rechnungen unter `/recurrings` und ihre drei Sub-Ressourcen (`/recurring-items`, `/recurring-tags`, `/recurring-email-receivers`).

## Zugriff

```php
$billomat->recurrings              // Abo-Rechnungen selbst
$billomat->recurringItems          // Positionen einer Abo-Rechnung
$billomat->recurringTags           // Schlagworte
$billomat->recurringEmailReceivers // E-Mail-Empfänger (TO/CC/BCC)
```

## Modell

Eine Abo-Rechnung beschreibt eine wiederkehrende Rechnungs-Vorlage: Was wird wem in welchem Rhythmus berechnet, und was passiert beim Lauf?

- **Rhythmus**: `cycle` + `cycleNumber` (z. B. `MONTHLY` × `2` → alle zwei Monate) plus `startDate`, `endDate`, optional `iterations`.
- **Aktion**: `action` legt fest, was Billomat bei jedem Lauf macht — von „nur Entwurf erzeugen“ bis „erzeugen, abschließen, per E-Mail versenden“.
- **Inhalt**: Positionen werden separat als `RecurringItem`s gepflegt.

Aus einem Recurring entstehen mit der Zeit „echte“ Rechnungen — diese referenzieren den ursprünglichen Recurring über `Invoice::$recurringId` (siehe [Invoices](invoices.md)).

## Endpunkt-Übersicht

### `/recurrings`

| Methode | HTTP | Pfad |
|---|---|---|
| `list($filters?)` | GET | `/recurrings` |
| `get($id)` | GET | `/recurrings/{id}` |
| `create($options)` | POST | `/recurrings` |
| `update($id, $options)` | PUT | `/recurrings/{id}` |
| `delete($id)` | DELETE | `/recurrings/{id}` |

### `/recurring-items`

| Methode | HTTP | Pfad |
|---|---|---|
| `listByRecurring($recurringId, $query?)` | GET | `/recurring-items?recurring_id={id}` |
| `get($id)` | GET | `/recurring-items/{id}` |
| `create($recurringId, $options)` | POST | `/recurring-items` |
| `update($id, $options)` | PUT | `/recurring-items/{id}` |
| `delete($id)` | DELETE | `/recurring-items/{id}` |

### `/recurring-tags`

| Methode | HTTP | Pfad |
|---|---|---|
| `listByRecurring($recurringId)` | GET | `/recurring-tags?recurring_id={id}` |
| `cloud()` | GET | `/recurring-tags` |
| `get($id)` | GET | `/recurring-tags/{id}` |
| `create($options)` | POST | `/recurring-tags` |
| `delete($id)` | DELETE | `/recurring-tags/{id}` |

### `/recurring-email-receivers`

| Methode | HTTP | Pfad |
|---|---|---|
| `listByRecurring($recurringId)` | GET | `/recurring-email-receivers?recurring_id={id}` |
| `get($id)` | GET | `/recurring-email-receivers/{id}` |
| `create($options)` | POST | `/recurring-email-receivers` |
| `delete($id)` | DELETE | `/recurring-email-receivers/{id}` |

## Recurrings

### `list(array $filters = []): list<Recurring>`

Filter laut Billomat-Doku: `client_id`, `contact_id`, `name`, `payment_type`, `cycle_number`, `cycle`, `label`, `intro`, `note`, `tags`, `article_id`. Array-Werte werden als `key[]=…` codiert.

```php
$monthly = $billomat->recurrings->list([
    'cycle' => 'MONTHLY',
    'order_by' => 'next_creation_date+ASC',
]);
```

### `get(int $id): ?Recurring`

Liefert `null` bei 404. Die Response enthält eingebettete `recurring-items` und die Tax-Aufschlüsselung — beide werden automatisch hydriert.

### `create(RecurringCreateOptions $options): Recurring`

Positionen können direkt im `create()`-Call mit `addItem()` mitgegeben werden — sie landen unter `recurring-items.recurring-item` im Payload.

```php
use Justpilot\Billomat\Api\RecurringCreateOptions;
use Justpilot\Billomat\Api\RecurringItemCreateOptions;
use Justpilot\Billomat\Model\Enum\RecurringAction;
use Justpilot\Billomat\Model\Enum\RecurringCycle;

$opts = new RecurringCreateOptions(clientId: 12345);
$opts->name = 'Hosting Basic';
$opts->cycle = RecurringCycle::MONTHLY;
$opts->cycleNumber = 1;
$opts->action = RecurringAction::EMAIL;
$opts->startDate = new DateTimeImmutable('2026-07-01');

$item = new RecurringItemCreateOptions(quantity: 1.0, unitPrice: 19.90);
$item->title = 'Webhosting Basic';
$opts->addItem($item);

$recurring = $billomat->recurrings->create($opts);
```

### `update(int $id, RecurringUpdateOptions $options): Recurring`

Schmaler Subset für Partial-Updates. Positionen sind hier laut Billomat-Doku **nicht** veränderbar — dafür die `RecurringItemsApi` nutzen. Die Klasse bietet daher kein `addItem()`.

### `delete(int $id): bool`

## Write-Modell: `RecurringCreateOptions`

Konstruktor: `new RecurringCreateOptions(int $clientId)` — `clientId` ist Pflicht.

### Identifikation & Stamm

| Property | Billomat-Feld | Typ |
|---|---|---|
| `clientId` | `client_id` | `int` (Pflicht) |
| `contactId` | `contact_id` | `?int` |
| `address` | `address` | `?string` |
| `numberPre` | `number_pre` | `?string` |
| `name` | `name` | `?string` — interner Name |
| `title` | `title` | `?string` |
| `label` | `label` | `?string` |
| `intro`, `note` | analog | `?string` |
| `reduction` | `reduction` | `?string` |
| `currencyCode` | `currency_code` | `?string` |
| `netGross` | `net_gross` | `?NetGross` |
| `quote` | `quote` | `?float` |
| `paymentTypes` | `payment_types` | `?string` (CSV) |
| `freeTextId` | `free_text_id` | `?int` |
| `templateId` | `template_id` | `?int` |

### Datumsangaben & Konditionen

| Property | Billomat-Feld | Typ |
|---|---|---|
| `supplyDate` | `supply_date` | `?\DateTimeImmutable` |
| `supplyDateType` | `supply_date_type` | `?SupplyDateType` |
| `dueDays` | `due_days` | `?int` |
| `discountRate` | `discount_rate` | `?int` |
| `discountDays` | `discount_days` | `?int` |

### Wiederholung

| Property | Billomat-Feld | Typ |
|---|---|---|
| `action` | `action` | `?RecurringAction` |
| `cycle` | `cycle` | `?RecurringCycle` |
| `cycleNumber` | `cycle_number` | `?int` |
| `hour` | `hour` | `?int` (0–23, Uhrzeit des Laufs) |
| `startDate` | `start_date` | `?\DateTimeImmutable` |
| `endDate` | `end_date` | `?\DateTimeImmutable` |
| `iterations` | `iterations` | `?int` — Begrenzung der Anzahl Läufe |

### E-Mail-Default (greift bei `action = EMAIL`)

| Property | Billomat-Feld | Typ |
|---|---|---|
| `emailSender` | `email_sender` | `?string` |
| `emailSubject` | `email_subject` | `?string` |
| `emailMessage` | `email_message` | `?string` |
| `emailTemplateId` | `email_template_id` | `?int` |

### Positionen

Positionen werden nicht über eine Property, sondern über `addItem()` angehängt:

```php
$opts->addItem(new RecurringItemCreateOptions(quantity: 1.0, unitPrice: 19.90));
```

`getItems(): list<RecurringItemCreateOptions>` listet die hinzugefügten Positionen. `toArray()` rendert sie in `recurring-items.recurring-item`.

## Write-Modell: `RecurringUpdateOptions`

Spiegelt `RecurringCreateOptions` ohne `clientId`-Pflicht (jetzt optional, falls Umhängen auf einen anderen Kunden gewünscht) und ohne Items-Helper. Felder, die nicht gesetzt werden, bleiben unberührt.

## Read-Modell: `Recurring`

`final readonly class Recurring`. Die wichtigsten Felder:

| Property | Typ |
|---|---|
| `id` | `?int` |
| `clientId` | `int` |
| `contactId`, `numberPre` | `?int`, `?string` |
| `created` | `?\DateTimeImmutable` |
| `address` | `?string` |
| `supplyDate`, `startDate`, `endDate` | `?\DateTimeImmutable` |
| `supplyDateType` | `?SupplyDateType` |
| `dueDays`, `discountRate`, `discountDays` | `?int`, `?float`, `?int` |
| `name`, `title`, `label`, `intro`, `note`, `reduction` | `?string` |
| `currencyCode`, `paymentTypes` | `?string` |
| `netGross` | `?NetGross` |
| `quote` | `?float` |
| `action`, `cycle` | `?RecurringAction`, `?RecurringCycle` |
| `cycleNumber`, `hour`, `iterations`, `counter` | `?int` |
| `lastCreationDate`, `nextCreationDate` | `?\DateTimeImmutable` |
| `totalGross`, `totalNet` | `?float` |
| `emailSender`, `emailSubject`, `emailMessage` | `?string` |
| `emailTemplateId`, `freeTextId`, `templateId` | `?int` |
| `taxes` | `list<array{name:string,rate:float,amount:float}>` |
| `items` | `list<RecurringItem>` |

`counter` zählt die bereits ausgeführten Läufe; `nextCreationDate` ist der nächste geplante Erzeugungs-Tag.

## Recurring Items

Strukturell identisch zu `InvoiceItem`, referenzieren aber `recurring_id` statt `invoice_id`.

### Methoden

```php
$items = $billomat->recurringItems->listByRecurring($recurring->id);

$item = $billomat->recurringItems->create(
    $recurring->id,
    new RecurringItemCreateOptions(quantity: 2.0, unitPrice: 49.90),
);

$billomat->recurringItems->update($item->id, $opts);
$billomat->recurringItems->delete($item->id);
```

`create()` injiziert `recurring_id` automatisch in den Payload — `RecurringItemCreateOptions` selbst hat dieses Feld nicht.

### Write-Modell: `RecurringItemCreateOptions`

Konstruktor: `new RecurringItemCreateOptions(float $quantity, float $unitPrice)`. Beide Werte sind Pflicht und werden auch dann mitgeschickt, wenn sie `0` sind.

| Property | Billomat-Feld | Typ |
|---|---|---|
| `quantity` | `quantity` | `float` (Pflicht) |
| `unitPrice` | `unit_price` | `float` (Pflicht) |
| `type` | `type` | `?InvoiceItemType` |
| `articleId` | `article_id` | `?int` |
| `title`, `description`, `unit` | analog | `?string` |
| `taxName`, `taxRate`, `taxChangedManually` | analog | `?string`, `?float`, `?bool` |
| `reduction` | `reduction` | `?string` |
| `position` | `position` | `?int` |

### Read-Modell: `RecurringItem`

`final readonly`. Felder analog zu `InvoiceItem`, mit `recurringId` statt `invoiceId`.

## Recurring Tags

Funktional identisch zu [Invoice Tags](invoice-tags.md), bezogen auf eine Abo-Rechnung statt auf eine Rechnung.

```php
use Justpilot\Billomat\Api\RecurringTagCreateOptions;

$tag = $billomat->recurringTags->create(
    new RecurringTagCreateOptions(recurringId: $recurring->id, name: 'auto-paid'),
);

$tags = $billomat->recurringTags->listByRecurring($recurring->id);
$cloud = $billomat->recurringTags->cloud(); // aggregiert, mit count
```

Read-Modelle: `RecurringTag` (Verknüpfung mit `recurringId`) und `RecurringTagCloudEntry` (aggregiert).

## Recurring Email Receivers

Pflegt die TO/CC/BCC-Liste für die automatische E-Mail-Versendung (`action = EMAIL`). Es gibt kein `update()` — Empfänger werden gelöscht und neu angelegt.

```php
use Justpilot\Billomat\Api\RecurringEmailReceiverCreateOptions;
use Justpilot\Billomat\Model\Enum\RecurringEmailReceiverType;

$billomat->recurringEmailReceivers->create(
    new RecurringEmailReceiverCreateOptions(
        recurringId: $recurring->id,
        type: RecurringEmailReceiverType::TO,
        address: 'kunde@example.com',
    ),
);

$billomat->recurringEmailReceivers->create(
    new RecurringEmailReceiverCreateOptions(
        recurringId: $recurring->id,
        type: RecurringEmailReceiverType::BCC,
        address: 'log@example.com',
    ),
);
```

### Write-Modell: `RecurringEmailReceiverCreateOptions`

Alle drei Werte sind Pflicht und werden über den Konstruktor gesetzt: `recurringId`, `type`, `address`.

### Read-Modell: `RecurringEmailReceiver`

`final readonly`. Felder: `id`, `recurringId`, `type` (`RecurringEmailReceiverType`), `address`.

## Verwendete Enums

- [`RecurringCycle`](../../src/Model/Enum/RecurringCycle.php): `DAILY`, `WEEKLY`, `MONTHLY`, `YEARLY`.
- [`RecurringAction`](../../src/Model/Enum/RecurringAction.php):
  - `CREATE` — erstellt nur eine Entwurfs-Rechnung,
  - `COMPLETE` — erstellt und schließt die Rechnung ab (PDF wird generiert),
  - `EMAIL` — erstellt, schließt ab und versendet per E-Mail (nutzt `recurring-email-receivers`),
  - `MAIL` — erstellt, schließt ab und versendet postalisch (Pixelletter, kostenpflichtig).
- [`RecurringEmailReceiverType`](../../src/Model/Enum/RecurringEmailReceiverType.php): `TO`, `CC`, `BCC` (Wire-Werte kleingeschrieben).

## Stolpersteine

- **Items nicht über `update()` ändern.** Billomat ignoriert eingebettete `recurring-items` beim PUT auf `/recurrings/{id}`. Stets die `RecurringItemsApi` verwenden.
- **`action = EMAIL` ohne Empfänger geht schief.** Wer `action` auf `EMAIL` setzt, muss mindestens einen `RecurringEmailReceiver` vom Typ `TO` angelegt haben.
- **`emailTemplateId`** zieht eine im Billomat-Account hinterlegte E-Mail-Vorlage; `emailSender`/`emailSubject`/`emailMessage` überschreiben einzelne Felder davon punktuell.
- **`cycle` und `cycleNumber` zusammen lesen.** `cycle=MONTHLY` allein heißt nicht „monatlich“ — es heißt „`cycleNumber` Monate“. Default ist 1, wenn nicht gesetzt.
- **`type` bei Email-Empfängern ist lowercase.** Der Enum-Wire-Wert ist `to`/`cc`/`bcc`, nicht `TO`/`CC`/`BCC`.
- **`hour`** beeinflusst lediglich die Uhrzeit am Erzeugungstag — das Datum kommt aus `cycle` + `cycleNumber`.

## End-to-End-Beispiel

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\RecurringCreateOptions;
use Justpilot\Billomat\Api\RecurringEmailReceiverCreateOptions;
use Justpilot\Billomat\Api\RecurringItemCreateOptions;
use Justpilot\Billomat\Api\RecurringTagCreateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\Enum\RecurringAction;
use Justpilot\Billomat\Model\Enum\RecurringCycle;
use Justpilot\Billomat\Model\Enum\RecurringEmailReceiverType;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

// 1) Abo anlegen — monatlich, automatischer E-Mail-Versand
$opts = new RecurringCreateOptions(clientId: 12345);
$opts->name = 'Hosting Basic';
$opts->title = 'Webhosting-Abo';
$opts->cycle = RecurringCycle::MONTHLY;
$opts->cycleNumber = 1;
$opts->action = RecurringAction::EMAIL;
$opts->startDate = new DateTimeImmutable('2026-07-01');
$opts->dueDays = 14;

$item = new RecurringItemCreateOptions(quantity: 1.0, unitPrice: 19.90);
$item->title = 'Webhosting Basic';
$opts->addItem($item);

$recurring = $billomat->recurrings->create($opts);
printf("Abo #%d angelegt, nächster Lauf: %s\n",
    $recurring->id,
    $recurring->nextCreationDate?->format('Y-m-d') ?? 'tbd',
);

// 2) E-Mail-Empfänger pflegen
$billomat->recurringEmailReceivers->create(
    new RecurringEmailReceiverCreateOptions(
        recurringId: $recurring->id,
        type: RecurringEmailReceiverType::TO,
        address: 'kunde@example.com',
    ),
);

$billomat->recurringEmailReceivers->create(
    new RecurringEmailReceiverCreateOptions(
        recurringId: $recurring->id,
        type: RecurringEmailReceiverType::BCC,
        address: 'buchhaltung@meinefirma.de',
    ),
);

// 3) Tag setzen, damit Filtern leichter fällt
$billomat->recurringTags->create(
    new RecurringTagCreateOptions(recurringId: $recurring->id, name: 'hosting'),
);

// 4) Position später anpassen — Preis-Update
foreach ($billomat->recurringItems->listByRecurring($recurring->id) as $current) {
    $update = new RecurringItemCreateOptions(quantity: 1.0, unitPrice: 24.90);
    $update->title = $current->title;
    $billomat->recurringItems->update($current->id, $update);
}
```
