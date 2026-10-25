# HTTP layer

The SDK's HTTP layer is deliberately thin. It builds on PSR-18 (HTTP client) and PSR-17 (request/stream factories) and handles five tasks: setting authentication headers, building the URL and query string, serializing the JSON body, translating 4xx/5xx into SDK exceptions, and retrying requests according to the `RetryPolicy`. This page describes the few but important quirks — and how you can hook in.

## Classes and responsibilities

| Class | Responsibility | File |
|---|---|---|
| `BillomatHttpClientInterface` | Contract: `request(method, path, query, json): ResponseInterface` (PSR-7). The response always has a status below 400; errors are thrown as exceptions. | `src/Http/BillomatHttpClientInterface.php` |
| `BillomatHttpClient` | Concrete implementation. Gets a PSR-18 `ClientInterface` and `BillomatConfig` injected, optionally PSR-17 factories (otherwise via `php-http/discovery`), a PSR-3 logger and a PSR-14 event dispatcher. Handles error mapping and retries. | `src/Http/BillomatHttpClient.php` |
| `AbstractApi` | Wrapper around `BillomatHttpClientInterface` with JSON decoding, envelope unwrapping and list/pagination helpers. | `src/Api/AbstractApi.php` |

`BillomatClient` creates a `BillomatHttpClient` internally and passes it to every `*Api` class. You can't plug your own `BillomatHttpClientInterface` implementation into `BillomatClient` — the lever is the underlying PSR-18 client (see the next section).

## Replacing the PSR-18 client

The quick lever: don't replace `BillomatHttpClient`, replace the PSR-18 client underneath it. `BillomatClient::create()` and the constructor accept any `Psr\Http\Client\ClientInterface`; the constructor also takes your own PSR-17 `RequestFactoryInterface`/`StreamFactoryInterface`.

Without one, the SDK uses Symfony's `Psr18Client` with `BillomatConfig::$timeout` when `symfony/http-client` is installed, otherwise the client found by `php-http/discovery` (Guzzle, …). A custom client configures its own timeout — `$timeout` doesn't apply then.

Wrap a Symfony `HttpClientInterface` in `Psr18Client`:

```php
use Justpilot\Billomat\BillomatClient;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\TraceableHttpClient;

$inner = HttpClient::create([
    'timeout' => 10.0,
    'max_duration' => 30.0,
]);

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
    httpClient: new Psr18Client(new TraceableHttpClient($inner)),
);
```

