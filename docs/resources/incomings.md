# Incomings (Eingangsbelege)

API-Wrapper für Eingangsrechnungen unter `/incomings` und ihre vier Sub-Ressourcen (`/incoming-comments`, `/incoming-payments`, `/incoming-tags`, `/incoming-property-values`). Definitionen der Custom-Properties liegen unter `/incoming-properties` (siehe `IncomingPropertiesApi`, hier nur am Rand dokumentiert).

## Zugriff

```php
$billomat->incomings              // Eingangsrechnungen selbst
$billomat->incomingComments       // Kommentar-Historie pro Beleg
$billomat->incomingPayments       // gezahlte Beträge
$billomat->incomingTags           // Schlagworte
$billomat->incomingPropertyValues // Werte für Custom-Properties
$billomat->incomingProperties     // Definitionen der Custom-Properties
```

## Modell

Ein Incoming bildet eine Lieferantenrechnung ab — also einen Beleg, den jemand anderes an die eigene Firma gestellt hat. Inhaltlich also das Gegenstück zu `Invoice`, aber strukturell deutlich schmaler: Billomat speichert hier keine Einzelpositionen, sondern nur die Summen (`totalGross`, `totalNet`).

- **Quelle**: Pflichtfeld `supplierId` zeigt auf einen [Supplier](suppliers.md).
- **Status**: `IncomingStatus` (`DRAFT`, `OPEN`, `OVERDUE`, `PAID`, `CANCELED`) — wird von Billomat aus Fälligkeit, Stornierung und Zahlungssumme abgeleitet, nicht direkt geschrieben.
- **PDF-Anhang**: optional über `upload()`. Alternativ kann ein bereits hochgeladenes [Inbox-Document](inbox-documents.md) als Quelle dienen.
- **Bezahlung**: über `IncomingPayment`-Einträge — die Summe der Zahlungen wandert in `paidAmount`, der Rest in `openAmount`.
- **Historie**: jeder Zustandswechsel (Anlage, Zahlung, Stornierung, Upload …) erzeugt automatisch einen `IncomingComment` mit passendem `actionkey`.

Lifecycle in der Praxis: `create()` legt einen Beleg im Status `OPEN` (oder `DRAFT`, abhängig von Pflichtfeldern) an. Über `incomingPayments->create(...)` mit `markIncomingAsPaid = true` wechselt er auf `PAID`, sobald die Summe stimmt. `cancel()` setzt ihn auf `CANCELED`, `uncancel()` rollt das zurück.

## Endpunkt-Übersicht

### `/incomings`

| Methode | HTTP | Pfad |
|---|---|---|
| `list($filters?)` | GET | `/incomings` |
| `get($id)` | GET | `/incomings/{id}` |
| `create($options)` | POST | `/incomings` |
| `update($id, $options)` | PUT | `/incomings/{id}` |
| `delete($id)` | DELETE | `/incomings/{id}` |
| `cancel($id)` | PUT | `/incomings/{id}/cancel` |
| `uncancel($id)` | PUT | `/incomings/{id}/uncancel` |
| `upload($id, $base64Pdf)` | PUT | `/incomings/{id}/upload` |

### `/incoming-comments`

| Methode | HTTP | Pfad |
|---|---|---|
| `listByIncoming($incomingId, $actionKeys?)` | GET | `/incoming-comments?incoming_id={id}` |
| `get($id)` | GET | `/incoming-comments/{id}` |
| `create($options)` | POST | `/incoming-comments` |
| `delete($id)` | DELETE | `/incoming-comments/{id}` |

### `/incoming-payments`

| Methode | HTTP | Pfad |
|---|---|---|
| `list($filters?)` | GET | `/incoming-payments` |
| `get($id)` | GET | `/incoming-payments/{id}` |
| `create($options)` | POST | `/incoming-payments` |
| `delete($id)` | DELETE | `/incoming-payments/{id}` |

### `/incoming-tags`

| Methode | HTTP | Pfad |
|---|---|---|
| `listByIncoming($incomingId)` | GET | `/incoming-tags?incoming_id={id}` |
| `cloud()` | GET | `/incoming-tags` |
| `get($id)` | GET | `/incoming-tags/{id}` |
| `create($options)` | POST | `/incoming-tags` |
| `delete($id)` | DELETE | `/incoming-tags/{id}` |

### `/incoming-property-values`

