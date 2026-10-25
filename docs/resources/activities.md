<!-- Source: https://www.billomat.com/api/aktivitaeten/ -->

# Activities (Aktivitäten / activity feed)

API wrapper for the activity feed at `/activity-feed`.

## Access

```php
$billomat->activities
```

`Justpilot\Billomat\Api\ActivitiesApi`.

## Overview

Billomat logs every change to your records in a central feed: status changes on invoices, e-mails sent, documents created, payments booked. The feed is read-only; activities are only created as a side effect of other operations.

## Endpoints

| Method | HTTP | Path |
|---|---|---|
| `list($filters?)` | GET | `/activity-feed` |
| `listPage($filters?)` | GET | `/activity-feed?page=N&per_page=M` |
| `iterateAll($filters?, $pageSize?)` | GET (lazy) | `/activity-feed?page=…` |

Pagination uses `page` and `per_page`, as everywhere in the SDK.

## Methods

### `list(array $filters = []): list<Activity>`

Fetches one page. Filters are sent as query parameters.

```php
$activities = $billomat->activities->list([
    'page' => 1,
    'per_page' => 50,
]);

foreach ($activities as $a) {
    echo "[{$a->date?->format('c')}] {$a->resource}#{$a->id}: {$a->title}\n";
}
```

### `listPage(array $filters = []): Page<Activity>`

One page plus `PageInfo` (`page`, `perPage`, `total`) in `$page->info`.

### `iterateAll(array $filters = [], int $pageSize = 100): Generator<int, Activity>`

Iterates lazily over the whole feed, `pageSize` items per request. Stops after the last page.

```php
foreach ($billomat->activities->iterateAll() as $activity) {
    if ($activity->isSystemActivity()) {
        continue;
    }
    // ... user activities only
}
```

## Model `Activity`

`Justpilot\Billomat\Model\Activity` — `final readonly`.

| Field | Type | Description |
|---|---|---|
| `resource` | `string` | Resource slug (`invoices`, `delivery-notes`, `clients`, …) |
| `id` | `?int` | ID of the affected record within the resource |
| `date` | `?DateTimeImmutable` | When the activity happened |
| `title` | `?string` | Headline, e.g. `"Rechnung RE123"` |
| `text` | `?string` | Free text, e.g. `"Status geändert von Entwurf nach offen."` |
| `userId` | `?int` | User who triggered it; `null` for system activities |

Helper `isSystemActivity(): bool` returns `true` when `userId` is `null`.

## Pitfalls

- **`user_id` can be an empty string.** For system activities Billomat sends `<user_id></user_id>`, which is `""` in JSON. `Activity::fromArray()` normalizes it to `null` via `ScalarCaster::toIntOrNull()`.
- **Slug, not class name.** `resource` is the URL path slug, i.e. `"delivery-notes"` (with a hyphen), not `"deliveryNotes"`.
- **Read-only, no `get()`.** There is no single-record endpoint `/activity-feed/{id}`. To load the record, query its resource directly (`$billomat->invoices->get($a->id)`).
- **The schema can drift.** Billomat adds new `resource` values when it adds features, which is why the field is a plain string and not an enum.
- **Server-side filters.** The docs don't list any filter parameters, but `page`/`per_page` work. Filter by resource on the client side if needed.
- **Title and text are localized.** Billomat renders them in the language of the `Accept-Language` header, which the SDK takes from `BillomatConfig::$language` (default `de-de`; e.g. `BillomatClient::create(..., language: 'en-gb')`).

## End-to-end

```php
use Justpilot\Billomat\BillomatClient;

$billomat = BillomatClient::create(billomatId: 'mycompany', apiKey: 'secret');

// Collect all user activities on invoices
foreach ($billomat->activities->iterateAll() as $activity) {
    if ($activity->resource !== 'invoices' || $activity->isSystemActivity()) {
        continue;
    }
    // ... audit log
}
```
