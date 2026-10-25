# Error handling

Every error the SDK throws extends `Justpilot\Billomat\Exception\BillomatException`. Billomat's HTTP errors map to a small, specialized hierarchy so you can react to specific error classes.

## Exception hierarchy

```text
\RuntimeException
└── BillomatException
    ├── HttpException
    │   ├── AuthenticationException
    │   ├── ValidationException
    │   ├── NotFoundException
    │   └── RateLimitException
    ├── TransportException
    ├── UnexpectedResponseException
    └── InvalidWebhookException
```

| Class | File | Meaning |
|---|---|---|
| `BillomatException` | `src/Exception/BillomatException.php` | Base class. Catching it catches everything the SDK throws. `isTransient()` and `mayHaveBeenProcessed()` classify the error (see [Classifying errors](concepts/errors-and-rate-limits.md#classifying-errors)). |
| `HttpException` | `src/Exception/HttpException.php` | Generic HTTP error. Holds the status code, raw body and `getRetryAfter(): ?int` (for 429 and 502/503/504). |
| `AuthenticationException` | `src/Exception/AuthenticationException.php` | 401/403 — invalid or missing API key, missing permission. |
| `NotFoundException` | `src/Exception/NotFoundException.php` | 404 — the resource doesn't exist. |
| `ValidationException` | `src/Exception/ValidationException.php` | 400/422 — payload rejected, required field missing, invalid value. |
| `RateLimitException` | `src/Exception/RateLimitException.php` | 429 — rate limit exhausted and the announced wait exceeds `RetryPolicy::$maxDelayMs` (or all attempts are used up). `getRetryAfter()` returns the seconds until the next window. |
| `TransportException` | `src/Exception/TransportException.php` | No response from Billomat (DNS, connection reset, timeout). The PSR-18 client's exception is available via `getPrevious()`. |
| `UnexpectedResponseException` | `src/Exception/UnexpectedResponseException.php` | 2xx, but the body doesn't match the expected structure (since 3.2). |
| `InvalidWebhookException` | `src/Exception/InvalidWebhookException.php` | An incoming webhook request couldn't be parsed (see [Webhooks](concepts/webhooks.md#receiving-webhooks-with-the-sdk)). |

## Mapping HTTP status → exception

`BillomatHttpClient::request()` (see `src/Http/BillomatHttpClient.php`) turns every response with status ≥ 400 into an SDK exception — only after the automatic retries (see [HTTP layer](advanced/http-layer.md#retries-and-rate-limits)) are exhausted:

| HTTP status | Exception thrown |
|---|---|
| 401 Unauthorized | `AuthenticationException` |
| 403 Forbidden | `AuthenticationException` |
| 404 Not Found | `NotFoundException` |
| 400 Bad Request | `ValidationException` |
| 422 Unprocessable Entity | `ValidationException` |
| 429 Too Many Requests | `RateLimitException` |
| other 4xx, 5xx | `HttpException` |
| no response (network error) | `TransportException` |

Every `HttpException` (including subclasses) carries:

- `getMessage(): string` — e.g. `Billomat responded with HTTP 422 to POST /clients. <Billomat's error text>`. The SDK reads the error text from `{"errors": {"error": …}}` (JSON) or `<error>…</error>` (XML); without one, the message ends after the path.
- `getStatusCode(): int` — Billomat's HTTP status (same as `getCode()`).
- `getResponseBody(): ?string` — the unmodified response body. Useful because Billomat sends validation details in it.

## Special case: `get($id)` returns `null` instead of throwing on 404

Methods like `ClientsApi::get(int $id)` or `InvoicesApi::get(int $id)` use `AbstractApi::getJsonOrNull()` internally. It catches a 404 response and returns `null` instead. You don't need `try/catch` for "record doesn't exist":

```php
$client = $billomat->clients->get(99999);

if ($client === null) {
    // Client doesn't exist — not an error, just handle it
    echo "No client with ID 99999.\n";
    return;
}

echo $client->name;
```

If a missing record is an error in your code, use `getOrFail()`. It returns the model or throws a `NotFoundException`:

```php
$client = $billomat->clients->getOrFail(99999); // NotFoundException on 404
```

Other 4xx/5xx responses (e.g. 401 for an expired key) still throw as usual.

## Patterns

### Handling errors by type

```php
use Justpilot\Billomat\Api\ClientCreateOptions;
use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\RateLimitException;
use Justpilot\Billomat\Exception\ValidationException;

$opts = new ClientCreateOptions(name: 'Example GmbH');
// (forgot: email, if the account requires it)

try {
    $client = $billomat->clients->create($opts);
} catch (AuthenticationException $e) {
    // API key wrong, missing or expired
    error_log('Billomat authentication failed: ' . $e->getMessage());
    throw $e;
} catch (ValidationException $e) {
    // Billomat rejected the payload — details are in the body
    error_log(sprintf(
        'Validation failed (HTTP %d): %s',
        $e->getStatusCode(),
        $e->getResponseBody() ?? '(no body)',
    ));
    throw $e;
} catch (RateLimitException $e) {
    // Limit exhausted, the SDK didn't wait any longer
    $wait = $e->getRetryAfter(); // seconds or null
    throw $e;
} catch (HttpException $e) {
    // Everything else (5xx, unknown 4xx)
    error_log(sprintf('Billomat HTTP error %d', $e->getStatusCode()));
    throw $e;
}
```

### Extracting validation details

On 400/422 Billomat usually returns JSON with details. The body is available as a raw string; parse it as needed:

```php
use Justpilot\Billomat\Exception\ValidationException;

try {
    $billomat->invoices->create($options);
} catch (ValidationException $e) {
    $body = $e->getResponseBody();

    if ($body !== null) {
        $decoded = json_decode($body, true);
        // The structure depends on the endpoint; Billomat often returns
        // { "errors": { "error": "Field X is missing" } }
        var_dump($decoded);
    }

    throw $e;
}
```

### Catching everything at once

If you only want to handle "something went wrong with Billomat":

```php
use Justpilot\Billomat\Exception\BillomatException;

try {
    $billomat->invoices->complete($id);
} catch (BillomatException $e) {
    // catches every SDK exception (HttpException and subclasses, TransportException, …)
}
```

## Non-HTTP errors

API methods throw an `UnexpectedResponseException` when Billomat responds with 2xx but the body doesn't match the expected structure — e.g. when the wrapper element `client` or `invoice` is missing, or a read model lacks a field Billomat always sends (e.g. `id`; the message names model and field). This is a defensive check and shouldn't happen in practice; if it does, please open an issue with the raw body.

Up to and including 3.1 this was a plain `\RuntimeException`. Since `UnexpectedResponseException` still extends `\RuntimeException` through `BillomatException`, existing `catch (\RuntimeException)` blocks keep working.

A 2xx body that isn't valid JSON or isn't a JSON object also results in an `UnexpectedResponseException` (the original `\JsonException` is available via `getPrevious()`). PDF downloads (`pdfContent()`) throw it too when the body is empty or doesn't start with `%PDF-`.

If no response arrives at all (DNS error, connection reset, timeout), the SDK throws a `TransportException`. GET requests are retried first according to the `RetryPolicy`; POST/PUT/DELETE are not.
