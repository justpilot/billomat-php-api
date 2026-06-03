<!-- Quelle: https://www.billomat.com/api/grundlagen/daten-lesen/ -->

# Pagination, Filterung, Sortierung, Sprache

Listen-Endpunkte (`GET /api/<resource>`) liefern paginierte Treffer und akzeptieren einen festen Satz von Steuer-Parametern. Dieses SDK kapselt das durchgängig über `list()`, `listPage()` und `iterateAll()` auf jeder `*Api`-Klasse.

## Pagination

| Parameter | Default | Maximum |
|---|---|---|
| `page` | `1` | — |
| `per_page` | `100` | `1000` |

Die Antwort einer Listen-Ressource enthält auf dem Root-Element drei Pagination-Felder:

- `page` — aktuelle Seite
- `per_page` — Einträge pro Seite
- `total` — Gesamtanzahl über alle Seiten hinweg

### Im SDK

- `list($filters)` — eine einzelne Seite (kein Auto-Paging).
- `listPage($filters)` — liefert `Page<T>` mit Items **und** `PageInfo` (`page`, `perPage`, `total`).
- `iterateAll($filters)` — `Generator<T>`, der über alle Seiten transparent durchläuft und intern `page` hochzählt.

```php
foreach ($billomat->invoices->iterateAll(['per_page' => 200]) as $invoice) {
    // …
}
```

Siehe `AbstractApi::listResource()`, `listResourcePage()` und `iterateResource()` in `src/Api/AbstractApi.php`.

## Filterung

Die unterstützten Filterparameter sind pro Ressource in `docs/resources/<resource>.md` und in den jeweiligen `*Api`-PHPDocs aufgeführt. Filter werden als Query-Parameter mitgegeben:

```php
$billomat->clients->list([
    'name' => 'Acme',
    'country_code' => 'DE',
]);
```

### Schnelles Zählen via HEAD

Wer nur wissen will, **wieviele** Treffer ein Filter ergibt, schickt einen `HEAD`-Request statt `GET` und liest den Response-Header `X-Total-Count`. Das spart die Übertragung der eigentlichen Datensätze.

Dieses SDK exponiert HEAD aktuell nicht direkt; bei Bedarf lässt sich `BillomatHttpClient::request('HEAD', …)` nutzen.

## Sortierung — `order_by`

Format: `<feld> <ASC|DESC>`, mehrere Sortierungen kommasepariert.

```
?order_by=date DESC,invoice_number ASC
```

### Quirk: `+` statt Space

Billomat erwartet den Trenner als **literales `+`**, nicht als `%20` oder `%2B`. `BillomatHttpClient::buildBillomatQuery()` baut den Querystring deshalb manuell:

- Array-Werte werden als `key[]=v1&key[]=v2` serialisiert.
- Nach `rawurlencode` werden `%2B`-Sequenzen wieder in `+` zurückübersetzt.

`http_build_query()` würde beides falsch machen.

## Sprache — `Accept-Language`

Einige Antworten enthalten lokalisierte Strings (z.B. der Activity-Feed). Über den HTTP-Header `Accept-Language` lässt sich das Gebietsschema steuern:

```
Accept-Language: de-de
Accept-Language: en-gb
```

Im SDK lässt sich das pro Request über `BillomatHttpClient`-Optionen setzen; pro-Resource-Helfer sind aktuell nicht vorgesehen.

## Single-Resource lesen

Einzel-Ressourcen liest `get($id)`:

```php
$invoice = $billomat->invoices->get(42); // null bei 404
```

`AbstractApi::getJsonOrNull()` schluckt `NotFoundException` und gibt `null` zurück. Andere Fehler werden zu typisierten Exceptions gemappt (siehe [Errors & Rate-Limits](errors-and-rate-limits.md)).
