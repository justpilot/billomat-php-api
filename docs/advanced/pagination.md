# Pagination

Billomat liefert List-Endpunkte (`/clients`, `/invoices`, …) seitenweise. Die Page-Metadaten stehen als `@page`/`@per_page`/`@total` neben dem eigentlichen Listeneintrag im äußeren Envelope:

```json
{
  "clients": {
    "@page": "1",
    "@per_page": "100",
    "@total": "234",
    "client": [ {…}, {…} ]
  }
}
```

Das SDK exponiert dazu drei Stufen, geordnet nach Bequemlichkeit:

| Methode | Wann nutzen? |
|---|---|
| `list($filters)` | Du brauchst nur die erste Seite (oder gibst `page`/`perPage` selbst mit). Verhalten wie vor v2.1. |
| `listPage($filters)` | Du brauchst Pagination-Metadaten für klassische UI ("Seite 1/12, 234 Treffer"). |
| `iterateAll($filters, int $pageSize = 100)` | Du willst über alle Einträge eines Filters laufen, ohne dich um Page-Indizes zu kümmern. |

`$filters` ist bei Kunden, Lieferanten, Artikeln, Benutzern und allen Dokumenttypen (Rechnungen, Angebote, Auftragsbestätigungen, Gutschriften, Lieferscheine, Briefe, Mahnungen, Eingangsrechnungen, Abo-Rechnungen) die typisierte Filterklasse (`ClientFilter`, `InvoiceFilter`, …) oder wie bisher ein Array (`ClientFilter|array`). `templates->list()` nimmt entsprechend `TemplateFilter|array`. Die übrigen List-APIs nehmen weiterhin ein Array.

`listPage()` und `iterateAll()` sind auf allen 26 List-APIs verfügbar: `clients`, `invoices`, `offers`, `confirmations`, `creditNotes`, `deliveryNotes`, `reminders`, `letters`, `articles`, `suppliers`, `incomings`, `inboxDocuments`, `recurrings`, `contacts` sowie auf den Lookup-/Property-APIs (`articleProperties`, `clientProperties`, `incomingProperties`, `supplierProperties` plus deren `*PropertyValues`-Geschwister) und den globalen Stammdaten (`countries`, `currencies`, `units`, `users`, `emailTemplates`, `freeTexts`).

## `iterateAll()` — Auto-Pagination

Lazy Generator nach dem Vorbild des Stripe-SDK (`auto_paging_iter()`). Holt seitenweise nach Bedarf und stoppt automatisch, sobald die letzte Seite erreicht ist.

```php
use Justpilot\Billomat\Api\ClientFilter;
use Justpilot\Billomat\BillomatClient;

$billomat = BillomatClient::create(billomatId: 'mycompany', apiKey: '…');

foreach ($billomat->clients->iterateAll(new ClientFilter(countryCode: 'DE')) as $client) {
    echo $client->id, ' ', $client->name, PHP_EOL;
}
```

Filter werden bei jeder Page-Anfrage mitgesendet; `page`/`perPage` (bzw. `page`/`per_page` im Array) darin werden überschrieben (das SDK steuert sie selbst).

### `pageSize` justieren

Default ist 100. Größere Pages reduzieren Round-Trips, kleinere Pages senken Peak-Memory:

```php
use Justpilot\Billomat\Api\InvoiceFilter;

$filter = new InvoiceFilter(from: new DateTimeImmutable('2026-01-01'));

foreach ($billomat->invoices->iterateAll(filters: $filter, pageSize: 500) as $invoice) {
    // …
}
```

### Terminierung

Der Iterator stoppt in zwei Fällen:

1. **Bevorzugt:** `PageInfo::hasNextPage()` ist `false`, weil `page * perPage >= total` (Billomat liefert `@total` mit).
2. **Fallback:** Der Endpunkt liefert keine `@total`-Metadaten und die aktuelle Seite enthält weniger Items als `pageSize`. In dem Fall ist `total` in der `PageInfo` `null`, und `hasNextPage()` gibt optimistisch `true` zurück — der Iterator verlässt sich auf den `count(items) < pageSize`-Check.

Beide Fälle sind sicher und brechen bei leeren Endpunkten nach der ersten Anfrage ab.

## `listPage()` — Eine Seite mit Metadaten

Identisch zu `list()`, gibt aber zusätzlich Page-Metadaten als `Page<Model>` zurück:

```php
use Justpilot\Billomat\Api\ClientFilter;
use Justpilot\Billomat\Pagination\Page;

$result = $billomat->clients->listPage(new ClientFilter(page: 3, perPage: 50));
// $result instanceof Page

foreach ($result->items as $client) {
    // /** @var Client $client */
}

echo sprintf(
    'Seite %d / %d — %d Treffer insgesamt',
    $result->info->page,
    $result->info->totalPages() ?? 1,
    $result->info->total ?? \count($result->items),
);
```

### `Page<T>` und `PageInfo`

Beide Wert-Objekte liegen unter `Justpilot\Billomat\Pagination\`:

```php
final readonly class Page // generic <T of object>
{
    /** @param list<T> $items */
    public function __construct(public array $items, public PageInfo $info) {}
}

final readonly class PageInfo
{
    public function __construct(
        public int $page,
        public int $perPage,
        public ?int $total, // null = unbekannt
    ) {}

    public function totalPages(): ?int;     // null, wenn $total === null
    public function hasNextPage(): bool;    // true, wenn $total === null (optimistisch)
}
```

`PageInfo::$total` ist `null`, wenn der Endpunkt keine `@total`-Metadaten liefert (z. B. einige Lookup-Endpunkte). In dem Fall ist auch `totalPages()` `null`; UI-Code muss diese Möglichkeit mit `?? '?'` o. ä. behandeln.

## Filter mit `list()` weiterhin möglich

`list()` bleibt unverändert. Wer manuell paginieren will (z. B. zur Wiederaufnahme nach Abbruch), kann das wie bisher:

```php
$pageTwo = $billomat->clients->list(new ClientFilter(page: 2, perPage: 50));

// gleichwertig als Array
$pageTwo = $billomat->clients->list(['page' => 2, 'per_page' => 50]);
```

Für neue Konsumenten ist `iterateAll()` aber die empfohlene Wahl — weniger Boilerplate, keine Off-by-one-Risiken am Seitenende.