Guzzle works the same way: `httpClient: new GuzzleHttp\Client(['timeout' => 10.0])`. This pattern gives you tracing, auth decoration etc. without changing SDK code. For logging and request events, the SDK has built-in PSR-3/PSR-14 support (`logger:`, `events:`, see [Configuration](../configuration.md#example-logging-requests-and-responses)). The SDK handles retries itself (see below); an extra retry decorator would double them.

## Retries and rate limits

`BillomatHttpClient` retries requests according to `BillomatConfig::$retry` (`RetryPolicy`, default: `maxRetries: 2`, `baseDelayMs: 500`, `maxDelayMs: 10_000`):

| Situation | Methods | Behavior |
|---|---|---|
| 429 Too Many Requests | all | Waits for the time Billomat announces (`Retry-After`, otherwise `X-Rate-Limit-Reset`), but only if it is ≤ `maxDelayMs` — otherwise throws `RateLimitException` immediately. Without a header: exponential backoff. |
| 502, 503, 504 | GET only | Exponential backoff with jitter (`baseDelayMs * 2^attempt`, capped at `maxDelayMs`). |
| Network error (PSR-18 `NetworkExceptionInterface`) | GET only | Same as 502–504. |
| 500 and all other errors | — | No retry. |

POST/PUT/DELETE are never retried except on 429, because Billomat's write endpoints aren't idempotent (e.g. `PUT /invoices/{id}/complete`). On 429 Billomat didn't process the request, so retrying is safe there. The request is rebuilt for every attempt.

```php
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Config\RetryPolicy;

$config = new BillomatConfig(
    billomatId: 'mycompany',
    apiKey: '…',
    retry: new RetryPolicy(maxRetries: 4, maxDelayMs: 30_000),
    // retry: RetryPolicy::none(), // disable retries
);
```

Once all attempts are used up, the client throws the exception for the last response (see [Error handling](../error-handling.md)); network errors surface as `TransportException` with the PSR-18 exception as `getPrevious()`.

## Quirk 1: `+` stays literal in the query string

`http_build_query` (standard PHP) always converts `+` to `%2B`. Billomat doesn't accept `%2B` as a separator in compound values — the typical example is sorting with `order_by=date+DESC`. If the SDK sent `%2B`, Billomat would ignore the parameter.

Solution: `BillomatHttpClient::buildBillomatQuery()` builds the query string by hand. Values are encoded with `rawurlencode()`, then `%2B` is turned back into `+`. Result:

```text
?order_by=date+DESC&per_page=50
```

This way every sort parameter documented by Billomat as `<field>+ASC` or `<field>+DESC` works without a workaround. In SDK code, build the value with `OrderBy` (`OrderBy::desc('date')`) instead of writing the string by hand.

## Quirk 2: arrays as `key[]=v1&key[]=v2`

If the value of a filter key is an array, the SDK serializes it as repeated `key[]` entries — not as `key=v1,v2` and not as `key[0]=v1`:

```php
$billomat->invoices->list([
    'status' => ['OPEN', 'OVERDUE'],
]);
```

Result:

```text
?status[]=OPEN&status[]=OVERDUE
```

On the wire the brackets are percent-encoded (`?status%5B%5D=OPEN&status%5B%5D=OVERDUE`), because the PSR-7 URI encodes `[` and `]` in the query. Billomat decodes this as `status[]`; in tests, assert against the encoded form.

The typed filters (`InvoiceFilter`, `ClientFilter`, …) don't use this form: they send multi-value fields comma-separated (`status=OPEN,OVERDUE`), which Billomat treats as OR (see [Pagination and filtering](../concepts/pagination-and-filtering.md)).

`null` values (top level as well as inside an array) are skipped silently — the key doesn't appear in the query string.

## Value conversion

`BillomatHttpClient::encodeBillomatQueryValue()` casts the individual values:

| PHP type | Wire representation |
|---|---|
| `bool` | `1` / `0` |
| `int`, `float` | `(string)` |
| `string` | unchanged |

Then `rawurlencode`, followed by the `%2B` → `+` correction.

Other types (objects such as `DateTimeImmutable`, enums) are not accepted in raw array filters; convert them to a string first. The typed filters do this for you (`from`/`to` as `YYYY-MM-DD`, enums as their value).

## Binary responses

Some Billomat endpoints return binary data — PDFs or thumbnails, for example. The SDK uses two patterns:

### Pattern A: `AbstractApi::getRaw()` for read-only binary fetches

Used by `TemplatesApi::thumb()`:

```php
#[NoDiscard]
public function thumb(int $id, TemplateThumbFormat $format = TemplateThumbFormat::PNG): string
{
    return $this->getRaw("/templates/{$id}/thumb", [
        'format' => $format->value,
    ]);
}
```

`getRaw()` sends the request and returns `(string) $response->getBody()`. Any HTTP error has already been thrown as an SDK exception by `BillomatHttpClient::request()` at that point. The return value is the binary string.

For PDFs, use `AbstractApi::getPdf()` instead: it works like `getRaw()` but throws an `UnexpectedResponseException` when the body is empty or doesn't start with `%PDF-` (Billomat occasionally answers 200 with an empty body or an HTML page).

### Pattern B: endpoints with a mode switch

Used by `InvoicesApi::pdfContent(int $id, ?PdfType $type = null)`: the endpoint returns either JSON or binary data depending on `format=pdf`. `pdfContent()` sets `format=pdf` and calls `getPdf(...)`; `pdf()` calls `getJson(...)` and returns an `InvoicePdf` model with the Base64 content.

When writing your own resources, follow these two patterns; there is no generic helper for "endpoint with content switch" (yet).

## Unwrapping response envelopes

Billomat consistently wraps responses in an envelope:

- List: `{ "clients": { "client": [ {…}, {…} ] } }`
- Single object: `{ "client": { … } }`

Quirk: when a list has exactly **one** entry, Billomat sometimes returns a single object instead of a one-element list. `AbstractApi::listResource()` (and `listResourcePage()`, which `iterateAll()` builds on) normalizes this with an `array_is_list()` check, so every `*Api::list()` that uses it gets the behavior for free — see `ClientsApi::list()`. Sub-resources (`ItemsApi`, `CommentsApi`, …) go through the same helper via `AbstractSubResourceApi`. Single objects are unwrapped with `AbstractApi::unwrapEnvelope()`, which throws an `UnexpectedResponseException` when the expected key is missing. When you add a new resource, use these helpers.

## What the layer does **not** do

- No proactive rate limiting: the SDK doesn't throttle ahead of time based on `X-Rate-Limit-Remaining`; it only reacts to 429.
- No retries of POST/PUT/DELETE except on 429, and no retries on 500.
- No caching of GET responses.
- No payload validation before it reaches Billomat. The SDK sends what you give it — Billomat returns validation errors as `ValidationException`, see [docs/error-handling.md](../error-handling.md).
