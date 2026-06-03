<!-- Quelle: https://www.billomat.com/api/waehrungen/ -->

# Currencies (Währungen)

Read-only API-Wrapper für die Billomat-Währungsliste — entspricht den Endpunkten unter `/currencies`.

## Zugriff

```php
$billomat->currencies
```

`Justpilot\Billomat\Api\CurrenciesApi`, intern angelegt in `BillomatClient::__construct()`.

## Endpunkt-Übersicht

| Methode | HTTP | Pfad |
|---|---|---|
| `list()` | GET | `/currencies` |
| `listPage()` | GET | `/currencies` (Seite mit Metadaten) |
| `iterateAll()` | GET | `/currencies` (lazy über alle Seiten) |

Einzelabruf per ID (`GET /currencies/{id}`) ist im SDK nicht implementiert; der `list()`-Endpunkt unterstützt den Filter `code` für ISO-Währungscodes.

## Methoden

### `list(): list<Currency>`

Listet alle Währungen.

```php
$currencies = $billomat->currencies->list();

foreach ($currencies as $currency) {
    echo $currency->code . ': ' . $currency->name . PHP_EOL;
}
```

Wer eine einzelne Währung sucht (z.B. `EUR`), kann die Liste clientseitig filtern oder den HTTP-Client direkt verwenden:

```php
$page = $billomat->currencies->listPage(['code' => 'EUR']);
$eur  = $page->items[0] ?? null;
```

**Exceptions:** `AuthenticationException`, `HttpException`.

### `listPage(array $filters = []): Page<Currency>`

Wie `list()`, mit Pagination-Metadaten (`page`, `per_page`, `total`).

### `iterateAll(array $filters = [], int $pageSize = 100): Generator<int, Currency>`

Lazy-Iteration über alle Seiten.

## Read-Modell: `Currency`

`final readonly class Justpilot\Billomat\Model\Currency`

| Property | Typ | Mapping |
|---|---|---|
| `code` | `string` | `code` (Fallback: `currency_code`) |
| `name` | `?string` | `name` |
| `symbol` | `?string` | `symbol` |

`Currency::fromArray($data)` hydratisiert aus dem Wire-Format; `Currency::toArray()` schreibt snake_case zurück.