| Methode | HTTP | Pfad |
|---|---|---|
| `list($filters?)` | GET | `/incoming-property-values` |
| `get($id)` | GET | `/incoming-property-values/{id}` |
| `create($options)` | POST | `/incoming-property-values` |

## Methoden

### Incomings

#### `list(array $filters = []): list<Incoming>`

Filter laut Billomat-Doku: `supplier_id`, `incoming_number`, `status`, `from`, `to`, `label`, `intro`, `note`, `tags`. Mehrere Status werden als Array übergeben und automatisch als `status[]=OPEN&status[]=OVERDUE` codiert.

```php
$open = $billomat->incomings->list([
    'status' => ['OPEN', 'OVERDUE'],
    'order_by' => 'date+DESC',
]);
```

#### `get(int $id): ?Incoming`

Liefert `null` bei 404.

#### `create(IncomingCreateOptions $options): Incoming`

```php
use Justpilot\Billomat\Api\IncomingCreateOptions;

$opts = new IncomingCreateOptions(supplierId: 4711);
$opts->incomingNumber = 'LR-2026-0815';
$opts->date = new DateTimeImmutable('2026-05-30');
$opts->dueDate = new DateTimeImmutable('2026-06-29');
$opts->totalGross = 357.00;
$opts->totalNet = 300.00;
$opts->currencyCode = 'EUR';

$incoming = $billomat->incomings->create($opts);
```

#### `update(int $id, IncomingUpdateOptions $options): Incoming`

Partial-Update — nur gesetzte Felder werden übertragen.

#### `delete(int $id): bool`

#### `cancel(int $id): bool`

Setzt den Status auf `CANCELED`. Liefert `true` bei HTTP 200.

#### `uncancel(int $id): bool`

Rollt eine Stornierung zurück.

#### `upload(int $id, string $base64Pdf): bool`

Hängt ein PDF an den Beleg. Erwartet den **schon Base64-codierten** Inhalt; die Methode codiert nicht selbst.

```php
$billomat->incomings->upload(
    $incoming->id,
    base64_encode(file_get_contents('/tmp/rechnung.pdf')),
);
```

### Incoming Comments

```php
use Justpilot\Billomat\Api\IncomingCommentCreateOptions;
use Justpilot\Billomat\Model\Enum\IncomingCommentActionKey;

$billomat->incomingComments->create(
    new IncomingCommentCreateOptions(
        incomingId: $incoming->id,
        comment: 'Mahnung versendet',
    ),
);

// Filterung der Historie nach actionkey
$paymentEvents = $billomat->incomingComments->listByIncoming(
    $incoming->id,
    [IncomingCommentActionKey::PAYMENT],
);
```

`listByIncoming()` reicht die `actionkey`-Liste als CSV (`actionkey=PAYMENT,UPLOAD`) an Billomat weiter.

### Incoming Payments

```php
use Justpilot\Billomat\Api\IncomingPaymentCreateOptions;
use Justpilot\Billomat\Model\Enum\InvoicePaymentType;

$payment = new IncomingPaymentCreateOptions(
    incomingId: $incoming->id,
    amount: 357.00,
);
$payment->date = new DateTimeImmutable('2026-06-15');
$payment->type = InvoicePaymentType::BANK_TRANSFER;
$payment->markIncomingAsPaid = true;

$billomat->incomingPayments->create($payment);
```

`list()` filtert serverseitig u. a. nach `incoming_id`, `from`, `to`, `type`.

### Incoming Tags

```php
use Justpilot\Billomat\Api\IncomingTagCreateOptions;

$billomat->incomingTags->create(
    new IncomingTagCreateOptions(incomingId: $incoming->id, name: 'miete'),
);

$tags = $billomat->incomingTags->listByIncoming($incoming->id);
$cloud = $billomat->incomingTags->cloud(); // aggregiert, mit count pro Tag
```

`cloud()` liest den `tag`-Knoten (nicht `incoming-tag`!) und gibt `IncomingTagCloudEntry`-Objekte zurück.

### Incoming Property Values

```php
use Justpilot\Billomat\Api\IncomingPropertyValueCreateOptions;

$billomat->incomingPropertyValues->create(
    new IncomingPropertyValueCreateOptions(
        incomingId: $incoming->id,
        incomingPropertyId: 42,
        value: 'Kostenstelle Marketing',
    ),
);
```

