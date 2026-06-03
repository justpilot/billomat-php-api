<!-- Quelle: https://www.billomat.com/api/eingangsrechnungen/kategorien/ -->

# Incoming Categories (Eingangsrechnungs-Kategorien)

API-Wrapper für die read-only Liste der Eingangsrechnungs-Kategorien unter `/incoming-categories`. Kategorien sind Billomat-vordefinierte Buckets wie *Waren*, *Dienstleistungen* oder *Bewirtungskosten* und werden auf Eingangsrechnungen referenziert.

## Zugriff

```php
$billomat->incomingCategories
```

`Justpilot\Billomat\Api\IncomingCategoriesApi`.

## Endpunkt-Übersicht

| Methode | HTTP | Pfad |
|---|---|---|
| `list($filters?)` | GET | `/incoming-categories` |
| `listPage($filters?)` | GET | `/incoming-categories` |
| `iterateAll($filters?, $pageSize?)` | GET | `/incoming-categories` (mehrseitig) |
| `get($id)` | GET | `/incoming-categories/{id}` |

> Read-only. Billomat dokumentiert keine Mutations-Endpunkte für diese Ressource.

## Methoden

### `list(array $filters = []): list<IncomingCategory>`

```php
foreach ($billomat->incomingCategories->list() as $cat) {
    printf("[%s] %s\n", $cat->id, $cat->title ?? '');
}
```

Pagination via `page`/`per_page`; siehe [Konzept](../concepts/pagination-and-filtering.md).

### `get(string $id): ?IncomingCategory`

Beachte: Die ID ist ein **String**-Slug (z.B. `goods`, `services`), kein numerischer Schlüssel. Liefert `null` bei 404.

## Read-Modell: `IncomingCategory`

`final readonly class IncomingCategory`.

| Property | Billomat-Feld | Typ |
|---|---|---|
| `id` | `id` | `string` |
| `title` | `title` | `?string` |
| `description` | `description` | `?string` |

## Stolpersteine

- **String-ID.** Anders als die meisten Billomat-Ressourcen identifiziert Billomat Kategorien per Slug (`goods`, `services`, …) statt per Integer.
- **Single-Item-List-Quirk.** Bei nur einer Kategorie liefert Billomat ein Objekt statt einer Liste — `listResource()` normalisiert.

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

foreach ($billomat->incomingCategories->iterateAll() as $cat) {
    printf("[%s] %s — %s\n",
        $cat->id,
        $cat->title ?? '(ohne Titel)',
        $cat->description ?? '',
    );
}
```
