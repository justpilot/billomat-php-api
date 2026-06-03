<!-- Quelle: https://www.billomat.com/api/einstellungen/steuerfreie-laender/ -->

# Country Taxes (Steuerfreie Länder)

API-Wrapper für die Liste steuerfreier Länder unter `/country-taxes`. Für jeden eingetragenen Ländercode (ISO 3166 Alpha-2) berechnet Billomat keine Mehrwertsteuer — relevant für Rechnungen ins Ausland.

> **Endpunktname beachten.** Die Billomat-Doku trägt den Titel *Steuerfreie Länder*, der HTTP-Pfad heisst aber `/country-taxes` (nicht `/tax-free-countries`). Die SDK-Klasse spiegelt das wider: `CountryTaxesApi`.

## Zugriff

```php
$billomat->countryTaxes
```

`Justpilot\Billomat\Api\CountryTaxesApi`.

## Endpunkt-Übersicht

| Methode | HTTP | Pfad |
|---|---|---|
| `list($filters?)` | GET | `/country-taxes` |
| `listPage($filters?)` | GET | `/country-taxes` |
| `iterateAll($filters?, $pageSize?)` | GET | `/country-taxes` (mehrseitig) |
| `get($id)` | GET | `/country-taxes/{id}` |

> Read-only im SDK. Anlegen/Bearbeiten/Löschen sind bei Billomat verfügbar (`POST`/`PUT`/`DELETE /country-taxes`), aber nicht als SDK-Methoden exponiert.

## Methoden

### `list(array $filters = []): list<CountryTax>`

```php
foreach ($billomat->countryTaxes->list() as $row) {
    printf("#%d %s\n", $row->id ?? 0, $row->countryCode ?? '');
}
```

Filter:

| Parameter | Beschreibung |
|---|---|
| `country` | ISO 3166 Alpha-2 Ländercode, z.B. `CH`, `US` |
| `page`, `per_page` | Pagination — siehe [Konzept](../concepts/pagination-and-filtering.md) |

### `get(int $id): ?CountryTax`

Liefert `null` bei 404.

## Read-Modell: `CountryTax`

`final readonly class CountryTax`.

| Property | Billomat-Feld | Typ |
|---|---|---|
| `id` | `id` | `?int` |
| `countryCode` | `country_code` | `?string` |

## Stolpersteine

- **Kein Steuersatz im Modell.** Anders als `/taxes` (siehe [taxes.md](taxes.md)) hält dieser Endpunkt nur die Ländercode-Liste vor; der Steuersatz für diese Länder ist implizit 0 %.
- **Single-Item-List-Quirk.** Bei nur einem Eintrag liefert Billomat ein Objekt statt einer Liste — `listResource()` normalisiert.

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

$taxFree = array_map(
    static fn ($row) => $row->countryCode,
    $billomat->countryTaxes->list(),
);

printf("Steuerfrei: %s\n", implode(', ', array_filter($taxFree)));
```