Es gibt bewusst kein `update()` — Werte werden in Billomat per `create()` upgesertet (ein bestehender Wert für dieselbe `incomingPropertyId` wird überschrieben).

## Write-Modell: `IncomingCreateOptions`

Konstruktor: `new IncomingCreateOptions(int $supplierId)` — `supplierId` ist Pflicht.

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `supplierId` | `supplier_id` | `int` (Pflicht) | |
| `incomingNumber` | `incoming_number` | `?string` | Externe Belegnummer des Lieferanten |
| `date` | `date` | `?\DateTimeImmutable` | Belegdatum |
| `supplyDate` | `supply_date` | `?\DateTimeImmutable` | Leistungsdatum |
| `dueDate` | `due_date` | `?\DateTimeImmutable` | Fälligkeitsdatum |
| `dueDays` | `due_days` | `?int` | Alternative zu `dueDate` |
| `address` | `address` | `?string` | Rechnungsanschrift (Snapshot) |
| `label` | `label` | `?string` | |
| `intro`, `note` | analog | `?string` | |
| `totalGross` | `total_gross` | `?float` | |
| `totalNet` | `total_net` | `?float` | |
| `currencyCode` | `currency_code` | `?string` | ISO-4217, z. B. `EUR` |
| `quote` | `quote` | `?float` | Wechselkurs zur Hauswährung |

## Write-Modell: `IncomingUpdateOptions`

Spiegelt `IncomingCreateOptions`, aber `supplierId` ist hier `?int` (nicht im Konstruktor). Felder, die nicht gesetzt sind, bleiben unberührt.

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `supplierId` | `supplier_id` | `?int` | |
| übrige Felder | analog `IncomingCreateOptions` | gleiche Typen | |

## Write-Modell: `IncomingCommentCreateOptions`

Konstruktor: `new IncomingCommentCreateOptions(int $incomingId, string $comment)`.

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `incomingId` | `incoming_id` | `int` (Pflicht) | |
| `comment` | `comment` | `string` (Pflicht) | Freitext |
| `actionkey` | `actionkey` | `?IncomingCommentActionKey` | Default `COMMENT`; bei manuell gesetzten Events `EDIT`, `OPEN`, … |

## Write-Modell: `IncomingPaymentCreateOptions`

Konstruktor: `new IncomingPaymentCreateOptions(int $incomingId, float $amount)`. Beide Werte werden auch dann gesendet, wenn sie `0` sind. Zusätzlich wird `mark_incoming_as_paid` immer übertragen — auch bei `false` (als `0`).

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `incomingId` | `incoming_id` | `int` (Pflicht) | |
| `amount` | `amount` | `float` (Pflicht) | |
| `date` | `date` | `?\DateTimeImmutable` | Zahlungsdatum |
| `comment` | `comment` | `?string` | |
| `transactionPurpose` | `transaction_purpose` | `?string` | Verwendungszweck |
| `type` | `type` | `?InvoicePaymentType` | Geteilter Enum mit Invoices |
| `markIncomingAsPaid` | `mark_incoming_as_paid` | `bool` (Default `false`) | Wird als `1`/`0` serialisiert |

## Write-Modell: `IncomingTagCreateOptions`

Konstruktor: `new IncomingTagCreateOptions(int $incomingId, string $name)`. Keine optionalen Felder.

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `incomingId` | `incoming_id` | `int` (Pflicht) | |
| `name` | `name` | `string` (Pflicht) | |

## Write-Modell: `IncomingPropertyValueCreateOptions`

Konstruktor: `new IncomingPropertyValueCreateOptions(int $incomingId, int $incomingPropertyId, mixed $value)`. Alle drei Felder sind Pflicht.

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `incomingId` | `incoming_id` | `int` (Pflicht) | |
| `incomingPropertyId` | `incoming_property_id` | `int` (Pflicht) | Verweist auf `IncomingProperty::$id` |
| `value` | `value` | `mixed` (Pflicht) | Typ richtet sich nach `PropertyType` der Definition |

## Read-Modell: `Incoming`

`final readonly class Incoming`.

