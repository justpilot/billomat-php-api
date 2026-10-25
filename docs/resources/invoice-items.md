<!-- Quelle: https://www.billomat.com/api/rechnungen/positionen/ -->

# Invoice Items (Rechnungspositionen)

API-Wrapper für die Positionen einer Rechnung. Endpunkte unter `/invoice-items`.

## Zugriff

```php
$billomat->invoices->items
```

`Justpilot\Billomat\Api\ItemsApi` (`$billomat->invoices->items`).

## Endpunkt-Übersicht

| Methode | HTTP | Pfad |
|---|---|---|
| `list($invoiceId, $query?)` | GET | `/invoice-items?invoice_id={id}` |
| `get($id)` | GET | `/invoice-items/{id}` |
| `create($invoiceId, $options)` | POST | `/invoice-items` |
| `update($id, $options)` | PUT | `/invoice-items/{id}` |
| `delete($id)` | DELETE | `/invoice-items/{id}` |

## Methoden

### `list(int $invoiceId, array $query = []): list<InvoiceItem>`

Listet alle Positionen einer Rechnung. `invoice_id` wird automatisch als Filter gesetzt, weitere Filter (`page`, `per_page` …) können über `$query` mitgegeben werden.

```php
$items = $billomat->invoices->items->list(98765, [
    'per_page' => 100,
]);
```

### `get(int $id): ?InvoiceItem`

Liefert `null` bei 404.

### `create(int $invoiceId, InvoiceItemCreateOptions $options): InvoiceItem`

Hängt eine Position an eine bestehende Rechnung. `invoice_id` wird vom SDK in den Payload eingefügt.

```php
use Justpilot\Billomat\Api\InvoiceItemCreateOptions;
use Justpilot\Billomat\Model\Enum\ItemType;
use Justpilot\Billomat\Model\Money;

$item = new InvoiceItemCreateOptions(
    quantity: 3.0, unitPrice: Money::of('49.90'),
    title: 'Premium-Support (3 Monate)',
    type: ItemType::SERVICE,
    unit: 'Monat',
);

$created = $billomat->invoices->items->create($invoiceId, $item);
```

### `update(int $id, InvoiceItemCreateOptions $options): InvoiceItem`

Aktualisiert eine bestehende Position. Verwendet **dieselbe** Options-Klasse wie `create()` — eine eigene `UpdateOptions` gibt es nicht. `quantity` und `unitPrice` werden immer gesendet (sie sind Pflicht im Konstruktor), alle anderen `null`-Felder bleiben unangetastet.

### `delete(int $id): void`

Löscht eine Position. Fehler kommen als Exception.

## Write-Modell: `InvoiceItemCreateOptions`

Konstruktor: `new InvoiceItemCreateOptions(float $quantity, Money $unitPrice)`.

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `type` | `type` | `?ItemType` | `PRODUCT` oder `SERVICE` |
| `articleId` | `article_id` | `?int` | Falls verknüpft mit einem Artikel |
| `title` | `title` | `?string` | Bezeichnung der Position |
| `description` | `description` | `?string` | Mehrzeilige Detailbeschreibung |
| `quantity` | `quantity` | `float` | Pflicht (im Konstruktor) |
| `unitPrice` | `unit_price` | `Money` | Pflicht (im Konstruktor) |
| `unit` | `unit` | `?string` | Frei wählbar, z. B. `Stunde`, `Stück`, `Monat` |
| `taxName` | `tax_name` | `?string` | Bezeichnung des Steuersatzes, z. B. `MwSt` |
| `taxRate` | `tax_rate` | `?float` | Prozentwert, z. B. `19.0` |
| `taxChangedManually` | `tax_changed_manually` | `?bool` | Setzt Billomat, wenn der Steuersatz vom Default des Kunden/Artikels abweicht |
| `reduction` | `reduction` | `?string` | Rabatt absolut (`"5"`) oder relativ (`"10%"`) |
| `position` | `position` | `?int` | Sortierreihenfolge; beim Erstellen typischerweise automatisch vergeben |

