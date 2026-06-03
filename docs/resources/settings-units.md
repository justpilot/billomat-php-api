<!-- Quelle: https://www.billomat.com/api/einstellungen/einheiten/ -->

# Units (Einheiten)

API-Wrapper für die globalen Einheiten unter `/units` (z.B. „Stunde", „Stück", „Paletten"). Einheiten werden in Artikel- und Rechnungspositionen referenziert.

## Zugriff

```php
$billomat->units
```

`Justpilot\Billomat\Api\UnitsApi`.

## Endpunkt-Übersicht

| Methode | HTTP | Pfad |
|---|---|---|
| `list($filters?)` | GET | `/units` |
| `listPage($filters?)` | GET | `/units` |
| `iterateAll($filters?, $pageSize?)` | GET | `/units` (mehrseitig) |
| `get($id)` | GET | `/units/{id}` |

> Die API ist **read-only** im SDK. Anlegen, Bearbeiten und Löschen werden von Billomat unterstützt, sind aber nicht als SDK-Methoden exponiert — wer das braucht, kann `BillomatHttpClient` direkt nutzen oder eine `UnitCreateOptions`-Klasse nachreichen.

## Methoden

### `list(array $filters = []): list<Unit>`

```php
foreach ($billomat->units->list() as $unit) {
    printf("#%d %s\n", $unit->id, $unit->name);
}
```

Filterparameter:

| Parameter | Beschreibung |
|---|---|
| `name` | Teilstring-Suche auf der Bezeichnung; case-insensitive |
| `page`, `per_page` | Pagination — siehe [Konzept](../concepts/pagination-and-filtering.md) |

### `listPage(array $filters = []): Page<Unit>`

Eine einzelne Seite mit `PageInfo` (`page`, `perPage`, `total`).

### `iterateAll(array $filters = [], int $pageSize = 100): Generator<Unit>`

Lazy-Iteration über alle Seiten.

### `get(int $id): ?Unit`

Liefert `null` bei 404.

## Read-Modell: `Unit`

`final readonly class Unit`.

| Property | Billomat-Feld | Typ |
|---|---|---|
| `id` | `id` | `?int` |
| `name` | `name` | `string` |

> Die Billomat-Antwort enthält zusätzlich ein `created` (Zeitstempel der Anlage). Das SDK exponiert es aktuell nicht — bei Bedarf in `Unit` ergänzen.

## Stolpersteine

- **Single-Item-List-Quirk.** Bei nur einer Einheit im Account liefert Billomat ein Objekt statt einer Liste — `list()` normalisiert das in `UnitsApi::list()`.
- **Löschen scheitert bei Verwendung.** `DELETE /units/{id}` schlägt bei Billomat fehl, sobald die Einheit noch Artikeln zugeordnet ist (im SDK aktuell nicht relevant, da kein `delete()`).

## End-to-End-Beispiel

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\BillomatClient;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

// Alle Einheiten
foreach ($billomat->units->iterateAll() as $unit) {
    printf("[#%d] %s\n", $unit->id, $unit->name);
}

// Suche per Filter
$matches = $billomat->units->list(['name' => 'stunde']);
printf("%d Treffer für 'stunde'\n", \count($matches));
```
