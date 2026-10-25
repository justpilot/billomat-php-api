# Configuration

This page covers every way to create a `BillomatClient` and the options that control HTTP communication.

## Quick start: `BillomatClient::create()`

The `BillomatClient::create()` factory covers most use cases:

```php
use Justpilot\Billomat\BillomatClient;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);
```

### Parameters

| Parameter | Type | Default | Description |
|---|---|---|---|
| `billomatId` | `string` | — | Subdomain of your Billomat account. For `https://mycompany.billomat.net/` the ID is `mycompany`. Required. |
| `apiKey` | `string` | — | API key from `https://<billomatId>.billomat.net/app/settings/api`. Required. Sent as the `X-BillomatApiKey` header. |
| `appId` | `?string` | `null` | Only for registered third-party apps. Header `X-AppId`. |
| `appSecret` | `?string` | `null` | Only for registered third-party apps. Header `X-AppSecret`. Sent only when both `appId` and `appSecret` are set. |
| `timeout` | `float` | `10.0` | Timeout in seconds per request. Applies only to the client the SDK creates itself; a custom `httpClient` configures its own timeout. |
| `httpClient` | `?ClientInterface` | `null` | Optional custom PSR-18 client (`Psr\Http\Client\ClientInterface`). If `null`, the SDK uses Symfony's `Psr18Client` with `timeout` when `symfony/http-client` is installed, otherwise the client found by `php-http/discovery`. |
| `retry` | `RetryPolicy` | `new RetryPolicy()` | Automatic retries on 429 and transient errors (see [Retries](#retries-retrypolicy)). `RetryPolicy::none()` disables them. |
| `language` | `?string` | `'de-de'` | Value of the `Accept-Language` header, e.g. `'en-gb'` for English error messages. `null` omits the header. |
| `logger` | `?LoggerInterface` | `null` | PSR-3 logger for responses, retries and errors (see [Logging](#example-logging-requests-and-responses)). |
| `events` | `?EventDispatcherInterface` | `null` | PSR-14 dispatcher for `RequestStarted`, `ResponseReceived`, `RetryScheduled` and `RequestFailed`. |

The signature:

```php
public static function create(
    string $billomatId,
    string $apiKey,
    ?string $appId = null,
    ?string $appSecret = null,
    float $timeout = 10.0,
    ?ClientInterface $httpClient = null,
    RetryPolicy $retry = new RetryPolicy(),
    ?string $language = 'de-de',
    ?LoggerInterface $logger = null,
    ?EventDispatcherInterface $events = null,
): self
```

## Full control: using `BillomatConfig` directly

For special cases (custom `baseUri`, dependency injection containers) you can build the client by hand:

```php
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Config\RetryPolicy;

$config = new BillomatConfig(
    billomatId: 'mycompany',
    apiKey: '…',
    appId: null,
    appSecret: null,
    baseUri: 'https://%s.billomat.net/api/',
    timeout: 15.0,
    retry: new RetryPolicy(),
    language: 'en-gb',
);

$billomat = new BillomatClient($config);
```

Besides the config, the `BillomatClient` constructor optionally takes a PSR-18 client, PSR-17 factories, a PSR-3 logger and a PSR-14 event dispatcher — useful when a DI container provides them:

```php
public function __construct(
    BillomatConfig $config,
    ?Psr\Http\Client\ClientInterface $httpClient = null,
    ?Psr\Http\Message\RequestFactoryInterface $requestFactory = null,
    ?Psr\Http\Message\StreamFactoryInterface $streamFactory = null,
    ?Psr\Log\LoggerInterface $logger = null,
    ?Psr\EventDispatcher\EventDispatcherInterface $events = null,
)
```

Without factories, the SDK looks them up via `php-http/discovery`.

`BillomatConfig` is `final readonly`. The default `baseUri` contains a `%s` placeholder that `getBaseUri()` replaces with the `billomatId` via `sprintf`. For tests against a local mock server you can set a different URL here.

The constructor validates its input and throws an `InvalidArgumentException` if:

- `billomatId` is not a valid subdomain name (letters, digits and inner hyphens, at most 63 characters),
- the base URI built from it cannot be parsed as an absolute URI with `Uri\Rfc3986\Uri`,
- `language` is not a valid `Accept-Language` value (this prevents header injection via line breaks).

## Retries: `RetryPolicy`

`BillomatConfig::$retry` controls when the SDK retries a request on its own:

| Parameter | Default | Meaning |
|---|---|---|
| `maxRetries` | `2` | Retries in addition to the first attempt. |
| `baseDelayMs` | `500` | Starting value of the exponential backoff (`baseDelayMs * 2^attempt`, with jitter). |
| `maxDelayMs` | `10_000` | Upper limit for each wait. If Billomat announces a longer wait on 429, the SDK throws a `RateLimitException` immediately. |

- **429:** every request, with the wait from `Retry-After` or `X-Rate-Limit-Reset`.
- **502/503/504 and network errors:** GET only, with exponential backoff.
- **POST/PUT/DELETE** are never retried except on 429; **500** is never retried.

```php
$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
    retry: RetryPolicy::none(), // e.g. in a job that reschedules itself
);
```

Details: [docs/advanced/http-layer.md](advanced/http-layer.md#retries-and-rate-limits).

## HTTP headers set by the SDK

`BillomatHttpClient` sends these headers with every request:

| Header | Value |
|---|---|
| `X-BillomatApiKey` | from `BillomatConfig::$apiKey` |
| `X-AppId` | from `BillomatConfig::$appId`, only when both app values are set |
| `X-AppSecret` | from `BillomatConfig::$appSecret`, only when both app values are set |
| `Accept` | `application/json` |
| `Accept-Language` | from `BillomatConfig::$language` (default `de-de`), omitted when `null` |
| `Content-Type` | `application/json` (only for POST/PUT with a body) |

## Injecting your own HTTP client

The SDK talks HTTP only through PSR-18 (`Psr\Http\Client\ClientInterface`) and PSR-17 factories. You can pass any compatible client and wrap it in decorators for logging, tracing or caching. The SDK already handles retries (see [Retries](#retries-retrypolicy)); you don't need an extra retry decorator.

### Example: Guzzle

```php
use GuzzleHttp\Client;
use Justpilot\Billomat\BillomatClient;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
    httpClient: new Client(['timeout' => 15.0]),
);
```

### Example: logging requests and responses

The SDK logs via PSR-3 and reports every request as a PSR-14 event. Pass `logger` and/or `events`:

```php
use Justpilot\Billomat\BillomatClient;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
    logger: $logger,          // Psr\Log\LoggerInterface
    events: $eventDispatcher, // Psr\EventDispatcher\EventDispatcherInterface
);
```

| Log level | When |
|---|---|
| `debug` | successful response: method, path, status, duration |
| `warning` | retry: reason (`HTTP 503`, `network error: …`), delay, attempt |
| `notice` | final 4xx error (including the expected 404 from `get()`) |
| `error` | final 5xx or network error |

The log context contains only `method`, `path`, `attempt` and, depending on the message, `status`, `duration_ms`, `reason`, `delay_ms`, `message` and `exception` — no headers, no query string and no body, so neither the API key nor customer data.

Events (`Justpilot\Billomat\Event`, all `final readonly`):

| Event | Properties |
|---|---|
| `RequestStarted` | `request`, `attempt` (starting at 1), once per attempt |
| `ResponseReceived` | `request`, `response`, `attempt`, `durationMs` — for every status |
| `RetryScheduled` | `request`, `attempt`, `delayMs`, `response` (429/5xx) or `error` (network error) |
| `RequestFailed` | `request`, `exception`, `attempt` — right before the SDK throws the exception |

`$event->request` contains the `X-BillomatApiKey` header — mask it before logging requests unfiltered. See [SECURITY.md](../SECURITY.md).

To also log the transport layer, you can still pass a Symfony client with a logger (`$symfony->setLogger($logger)`, wrapped in `Psr18Client`).

### Example: injecting a `MockHttpClient` in tests

This is the same pattern the SDK's own unit tests use:

```php
use Justpilot\Billomat\BillomatClient;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

$mock = new MockHttpClient(static function (string $method, string $url, array $options): MockResponse {
    // Assertions on $method/$url/$options go here
    return new MockResponse('{"clients":{"client":[]}}', ['http_code' => 200]);
});

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: 'test-key',
    httpClient: new Psr18Client($mock),
);
```

More in [docs/testing.md](testing.md).

## HTTP layer quirks

Billomat expects some query string formats that `http_build_query` doesn't produce. The SDK handles these quirks in `BillomatHttpClient::buildBillomatQuery()`:

- Array values are serialized as `key[]=v1&key[]=v2` (percent-encoded on the wire as `key%5B%5D=v1`, which Billomat accepts as well).
- `+` stays literal in values (e.g. `order_by=date+DESC`), because Billomat doesn't interpret `%2B` as `+`.

Both are transparent when you pass filters to `list($filters)`. Background: [docs/advanced/http-layer.md](advanced/http-layer.md).