| Property | Typ |
|---|---|
| `id` | `?int` |
| `supplierId` | `?int` |
| `created` | `?\DateTimeImmutable` |
| `date`, `supplyDate`, `dueDate`, `paidAt` | `?\DateTimeImmutable` |
| `dueDays` | `?int` |
| `status` | `?IncomingStatus` |
| `incomingNumber`, `address`, `label`, `intro`, `note` | `?string` |
| `totalGross`, `totalNet`, `paidAmount`, `openAmount` | `?float` |
| `currencyCode` | `?string` |
| `quote` | `?float` |

`paidAmount` und `openAmount` werden serverseitig aus den Zahlungen berechnet.

## Read-Modell: `IncomingComment`

| Property | Typ |
|---|---|
| `id` | `?int` |
| `incomingId` | `int` |
| `comment` | `?string` |
| `created` | `?\DateTimeImmutable` |
| `userId` | `?int` |
| `actionkey` | `?IncomingCommentActionKey` |
| `actionkeyRaw` | `?string` |

`actionkeyRaw` hält den rohen API-Wert; `actionkey` ist `null`, falls Billomat einen unbekannten Wert liefert (`tryFrom`). Beim Debuggen hilft `actionkeyRaw`.

## Read-Modell: `IncomingPayment`

| Property | Typ |
|---|---|
| `id` | `?int` |
| `incomingId` | `int` |
| `date` | `?\DateTimeImmutable` |
| `amount` | `float` |
| `type` | `?InvoicePaymentType` |
| `comment` | `?string` |
| `created` | `?\DateTimeImmutable` |
| `userId` | `?int` |
| `transactionPurpose` | `?string` |

## Read-Modell: `IncomingTag`

| Property | Typ |
|---|---|
| `id` | `?int` |
| `incomingId` | `int` |
| `name` | `string` |

## Read-Modell: `IncomingTagCloudEntry`

| Property | Typ |
|---|---|
| `id` | `?int` |
| `name` | `string` |
| `count` | `int` |

## Read-Modell: `IncomingPropertyValue`

| Property | Typ |
|---|---|
| `id` | `?int` |
| `incomingId` | `int` |
| `incomingPropertyId` | `int` |
| `type` | `?string` |
| `name` | `?string` |
| `value` | `mixed` |

## Read-Modell: `IncomingProperty`

`final readonly class IncomingProperty`. Definition einer Custom-Property auf Eingangsrechnungen.

| Property | Typ |
|---|---|
| `id` | `?int` |
| `name` | `string` |
| `type` | `?PropertyType` |
| `defaultValue` | `?string` |
| `position` | `?int` |

## Verwendete Enums

- [`IncomingStatus`](../../src/Model/Enum/IncomingStatus.php): `DRAFT`, `OPEN`, `OVERDUE`, `PAID`, `CANCELED`. Wird vom Server gesetzt, nicht vom Client.
- [`IncomingCommentActionKey`](../../src/Model/Enum/IncomingCommentActionKey.php): `CREATE`, `EDIT`, `OPEN`, `COMPLETE`, `CANCEL`, `UNCANCEL`, `CHANGE_STATUS`, `PAYMENT`, `UPLOAD`, `COMMENT`. Die meisten Werte erzeugt Billomat automatisch — manuell sinnvoll ist üblicherweise nur `COMMENT`.
- [`InvoicePaymentType`](../../src/Model/Enum/InvoicePaymentType.php): geteilter Enum mit Invoices (`BANK_TRANSFER`, `CASH`, `PAYPAL`, …).
- [`PropertyType`](../../src/Model/Enum/PropertyType.php): Datentyp der Property-Definition (für `IncomingProperty`).

## Stolpersteine

