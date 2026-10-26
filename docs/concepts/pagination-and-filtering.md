<!-- Source: https://www.billomat.com/api/grundlagen/daten-lesen/ -->

# Pagination, filtering, sorting, language

List endpoints (`GET /api/<resource>`) return paginated results and accept a fixed set of control parameters. This SDK wraps them consistently in `list()`, `listPage()` and `iterateAll()` on the `*Api` classes.

## Pagination

| Parameter | Default | Maximum |
|---|---|---|
| `page` | `1` | — |
| `per_page` | `100` | `1000` |

The response of a list resource carries three pagination fields on the root element:

- `page` — current page
- `per_page` — entries per page
- `total` — total count across all pages

### In the SDK

- `list($filters)` — a single page (no auto-paging).
- `listPage($filters)` — returns a `Page<T>` with the items **and** a `PageInfo` (`page`, `perPage`, `total`).
- `iterateAll($filters)` — a `Generator<T>` that walks through all pages transparently and increments `page` internally.

```php
foreach ($billomat->invoices->iterateAll(new InvoiceFilter(status: [InvoiceStatus::OPEN]), pageSize: 200) as $invoice) {
    // …
}
```

`iterateAll()` sets `page` and `per_page` itself; the page size comes from `pageSize`.

See `AbstractApi::listResource()`, `listResourcePage()` and `iterateResource()` in `src/Api/AbstractApi.php`, and [Pagination](../advanced/pagination.md) for details.

## Filtering

The supported filter parameters are listed per resource in `docs/resources/<resource>.md`. The large resources have typed filter classes, generated from the Billomat documentation: `ClientFilter`, `SupplierFilter`, `ArticleFilter`, `UserFilter`, `InvoiceFilter`, `OfferFilter`, `ConfirmationFilter`, `CreditNoteFilter`, `DeliveryNoteFilter`, `LetterFilter`, `ReminderFilter`, `IncomingFilter`, `RecurringFilter` and `TemplateFilter`.

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

- All fields are optional named arguments; unset fields (`null` or an empty list) are left out of the query.
- Multi-value fields (`status`, `tags`, ID lists like `ClientFilter::$invoiceId`) are lists and are sent comma-separated (`status=OPEN,OVERDUE`); Billomat combines them with OR.
- `from`/`to` are `DateTimeImmutable` and are sent as `YYYY-MM-DD`; statuses, payment types and intervals are enums.
- Filters are immutable: `$filter->withStatus(InvoiceStatus::PAID)` (variadic for list fields) or `$filter->withPage(2)` returns a modified copy.
- Every filter has `orderBy`, `page`, `perPage` and `extra`. `extra` passes additional query parameters, such as [custom attributes](custom-meta-attributes.md); they override fields with the same name.

Sub-resources take the parent ID as the first argument and a filter after it. Payments and property values have their own generated filters (`InvoicePaymentFilter`, `ClientPropertyValueFilter`, …); items, comments, tags, email receivers and property definitions only sort and paginate, via the generic `SubResourceFilter`:

```php
use Justpilot\Billomat\Api\InvoicePaymentFilter;
use Justpilot\Billomat\Api\SubResourceFilter;

$payments = $billomat->invoices->payments->list($invoiceId, new InvoicePaymentFilter(orderBy: OrderBy::desc('date')));
$items = $billomat->invoices->items->list($invoiceId, new SubResourceFilter(orderBy: OrderBy::asc('position')));
```

The parent ID wins over a same-named key in the filter. Passing the filter of another resource (e.g. `CreditNotePaymentFilter` to `invoices->payments`) throws an `InvalidArgumentException`.

An array still works everywhere, including resources without a filter class:

```php
$billomat->clients->list(['name' => 'Acme', 'country_code' => 'DE']);
```

### Fast counting via HEAD

If you only want to know **how many** results a filter matches, send a `HEAD` request instead of `GET` and read the `X-Total-Count` response header. This saves transferring the actual records.

The SDK doesn't expose HEAD directly; if needed, create your own `BillomatHttpClient` with the same `BillomatConfig` and call `request('HEAD', …)`.

## Sorting — `order_by`

Format: `<field> <ASC|DESC>`, multiple sort orders comma-separated. In the SDK, `OrderBy` builds the value:

```php
OrderBy::desc('date')->thenAsc('invoice_number'); // order_by=date+DESC,invoice_number+ASC
```

`OrderBy` checks that the field name is snake_case and throws an `InvalidArgumentException` otherwise.

### Quirk: `+` instead of a space

Billomat expects the separator as a **literal `+`**, not as `%20` or `%2B`. `BillomatHttpClient::buildBillomatQuery()` therefore builds the query string by hand:

- Array values are serialized as `key[]=v1&key[]=v2`.
- After `rawurlencode`, `%2B` sequences are turned back into `+`.

`http_build_query()` would get both wrong.

## Language — `Accept-Language`

Some responses contain localized strings (e.g. error messages or the activity feed). The `Accept-Language` HTTP header selects the locale:

```
Accept-Language: de-de
Accept-Language: en-gb
```

In the SDK you set the value for all requests via `BillomatConfig::$language` or `BillomatClient::create(language: 'en-gb')`; the default is `de-de`, and `null` omits the header.

## Reading a single resource

Single resources are read with `get($id)`:

```php
$invoice = $billomat->invoices->get(42);        // null on 404
$invoice = $billomat->invoices->getOrFail(42);  // NotFoundException on 404
```

`getOrFail()` exists on all resources and sub-resources that have `get()`; it saves the `null` check when a missing record is an error.

`get()` turns a 404 into `null`. Other errors are mapped to typed exceptions (see [Errors and rate limits](errors-and-rate-limits.md)).
