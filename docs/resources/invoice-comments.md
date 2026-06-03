<!-- Quelle: https://www.billomat.com/api/rechnungen/kommentare/ -->

# Invoice Comments (Rechnungskommentare)

API-Wrapper für Kommentare an Rechnungen unter `/invoice-comments`.

## Zugriff

```php
$billomat->invoiceComments
```

`Justpilot\Billomat\Api\InvoiceCommentsApi`.

## Überblick

Billomat protokolliert für jede Rechnung einen Strom von Kommentaren — teils system-getrieben (Statuswechsel, Zahlungseingänge, Mailversand), teils manuell durch User. Beide Sorten landen in derselben Ressource und unterscheiden sich nur über das Feld `actionkey`:

- **System-Kommentare** haben `actionkey` gesetzt (`COMPLETE`, `PAYMENT`, `EMAIL`, …) und `user_id = null`.
- **Manuelle Kommentare** haben typischerweise kein `actionkey` und eine konkrete `user_id`.

## Endpunkt-Übersicht

| Methode | HTTP | Pfad |
|---|---|---|
| `listByInvoice($invoiceId, $actionKeys?)` | GET | `/invoice-comments?invoice_id={id}` |
| `get($id)` | GET | `/invoice-comments/{id}` |
| `create($options)` | POST | `/invoice-comments` |
| `delete($id)` | DELETE | `/invoice-comments/{id}` |

Es gibt bewusst keine `list()` ohne `invoice_id` — Billomat verlangt den Filter zwingend.

## Methoden

### `listByInvoice(int $invoiceId, ?array $actionKeys = null): list<InvoiceComment>`

Listet alle Kommentare einer Rechnung. Optional kann auf bestimmte ActionKey-Typen gefiltert werden; die Liste wird als CSV an Billomat geschickt.

```php
use Justpilot\Billomat\Model\Enum\InvoiceCommentActionKey;

// Alle Kommentare
$all = $billomat->invoiceComments->listByInvoice(98765);

// Nur Zahlungs- und Mahnungseinträge
$financial = $billomat->invoiceComments->listByInvoice(
    98765,
    [InvoiceCommentActionKey::PAYMENT, InvoiceCommentActionKey::DUNNING],
);
```

### `get(int $id): ?InvoiceComment`

Liefert `null` bei 404.

### `create(InvoiceCommentCreateOptions $options): InvoiceComment`

```php
use Justpilot\Billomat\Api\InvoiceCommentCreateOptions;

$comment = $billomat->invoiceComments->create(
    new InvoiceCommentCreateOptions(
        invoiceId: 98765,
        comment: 'Kunde meldete sich zur Klärung — Rückruf vereinbart 2026-06-05.',
    ),
);
```

`actionkey` ist optional und sollte für manuelle Kommentare leer gelassen werden — Billomat befüllt das Feld bei systemischen Aktionen automatisch.

### `delete(int $id): bool`

## Write-Modell: `InvoiceCommentCreateOptions`

Konstruktor: `new InvoiceCommentCreateOptions(int $invoiceId, string $comment)`.

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `invoiceId` | `invoice_id` | `int` | Pflicht (Konstruktor) |
| `comment` | `comment` | `string` | Pflicht (Konstruktor) |
| `actionkey` | `actionkey` | `?InvoiceCommentActionKey` | Selten manuell zu setzen |

`toArray()` filtert `null`-Werte heraus.

## Read-Modell: `InvoiceComment`

`final readonly class InvoiceComment`.

| Property | Typ | Notes |
|---|---|---|
| `id` | `?int` | |
| `invoiceId` | `int` | |
| `comment` | `?string` | |
| `created` | `?\DateTimeImmutable` | |
| `userId` | `?int` | `null` bei System-Kommentaren |
| `actionkey` | `?InvoiceCommentActionKey` | `null` bei unbekanntem oder fehlendem Wert |
| `actionkeyRaw` | `?string` | Originaler API-String, auch wenn das Enum den Wert nicht kennt |

## Verwendetes Enum: `InvoiceCommentActionKey`

[`InvoiceCommentActionKey`](../../src/Model/Enum/InvoiceCommentActionKey.php) deckt die im API-Output beobachteten ActionKeys ab:

| Case | Wire-Wert |
|---|---|
| `CREATE` | `CREATE` |
| `EDIT` | `EDIT` |
| `OPEN` | `OPEN` |
| `COMPLETE` | `COMPLETE` |
| `CANCEL` | `CANCEL` |
| `UNCANCEL` | `UNCANCEL` |
| `CHANGE_STATUS` | `CHANGE_STATUS` |
| `PAYMENT` | `PAYMENT` |
| `EMAIL` | `EMAIL` |
| `MAIL` | `MAIL` |
| `DUNNING` | `DUNNING` |
| `COMMENT` | `COMMENT` |

Billomat dokumentiert keine geschlossene Liste — falls künftig neue Werte auftauchen, parst das SDK `actionkey` zu `null` und füllt parallel `actionkeyRaw` mit dem Originalstring. Es wird also nichts „verschluckt“.

## Stolpersteine

- **`invoice_id` ist Pflicht.** Ohne den Filter antwortet Billomat mit 400 — `listByInvoice()` setzt den Filter automatisch.
- **System-Kommentare nicht überschreiben.** `actionkey` ist primär ein API-Output. Wer dort manuell einen Wert wie `PAYMENT` setzt, kann die Aktivitätsansicht in der Billomat-UI verfälschen.
- **`delete()` löscht auch System-Kommentare.** Vorsicht beim Aufräumen: System-Einträge sind Teil des Audit-Trails.

## End-to-End-Beispiel

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\InvoiceCommentCreateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\Enum\InvoiceCommentActionKey;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

$invoiceId = 98765;

// 1) Manuellen Kommentar anhängen
$billomat->invoiceComments->create(
    new InvoiceCommentCreateOptions(
        invoiceId: $invoiceId,
        comment: 'Kunde wünscht Verlängerung um 14 Tage.',
    ),
);

// 2) Komplette Historie anzeigen
foreach ($billomat->invoiceComments->listByInvoice($invoiceId) as $c) {
    printf(
        "[%s] %s — %s\n",
        $c->created?->format('Y-m-d H:i') ?? '???',
        $c->actionkey?->value ?? ($c->actionkeyRaw ?? 'manual'),
        $c->comment ?? '',
    );
}

// 3) Nur Mahn-Historie ausfiltern
$dunning = $billomat->invoiceComments->listByInvoice(
    $invoiceId,
    [InvoiceCommentActionKey::DUNNING],
);
printf("Mahnungen: %d\n", count($dunning));
```