- **`upload()` codiert nicht selbst.** Der String muss bereits Base64-codiert sein; `file_get_contents()` direkt zu übergeben liefert beim Server kaputte PDFs. Immer `base64_encode(file_get_contents(...))`.
- **Status ist read-only.** `IncomingStatus` lässt sich nicht über `IncomingCreateOptions`/`IncomingUpdateOptions` setzen — Billomat leitet ihn aus Fälligkeit, Stornierung und Zahlungssumme ab. Statuswechsel laufen ausschließlich über `cancel()`/`uncancel()` und das Setzen von `markIncomingAsPaid` bei Zahlungen.
- **`markIncomingAsPaid` wird immer übertragen.** Anders als die übrigen optionalen Felder filtert `IncomingPaymentCreateOptions::toArray()` den Wert nicht heraus — `false` landet als `mark_incoming_as_paid=0` im Payload. Für Teilzahlungen also nicht setzen.
- **`tag`-Knoten in `cloud()`**. Bei `GET /incoming-tags` ohne `incoming_id` liefert Billomat den Wurzelknoten `tag` (mit `count`), nicht `incoming-tag`. Die `IncomingTagsApi` unterscheidet beide Pfade intern — die Cloud-Einträge sind nicht dieselbe Form wie Einzel-Tags und werden in ein separates Read-Modell hydriert.
- **Keine Positionen.** Im Gegensatz zu `Invoice` gibt es keine `IncomingItem`-Ressource — Beträge werden ausschließlich als `totalGross`/`totalNet` summarisch gepflegt.
- **`actionkey` bei Kommentaren ist meist server-getrieben.** Wer einen Kommentar manuell anlegt, sollte `actionkey` weglassen — Billomat setzt dann `COMMENT`. Selbst gesetzte Werte wie `PAYMENT` werden zwar akzeptiert, die zugehörigen Effekte (z. B. Statusänderung) bleiben aber aus.
- **`IncomingPropertyValuesApi` ohne `update()`/`delete()`.** Werte werden per `create()` upgesertet; ein expliziter `update()`-Endpoint existiert in der API nicht.
- **`IncomingPropertiesApi::create()` nutzt `PropertyCreateOptions`** (geteilt mit Article/Client/Supplier-Properties), nicht eine eigene Klasse.

## End-to-End-Beispiel

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\IncomingCommentCreateOptions;
use Justpilot\Billomat\Api\IncomingCreateOptions;
use Justpilot\Billomat\Api\IncomingPaymentCreateOptions;
use Justpilot\Billomat\Api\IncomingTagCreateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\Enum\IncomingCommentActionKey;
use Justpilot\Billomat\Model\Enum\InvoicePaymentType;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

// 1) Eingangsrechnung anlegen
$opts = new IncomingCreateOptions(supplierId: 4711);
$opts->incomingNumber = 'LR-2026-0815';
$opts->date = new DateTimeImmutable('2026-05-30');
$opts->dueDate = new DateTimeImmutable('2026-06-29');
$opts->totalGross = 357.00;
$opts->totalNet = 300.00;
$opts->currencyCode = 'EUR';
$opts->label = 'Hosting Mai 2026';

$incoming = $billomat->incomings->create($opts);

// 2) PDF anhängen
$billomat->incomings->upload(
    $incoming->id,
    base64_encode((string) file_get_contents('/tmp/rechnung-mai-2026.pdf')),
);

// 3) Tag setzen, damit Filtern leichter fällt
$billomat->incomingTags->create(
    new IncomingTagCreateOptions(incomingId: $incoming->id, name: 'hosting'),
);

// 4) Zahlung verbuchen — markiert den Beleg als bezahlt
$billomat->incomingPayments->create(
    (function () use ($incoming): IncomingPaymentCreateOptions {
        $p = new IncomingPaymentCreateOptions(
            incomingId: $incoming->id,
            amount: 357.00,
        );
        $p->date = new DateTimeImmutable('2026-06-15');
        $p->type = InvoicePaymentType::BANK_TRANSFER;
        $p->transactionPurpose = 'RG LR-2026-0815';
        $p->markIncomingAsPaid = true;

        return $p;
    })(),
);

// 5) Eigenen Kommentar nachschieben
$billomat->incomingComments->create(
    new IncomingCommentCreateOptions(
        incomingId: $incoming->id,
        comment: 'Im DATEV-Export berücksichtigt.',
    ),
);

// 6) Historie inspizieren — nur Zahlungs-Events
$paymentLog = $billomat->incomingComments->listByIncoming(
    $incoming->id,
    [IncomingCommentActionKey::PAYMENT, IncomingCommentActionKey::CHANGE_STATUS],
);

foreach ($paymentLog as $entry) {
    printf("[%s] %s: %s\n",
        $entry->created?->format('Y-m-d H:i') ?? '?',
        $entry->actionkeyRaw ?? '-',
        $entry->comment ?? '',
    );
}

// 7) Aktuellen Stand prüfen
$fresh = $billomat->incomings->get($incoming->id);
printf("Status: %s, offen: %.2f %s\n",
    $fresh?->status?->label() ?? 'unbekannt',
    $fresh?->openAmount ?? 0.0,
    $fresh?->currencyCode ?? '',
);
```
