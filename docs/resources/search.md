<!-- Quelle: https://www.billomat.com/api/suche/ -->

# Search (Volltextsuche)

API-Wrapper für die globale Volltextsuche unter `/search`.

## Zugriff

```php
$billomat->search
```

`Justpilot\Billomat\Api\SearchApi`.

## Überblick

Billomat bietet eine resource-übergreifende Volltextsuche, die schlanke Treffer-Datensätze liefert (Ressourcen-Slug, ID, Überschrift, Unterzeile). Sie ist gedacht, um den Nutzer schnell zu einem Datensatz zu navigieren — nicht, um vollständige Datensätze auszuliefern.

## Endpunkt-Übersicht

| Methode | HTTP | Pfad |
|---|---|---|
| `query($query, $extraFilters?)` | GET | `/search?query={query}` |

## Methode

### `query(string $query, array $extraFilters = []): list<SearchResult>`

Führt die Suche aus.

```php
$hits = $billomat->search->query('Hans Wurst');

foreach ($hits as $hit) {
    echo "{$hit->resource}#{$hit->id}: {$hit->headline} — {$hit->subline}\n";
}
```

Weitere Query-Parameter (z. B. `per_page`) lassen sich über `$extraFilters` mitgeben.

## Modell `SearchResult`

`Justpilot\Billomat\Model\SearchResult` — `final readonly`.

| Feld | Typ | Beschreibung |
|---|---|---|
| `resource` | `string` | Slug der Ressource, z. B. `"invoices"`, `"delivery-notes"`, `"reminders"` |
| `id` | `?int` | ID des Treffers innerhalb der Ressource |
| `headline` | `?string` | Überschrift, meist Belegnummer in eckigen Klammern (`[14709-003]`) |
| `subline` | `?string` | Unterzeile, üblicherweise Datum und Empfänger |

Vollständige Datensätze sind nicht enthalten — wer mehr braucht, navigiert über `resource` + `id` zur richtigen API:

```php
foreach ($billomat->search->query('Hans Wurst') as $hit) {
    $invoice = match ($hit->resource) {
        'invoices'        => $billomat->invoices->get((int) $hit->id),
        'delivery-notes'  => $billomat->deliveryNotes->get((int) $hit->id),
        // ...
        default           => null,
    };
}
```

## Stolpersteine

- **Pflicht-Parameter `query`.** Ohne `query` antwortet Billomat mit einem Fehler. Der Wrapper sorgt dafür, dass er gesetzt ist.
- **Doku-Tippfehler `sublineline`.** Die HTML-Doku führt das Feld als `sublineline`; tatsächlich liefert die API `subline`. Das SDK kennt beides, normalisiert es aber auf `subline`.
- **`resource` ist ein Slug.** `delivery-notes` (mit Bindestrich), nicht `deliveryNotes`. Beim Mapping auf SDK-APIs ist die `match`-Form oben das saubere Vorgehen.
- **Schmale Treffer.** Keine Datums-, Status-, oder Betrag-Felder. Für Listenfilter ist `*Api::list($filters)` der richtige Weg, die Suche ist auf Tipp-Geschwindigkeit optimiert.
- **Pagination dünn dokumentiert.** Die Spec nennt nur `query`. `page`/`per_page` lassen sich per `$extraFilters` mitgeben, falls Billomat sie akzeptiert.

## End-to-End

```php
use Justpilot\Billomat\BillomatClient;

$billomat = BillomatClient::create(billomatId: 'mycompany', apiKey: 'secret');

foreach ($billomat->search->query('Müller', ['per_page' => 20]) as $hit) {
    printf("%-20s #%d  %s\n", $hit->resource, $hit->id ?? 0, $hit->headline ?? '');
}
```
