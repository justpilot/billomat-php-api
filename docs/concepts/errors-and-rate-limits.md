<!-- Source: https://www.billomat.com/api/grundlagen/fehler/ + https://www.billomat.com/api/grundlagen/zugriffsbegrenzung/ -->

# Errors and rate limits

When something goes wrong, Billomat responds with a `4xx`/`5xx` status code and a short plain-text message in the body. This SDK translates that into a small hierarchy of typed exceptions; all of them extend `BillomatException`.

## Status code mapping

`BillomatHttpClient::request()` (see `src/Http/BillomatHttpClient.php`) translates every response with status ≥ 400 — after any automatic retries — into:

| Status | SDK exception |
|---|---|
| 401, 403 | `AuthenticationException` |
| 404 | `NotFoundException` |
| 400, 422 | `ValidationException` |
| 429 | `RateLimitException` (extends `HttpException`) |
| other `4xx`/`5xx` | `HttpException` |
| no response (DNS, timeout, connection reset) | `TransportException` (PSR-18 exception in `getPrevious()`) |

Billomat's error message ends up in the exception message, e.g. `Billomat responded with HTTP 404 to GET /invoices/1. Resource not found`. The raw response body is kept as well (`getResponseBody()`):

```xml
<?xml version="1.0" encoding="UTF-8"?>
<errors>
    <error>Resource not found</error>
</errors>
```

## Classifying errors

Every `BillomatException` answers the two questions a caller usually asks, without evaluating status codes or `getPrevious()`:

| Error | `isTransient()` | `mayHaveBeenProcessed()` | `getRetryAfter()` |
|---|---|---|---|
| 400, 401, 403, 404, 422, other 4xx | `false` | `false` | `null` |
| 429 | `true` | `false` | `Retry-After` or `X-Rate-Limit-Reset` |
| 502, 503, 504 | `true` | `true` | `Retry-After`, if set |
| 500 and other 5xx | `false` | `true` | `null` |
| `TransportException` (network error, timeout) | `true` | `true` | – |
| `TransportException` wrapping a PSR-18 `RequestExceptionInterface` (request rejected by the client, never sent) | `false` | `false` | – |
| `UnexpectedResponseException` (2xx with an unexpected body) | `false` | `true` | – |

- **`isTransient()`**: a later attempt may succeed. The SDK's automatic retries are already exhausted at this point; a later retry, e.g. via a queue, makes sense.
- **`mayHaveBeenProcessed()`**: Billomat may have processed the request despite the error. Before sending the POST again, check via `get()`/`list()` whether the invoice already exists. With `false` (4xx), Billomat definitely rejected the request.

```php
use Justpilot\Billomat\Exception\BillomatException;
use Justpilot\Billomat\Exception\HttpException;

try {
    $invoice = $billomat->invoices->create($options);
} catch (BillomatException $e) {
    if ($e->mayHaveBeenProcessed()) {
        // Check first whether the invoice was already created.
    }
    if ($e->isTransient()) {
        $job->releaseIn($e instanceof HttpException ? $e->getRetryAfter() ?? 60 : 60);
    }
}
```

## `get($id)` and 404

`get()` turns a 404 into `null`; a 2xx response without the expected envelope throws `UnexpectedResponseException`. In practice:

```php
$invoice = $billomat->invoices->get(999_999); // null instead of an exception
if ($invoice === null) { /* doesn't exist */ }

$invoice = $billomat->invoices->getOrFail(999_999); // NotFoundException instead of null
```

A 404 from any other method (`list()`, `update()`, `complete()`, …) still throws a `NotFoundException`.

## Rate limits

The API counts calls in **15-minute windows**, fixed at `XX:00–XX:14`, `XX:15–XX:29`, `XX:30–XX:44`, `XX:45–XX:59` (server time, not the client's wall clock). Unused calls expire when the window changes — the quota can't be saved up.

### Headers on every response

| Header | Meaning |
|---|---|
| `X-Rate-Limit-Remaining` | remaining calls in the current window |
| `X-Rate-Limit-Reset` | Unix timestamp (seconds) of the next window start |

The SDK evaluates `X-Rate-Limit-Reset` on a 429 response (see below). It doesn't read `X-Rate-Limit-Remaining` — if you want to throttle ahead of time, read the headers with a decorator around the PSR-18 client or a listener for the `ResponseReceived` event.

### 429 — limit exceeded

When the window's quota is used up, Billomat responds with `429 Too Many Requests`. The error message states the number of seconds until the reset:

```xml
<error>Maximum number of requests reached. Try again in 246 seconds.</error>
```

The SDK then waits by itself: it takes the wait from `Retry-After` (if set) or `X-Rate-Limit-Reset` and repeats the request — for 429 with every method, including POST/PUT/DELETE, because Billomat didn't process the request. This only happens if the wait is at most `RetryPolicy::$maxDelayMs` (default 10 s) and attempts are left. Otherwise the SDK throws a `RateLimitException` immediately; when it reaches the caller, the retries are exhausted. `getRetryAfter()` returns the seconds until the next window (or `null` if Billomat didn't say):

```php
use Justpilot\Billomat\Exception\RateLimitException;

try {
    $billomat->invoices->list();
} catch (RateLimitException $e) {
    $job->releaseIn($e->getRetryAfter() ?? 60); // e.g. reschedule a queue job
}
```

### Registered vs. unregistered app

| Mode | Quota | App headers required |
|---|---|---|
| **unregistered** | the number of calls per 15 min guaranteed by the plan — **shared** across all unregistered services on the account | no |
| **registered** | full plan quota **per app per account** | `X-AppId` + `X-AppSecret` |

Apps are registered in the Billomat UI under *Settings → Administration → Apps*; the SDK passes the values through `BillomatConfig` (see [Authentication](authentication.md)).

### Webhooks save calls

Webhooks do **not** count toward the rate limit. Instead of polling lists regularly, set up a webhook receiver for each relevant event (see [Webhooks](webhooks.md)).

## Automatic retries

`BillomatConfig::$retry` (`RetryPolicy`, default: `maxRetries: 2`, `baseDelayMs: 500`, `maxDelayMs: 10_000`) defines what the SDK retries:

| Case | Methods | Wait |
|---|---|---|
| 429 | all | `Retry-After` or `X-Rate-Limit-Reset`; longer than `maxDelayMs` → `RateLimitException` immediately |
| 502, 503, 504 | GET only | exponential backoff with jitter |
| network error | GET only | exponential backoff with jitter |
| 500, other errors | — | no retry |

POST/PUT/DELETE are never retried except on 429, because Billomat's write endpoints aren't idempotent. If you need your own strategy (e.g. waiting longer for the next window in a background job), disable the built-in retries with `RetryPolicy::none()` or raise `maxDelayMs` — see [Configuration](../configuration.md#retries-retrypolicy).
