# Pagination

Billomat returns list endpoints (`/clients`, `/invoices`, …) page by page. The page metadata sits as `@page`/`@per_page`/`@total` next to the actual list entry in the outer envelope:

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

The SDK offers three levels, ordered by convenience:

| Method | When to use |
|---|---|
| `list($filters)` | You only need the first page (or pass `page`/`perPage` yourself). |
| `listPage($filters)` | You need pagination metadata for a classic UI ("page 1/12, 234 results"). |
| `iterateAll($filters, int $pageSize = 100)` | You want to walk through all entries of a filter without dealing with page indexes. |

For clients, suppliers, articles, users and all document types (invoices, offers, confirmations, credit notes, delivery notes, letters, reminders, incoming invoices, recurring invoices), `$filters` is the typed filter class (`ClientFilter`, `InvoiceFilter`, …) or an array (`ClientFilter|array`). `templates->list()` takes `TemplateFilter|array`. Sub-resources get their parent ID as the first argument: payments take `InvoicePaymentFilter`/`CreditNotePaymentFilter`/`IncomingPaymentFilter`, property values `ClientPropertyValueFilter`/`ArticlePropertyValueFilter`/`SupplierPropertyValueFilter`/`IncomingPropertyValueFilter`, and items, comments, tags, email receivers and property definitions the generic `SubResourceFilter` (only `orderBy`, `page`, `perPage`, `extra`). The other list APIs take an array.

`listPage()` and `iterateAll()` are available on these list APIs:

- top-level resources: `clients`, `suppliers`, `articles`, `users`, `invoices`, `offers`, `confirmations`, `creditNotes`, `deliveryNotes`, `reminders`, `letters`, `incomings`, `recurrings`, `inboxDocuments`, `activities`, `roles`,
- master data lookups: `countries`, `currencies`, `countryTaxes`, `units`, `emailTemplates`, `freeTexts`, `incomingCategories`,
- the attribute sub-resources `properties` (on `clients`, `articles`, `suppliers`, `incomings`, `users`) and `propertyValues` (on `clients`, `articles`, `suppliers`, `incomings`), e.g. `$billomat->clients->propertyValues->iterateAll($clientId)`,
- the document sub-resources `items`, `comments`, `tags` and `payments` (where the parent has them) and `recurrings->emailReceivers`, e.g. `$billomat->invoices->items->iterateAll($invoiceId, new SubResourceFilter(orderBy: OrderBy::asc('position')))`.

`contacts->list($clientId)` and `templates->list()` return a single list without pagination helpers.

## `iterateAll()` — auto-pagination

A lazy generator modeled on the Stripe SDK (`auto_paging_iter()`). It fetches page by page on demand and stops automatically once the last page is reached.

```php
use Justpilot\Billomat\Api\ClientFilter;
use Justpilot\Billomat\BillomatClient;

$billomat = BillomatClient::create(billomatId: 'mycompany', apiKey: '…');

foreach ($billomat->clients->iterateAll(new ClientFilter(countryCode: 'DE')) as $client) {
    echo $client->id, ' ', $client->name, PHP_EOL;
}
```

Filters are sent with every page request; `page`/`perPage` (or `page`/`per_page` in an array) are overwritten, because the SDK controls them.

### Tuning `pageSize`

The default is 100 (Billomat's maximum is 1000). Larger pages mean fewer round trips, smaller pages lower peak memory:

```php
use Justpilot\Billomat\Api\InvoiceFilter;

$filter = new InvoiceFilter(from: new DateTimeImmutable('2026-01-01'));

foreach ($billomat->invoices->iterateAll(filters: $filter, pageSize: 500) as $invoice) {
    // …
}
```

### Termination

The iterator stops in two cases:

1. **Preferred:** `PageInfo::hasNextPage()` is `false` because `page >= totalPages()` (Billomat sends `@total`).
2. **Fallback:** the endpoint sends no `@total` metadata and the current page contains fewer items than `pageSize`. In that case `total` in the `PageInfo` is `null` and `hasNextPage()` optimistically returns `true` — the iterator relies on the `count(items) < pageSize` check.

Both cases are safe and stop after the first request for empty endpoints.

## `listPage()` — one page with metadata

Same as `list()`, but also returns the page metadata as a `Page<Model>`:

```php
use Justpilot\Billomat\Api\ClientFilter;
use Justpilot\Billomat\Pagination\Page;

$result = $billomat->clients->listPage(new ClientFilter(page: 3, perPage: 50));
// $result instanceof Page

foreach ($result->items as $client) {
    // /** @var Client $client */
}

echo sprintf(
    'Page %d / %d — %d results in total',
    $result->info->page,
    $result->info->totalPages() ?? 1,
    $result->info->total ?? \count($result->items),
);
```

### `Page<T>` and `PageInfo`

Both value objects live in `Justpilot\Billomat\Pagination\`:

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
        public ?int $total, // null = unknown
    ) {}

    public function totalPages(): ?int;     // null if $total === null
    public function hasNextPage(): bool;    // true if $total === null (optimistic)
}
```

`PageInfo::$total` is `null` when the endpoint sends no `@total` metadata (e.g. some lookup endpoints). Then `totalPages()` is `null` as well; UI code has to handle that, e.g. with `?? '?'`.

## Paging manually with `list()`

`list()` takes `page`/`perPage` as well. To page manually (e.g. to resume after an abort):

```php
$pageTwo = $billomat->clients->list(new ClientFilter(page: 2, perPage: 50));

// equivalent as an array
$pageTwo = $billomat->clients->list(['page' => 2, 'per_page' => 50]);
```

For new code, `iterateAll()` is the recommended choice — less boilerplate and no off-by-one risk at the end of the list.