`toArray()` filtert `null`-Werte heraus — **außer** `quantity` und `unit_price`, die immer mitgesendet werden.

## Read-Modell: `InvoiceItem`

`final readonly class InvoiceItem`.

| Property | Typ | Notes |
|---|---|---|
| `id` | `?int` | |
| `invoiceId` | `?int` | |
| `articleId` | `?int` | |
| `position` | `?int` | Sortierreihenfolge in der Rechnung |
| `unit` | `?string` | |
| `quantity` | `float` | |
| `unitPrice` | `Money` | |
| `taxName` | `?string` | |
| `taxRate` | `?float` | |
| `taxChangedManually` | `?bool` | |
| `title` | `?string` | |
| `description` | `?string` | |
| `reduction` | `?string` | |
| `type` | `?ItemType` | |
| `totalGross`, `totalNet` | `?Money` | Inkl. Rabatt |
| `totalGrossUnreduced`, `totalNetUnreduced` | `?Money` | Ohne Rabatt |
| `customfield` | `?string` | Freies Zusatzfeld, siehe [Custom-Meta-Attribute](../concepts/custom-meta-attributes.md) |

## Verwendete Enums

- [`ItemType`](../../src/Model/Enum/ItemType.php): `PRODUCT`, `SERVICE`.

## Stolpersteine

- **Kein separater `UpdateOptions`-Typ.** `update()` verwendet dieselbe Klasse wie `create()`. Praktisch heißt das: der Konstruktor verlangt `quantity` und `unitPrice`, auch wenn du sie nicht ändern willst. Du musst sie also entweder neu setzen oder die alten Werte aus dem Read-Modell übernehmen.
- **Steuersatz manuell ändern.** Wenn die Position vom Default-Steuersatz des Kunden/Artikels abweichen soll, setze `taxRate` **und** `taxChangedManually = true`. Lässt du das Flag weg, ignoriert Billomat oft den manuellen `taxRate`.
- **Reihenfolge.** Beim Anlegen ist `position` typischerweise irrelevant — Billomat hängt die Position ans Ende. Beim Update kannst du `position` setzen, um umzusortieren. Andere Positionen werden dabei nicht automatisch verschoben.
- **Positionen im `create()`-Call der Rechnung.** Wenn du eine Rechnung direkt mit `items: […]` anlegst, sind die Positionen Teil derselben POST-Anfrage — kein zweiter Call nötig. Siehe [Invoices](invoices.md).

## End-to-End-Beispiel

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\InvoiceCreateOptions;
use Justpilot\Billomat\Api\InvoiceItemCreateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\Enum\ItemType;
use Justpilot\Billomat\Model\Money;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

// Rechnung leer anlegen
$invoice = $billomat->invoices->create(new InvoiceCreateOptions(clientId: 12345));

// Erste Position via Items-API ergänzen
$first = new InvoiceItemCreateOptions(
    quantity: 10.0, unitPrice: Money::of('12.5'),
    title: 'Lizenzgebühr Modul A',
    type: ItemType::PRODUCT,
    unit: 'Lizenz',
);

$created = $billomat->invoices->items->create($invoice->id, $first);

// Zweite Position
$second = new InvoiceItemCreateOptions(
    quantity: 2.5, unitPrice: Money::of('110'),
    title: 'Anpassung & Support',
    type: ItemType::SERVICE,
    unit: 'Stunde',
);

$billomat->invoices->items->create($invoice->id, $second);

// Alle Positionen lesen
foreach ($billomat->invoices->items->list($invoice->id) as $item) {
    printf("- %s: %.2f x %s = %s\n", $item->title, $item->quantity, $item->unitPrice->format(), ($item->totalNet ?? Money::zero())->format());
}

// Erste Position aktualisieren (Preis senken) — quantity bleibt gleich
$update = new InvoiceItemCreateOptions(quantity: $created->quantity, unitPrice: Money::of('9.99'), title: $created->title);
$billomat->invoices->items->update($created->id, $update);
```
