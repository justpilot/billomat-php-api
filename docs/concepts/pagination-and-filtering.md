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
foreach ($billomat->invoices->iterateAll(new InvoiceFilter(status: [InvoiceStatus::OPEN]), pageSize: 200) as $invoice) {
    // …
}
```

`iterateAll()` setzt `page` und `per_page` selbst; die Seitengröße kommt aus `pageSize`.

Siehe `AbstractApi::listResource()`, `listResourcePage()` und `iterateResource()` in `src/Api/AbstractApi.php`.

## Filterung

Die unterstützten Filterparameter sind pro Ressource in `docs/resources/<resource>.md` aufgeführt. Für die großen Ressourcen gibt es typisierte Filter-Klassen, generiert aus der Billomat-Doku: `ClientFilter`, `SupplierFilter`, `ArticleFilter`, `UserFilter`, `InvoiceFilter`, `OfferFilter`, `ConfirmationFilter`, `CreditNoteFilter`, `DeliveryNoteFilter`, `LetterFilter`, `ReminderFilter`, `IncomingFilter`, `RecurringFilter` und `TemplateFilter`.

```php
use Justpilot\Billomat\Api\InvoiceFilter;
use Justpilot\Billomat\Api\OrderBy;
use Justpilot\Billomat\Model\Enum\InvoiceStatus;

$invoices = $billomat->invoices->list(new InvoiceFilter(
    clientId: 42,
    status: [InvoiceStatus::OPEN, InvoiceStatus::OVERDUE],
    from: new DateTimeImmutable('2026-01-01'),
    orderBy: OrderBy::desc('date'),
    perPage: 50,
));
```

- Alle Felder sind optionale benannte Argumente; nicht gesetzte Felder (null bzw. leere Liste) fehlen im Query.
- Mehrfachwerte (`status`, `tags`, ID-Listen wie `ClientFilter::$invoiceId`) sind Listen und werden kommasepariert gesendet (`status=OPEN,OVERDUE`); Billomat verknüpft sie mit ODER.
- `from`/`to` sind `DateTimeImmutable` und gehen als `YYYY-MM-DD` raus; Status, Zahlarten und Intervalle sind Enums.
- Die Filter sind unveränderlich: `$filter->withStatus(InvoiceStatus::PAID)` oder `$filter->withPage(2)` liefert eine geänderte Kopie.
- Jeder Filter hat `orderBy`, `page`, `perPage` und `extra`. Über `extra` gehen weitere Query-Parameter mit, etwa [benutzerdefinierte Attribute](custom-meta-attributes.md); sie überschreiben gleichnamige Felder.

Ein Array funktioniert weiterhin überall, auch bei Ressourcen ohne Filter-Klasse:

```php
$billomat->clients->list(['name' => 'Acme', 'country_code' => 'DE']);
```

### Schnelles Zählen via HEAD

Wer nur wissen will, **wieviele** Treffer ein Filter ergibt, schickt einen `HEAD`-Request statt `GET` und liest den Response-Header `X-Total-Count`. Das spart die Übertragung der eigentlichen Datensätze.

Dieses SDK exponiert HEAD aktuell nicht direkt; bei Bedarf lässt sich `BillomatHttpClient::request('HEAD', …)` nutzen.

## Sortierung — `order_by`

Format: `<feld> <ASC|DESC>`, mehrere Sortierungen kommasepariert. Im SDK baut `OrderBy` den Wert:

```php
OrderBy::desc('date')->thenAsc('invoice_number'); // order_by=date+DESC,invoice_number+ASC
```

`OrderBy` prüft, dass der Feldname snake_case ist, und wirft sonst eine `InvalidArgumentException`.

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

Im SDK setzt du den Wert für alle Requests über `BillomatConfig::$language` bzw. `BillomatClient::create(language: 'en-gb')`; Standard ist `de-de`.

## Single-Resource lesen

Einzel-Ressourcen liest `get($id)`:

```php
$invoice = $billomat->invoices->get(42);        // null bei 404
$invoice = $billomat->invoices->getOrFail(42);  // NotFoundException bei 404
```

`getOrFail()` gibt es auf allen Ressourcen und Sub-Ressourcen; es spart den `null`-Check, wenn ein fehlender Datensatz ein Fehler ist.

`AbstractApi::getJsonOrNull()` schluckt `NotFoundException` und gibt `null` zurück. Andere Fehler werden zu typisierten Exceptions gemappt (siehe [Errors & Rate-Limits](errors-and-rate-limits.md)).
