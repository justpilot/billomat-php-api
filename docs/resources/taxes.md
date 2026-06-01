# Taxes (Steuersätze)

API-Wrapper für die Verwaltung von Steuersätzen unter `/taxes`. Steuersätze sind die globalen Account-Steuersätze (z. B. „MwSt 19 %“). Die pro Rechnungsposition tatsächlich angewendete Steuer liegt in der Position selbst (`InvoiceItemCreateOptions::$taxRate`).

## Zugriff

```php
$billomat->taxes
```

`Justpilot\Billomat\Api\TaxesApi`.

## Endpunkt-Übersicht

| Methode | HTTP | Pfad |
|---|---|---|
| `list($query?)` | GET | `/taxes` |
| `get($id)` | GET | `/taxes/{id}` |
| `create($options)` | POST | `/taxes` |
| `update($id, $options)` | PUT | `/taxes/{id}` |
| `delete($id)` | DELETE | `/taxes/{id}` |

## Methoden

### `list(array $query = []): list<TaxRate>`

```php
$rates = $billomat->taxes->list();

foreach ($rates as $rate) {
    printf("#%d %s — %.2f %% %s\n",
        $rate->id,
        $rate->name,
        $rate->rate,
        $rate->isDefault ? '(Default)' : '',
    );
}
```

`$query` erlaubt Pagination (`page`, `per_page`).

### `get(int $id): ?TaxRate`

Liefert `null` bei 404.

### `create(TaxRateCreateOptions $options): TaxRate`

```php
use Justpilot\Billomat\Api\TaxRateCreateOptions;

$opts = new TaxRateCreateOptions(name: 'Ermäßigt 7%', rate: 7.0, isDefault: false);
$created = $billomat->taxes->create($opts);
```

### `update(int $id, TaxRateCreateOptions $options): TaxRate`

Verwendet **dieselbe** Options-Klasse wie `create()`. Es gibt also kein Partial-Update — du sendest immer einen vollständigen Steuersatz.

### `delete(int $id): bool`

Löscht einen Steuersatz. Achtung: das schlägt fehl, wenn der Satz noch in Rechnungen referenziert wird.

## Write-Modell: `TaxRateCreateOptions`

Konstruktor: `new TaxRateCreateOptions(string $name, float $rate, bool $isDefault = false)`.

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `name` | `name` | `string` | Pflicht (z. B. `MwSt`) |
| `rate` | `rate` | `float` | Pflicht, Prozentwert (z. B. `19.0`) |
| `isDefault` | `is_default` | `bool` | Default `false`. Wird als `1`/`0` serialisiert. |

`toArray()` filtert hier nichts heraus — alle drei Felder sind immer gesetzt.

## Read-Modell: `TaxRate`

`final readonly class TaxRate`.

| Property | Typ | Notes |
|---|---|---|
| `id` | `?int` | |
| `accountId` | `?int` | Billomat-Account-ID, zu dem der Satz gehört |
| `name` | `string` | |
| `rate` | `float` | Prozentwert |
| `isDefault` | `bool` | |

## Verwendete Enums

Keine. Die Ressource ist enum-frei — `name` ist freitextlich, `rate` ein `float`.

## Stolpersteine

- **`isDefault = true` ist exklusiv.** Setzt du einen Steuersatz auf Default, verlieren andere automatisch das Flag. Das passiert serverseitig.
- **Löschen eines referenzierten Satzes scheitert.** Wenn `delete()` mit 422 zurückkommt, ist der Satz noch in Rechnungen verwendet. Lösung: Steuersatz inaktivieren (manuell im Billomat-Backend) statt löschen.
- **Single-Item-List-Quirk.** Bei nur einem Steuersatz im Account liefert Billomat ein Objekt statt einer Liste — `list()` normalisiert das.

## End-to-End-Beispiel

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\TaxRateCreateOptions;
use Justpilot\Billomat\BillomatClient;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

// Aktuelle Steuersätze
foreach ($billomat->taxes->list() as $rate) {
    printf("[#%d] %s — %.2f %%%s\n",
        $rate->id,
        $rate->name,
        $rate->rate,
        $rate->isDefault ? ' (Default)' : '',
    );
}

// Neuen Steuersatz anlegen
$new = $billomat->taxes->create(new TaxRateCreateOptions(
    name: 'Reverse-Charge',
    rate: 0.0,
    isDefault: false,
));
printf("Angelegt: #%d %s\n", $new->id, $new->name);

// Auf Default umstellen
$updated = $billomat->taxes->update($new->id, new TaxRateCreateOptions(
    name: $new->name,
    rate: $new->rate,
    isDefault: true,
));
var_dump($updated->isDefault); // true

// Löschen
$billomat->taxes->delete($new->id);
```
