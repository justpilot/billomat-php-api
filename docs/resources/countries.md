<!-- Quelle: https://www.billomat.com/api/laender/ -->

# Countries (Länder)

Read-only API-Wrapper für die Billomat-Länderliste — entspricht den Endpunkten unter `/countries`.

## Zugriff

```php
$billomat->countries
```

`Justpilot\Billomat\Api\CountriesApi`, intern angelegt in `BillomatClient::__construct()`.

## Endpunkt-Übersicht

| Methode | HTTP | Pfad |
|---|---|---|
| `list()` | GET | `/countries` |
| `listPage()` | GET | `/countries` (Seite mit Metadaten) |
| `iterateAll()` | GET | `/countries` (lazy über alle Seiten) |

Einzelabruf per ID (`GET /countries/{id}`) ist im SDK nicht implementiert; der `list()`-Endpunkt akzeptiert den Filter `code` und liefert das passende Land direkt.

## Methoden

### `list(): list<Country>`

Listet alle Länder.

```php
$countries = $billomat->countries->list();

foreach ($countries as $country) {
    echo $country->code . ': ' . ($country->nameDe ?? $country->name) . PHP_EOL;
}
```

Hinweis: Diese SDK-Variante akzeptiert (anders als `users->list()`) keine Filter — der zugrundeliegende Billomat-Endpunkt unterstützt allerdings den Query-Parameter `code` für ISO-3166-Alpha-2-Codes. Wer ein einzelnes Land sucht, kann `BillomatClient::getHttpClient()` direkt verwenden oder die Liste clientseitig filtern.

**Exceptions:** `AuthenticationException`, `HttpException`.

### `listPage(array $filters = []): Page<Country>`

Wie `list()`, mit Pagination-Metadaten (`page`, `per_page`, `total`).

### `iterateAll(array $filters = [], int $pageSize = 100): Generator<int, Country>`

Lazy-Iteration über alle Seiten.

## Read-Modell: `Country`

`final readonly class Justpilot\Billomat\Model\Country`

| Property | Typ | Mapping |
|---|---|---|
| `code` | `string` | `code` (Fallback: `country_code`) |
| `name` | `?string` | `name` |
| `nameDe` | `?string` | `name_de` |
| `eu` | `?bool` | `eu` |

`Country::fromArray($data)` hydratisiert aus dem Wire-Format; `Country::toArray()` schreibt snake_case zurück.
