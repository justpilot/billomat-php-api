<!-- Source: https://www.billomat.com/api/suche/ -->

# Search (Volltextsuche / full-text search)

API wrapper for the global full-text search at `/search`.

## Access

```php
$billomat->search
```

`Justpilot\Billomat\Api\SearchApi`.

## Overview

Billomat offers a full-text search across resources that returns slim hits (resource slug, ID, headline, subline). It is meant to navigate quickly to a record, not to return full records.

## Endpoints

| Method | HTTP | Path |
|---|---|---|
| `query($query, $extraFilters?)` | GET | `/search?query={query}` |

## Method

### `query(string $query, array $extraFilters = []): list<SearchResult>`

Runs the search.

```php
$hits = $billomat->search->query('Hans Wurst');

foreach ($hits as $hit) {
    echo "{$hit->resource}#{$hit->id}: {$hit->headline} — {$hit->subline}\n";
}
```

Pass further query parameters (e.g. `per_page`) via `$extraFilters`.

## Model `SearchResult`

`Justpilot\Billomat\Model\SearchResult` — `final readonly`.

| Field | Type | Description |
|---|---|---|
| `resource` | `string` | Resource slug, e.g. `"invoices"`, `"delivery-notes"`, `"reminders"` |
| `id` | `?int` | ID of the hit within the resource |
| `headline` | `?string` | Headline, usually the document number in square brackets (`[14709-003]`) |
| `subline` | `?string` | Subline, usually date and recipient |

Full records are not included. To get more, use `resource` + `id` to call the matching API:

```php
foreach ($billomat->search->query('Hans Wurst') as $hit) {
    $record = match ($hit->resource) {
        'invoices'        => $billomat->invoices->get((int) $hit->id),
        'delivery-notes'  => $billomat->deliveryNotes->get((int) $hit->id),
        // ...
        default           => null,
    };
}
```

## Pitfalls

- **`query` is required.** Without `query` Billomat returns an error. The wrapper always sends it.
- **Docs typo `sublineline`.** The HTML docs list the field as `sublineline`; the API actually returns `subline`, which is what the SDK reads.
- **`resource` is a slug.** `delivery-notes` (with a hyphen), not `deliveryNotes`. Map it to SDK APIs with a `match` as shown above.
- **Slim hits.** No date, status or amount fields. For filtered lists use `*Api::list($filters)`; the search is optimized for type-ahead.
- **Pagination is barely documented.** The spec only lists `query`. You can pass `page`/`per_page` via `$extraFilters` in case Billomat accepts them.

## End-to-end

```php
use Justpilot\Billomat\BillomatClient;

$billomat = BillomatClient::create(billomatId: 'mycompany', apiKey: 'secret');

foreach ($billomat->search->query('Müller', ['per_page' => 20]) as $hit) {
    printf("%-20s #%d  %s\n", $hit->resource, $hit->id ?? 0, $hit->headline ?? '');
}
```
