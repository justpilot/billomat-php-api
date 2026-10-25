# Konfiguration

Diese Seite beschreibt alle Wege, einen `BillomatClient` zu instanziieren, sowie die Optionen, die das Verhalten der HTTP-Kommunikation steuern.

## Schneller Einstieg: `BillomatClient::create()`

Die Factory `BillomatClient::create()` deckt 95 % der Anwendungsfälle ab:

```php
use Justpilot\Billomat\BillomatClient;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);
```

### Parameter

| Parameter | Typ | Default | Beschreibung |
|---|---|---|---|
| `billomatId` | `string` | — | Subdomain-Teil deiner Billomat-Instanz. Bei `https://mycompany.billomat.net/` ist die ID `mycompany`. Pflicht. |
| `apiKey` | `string` | — | API-Schlüssel aus `https://<billomatId>.billomat.net/app/settings/api`. Pflicht. Wird als Header `X-BillomatApiKey` gesendet. |
| `appId` | `?string` | `null` | Nur für eingetragene Drittanbieter-Apps. Header `X-AppId`. |
| `appSecret` | `?string` | `null` | Nur für eingetragene Drittanbieter-Apps. Header `X-AppSecret`. Wird nur mitgesendet, wenn `appId` und `appSecret` beide gesetzt sind. |
| `timeout` | `float` | `10.0` | Timeout in Sekunden für jeden Request. Gilt nur für den Client, den das SDK selbst erzeugt; ein eigener `httpClient` konfiguriert seinen Timeout selbst. |
| `httpClient` | `?ClientInterface` | `null` | Optionaler eigener PSR-18-Client (`Psr\Http\Client\ClientInterface`). Wenn `null`, nutzt das SDK Symfonys `Psr18Client` mit `timeout`, falls `symfony/http-client` installiert ist, sonst den per `php-http/discovery` gefundenen Client. |
| `retry` | `RetryPolicy` | `new RetryPolicy()` | Automatische Wiederholungen bei 429 und transienten Fehlern (siehe [Retries](#retries-retrypolicy)). `RetryPolicy::none()` schaltet sie ab. |
| `language` | `?string` | `'de-de'` | Wert für `Accept-Language`, z. B. `'en-gb'` für englische Fehlermeldungen. `null` sendet den Header nicht. |

Die Methode signiert als:

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
): self
```

## Volle Kontrolle: `BillomatConfig` direkt verwenden

Für Sonderfälle (eigene `baseUri`, Dependency-Injection-Container) lässt sich der Client manuell zusammenbauen:

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
);

$billomat = new BillomatClient($config);
```

Der Konstruktor von `BillomatClient` nimmt neben der Config optional einen PSR-18-Client sowie PSR-17-Factories entgegen — nützlich, wenn ein DI-Container sie bereitstellt:

```php
public function __construct(
    BillomatConfig $config,
    ?Psr\Http\Client\ClientInterface $httpClient = null,
    ?Psr\Http\Message\RequestFactoryInterface $requestFactory = null,
    ?Psr\Http\Message\StreamFactoryInterface $streamFactory = null,
)
```

Ohne Factories sucht das SDK sie per `php-http/discovery`.

`BillomatConfig` ist `final readonly`. Der Default für `baseUri` enthält ein `%s`-Platzhalter, das `getBaseUri()` mit der `billomatId` per `sprintf` ersetzt. Für reine Tests gegen einen lokalen Mock-Server kann hier eine andere URL gesetzt werden.

Der Konstruktor prüft beide Werte: Die `billomatId` muss ein gültiger Subdomain-Name sein (Buchstaben, Ziffern und Bindestriche im Inneren, höchstens 63 Zeichen), und die daraus gebaute Basis-URI muss sich mit `Uri\Rfc3986\Uri` als absolute URI parsen lassen. Sonst wirft er eine `InvalidArgumentException`.

## Retries: `RetryPolicy`

`BillomatConfig::$retry` steuert, wann das SDK einen Request selbst wiederholt:

| Parameter | Default | Bedeutung |
|---|---|---|
| `maxRetries` | `2` | Wiederholungen zusätzlich zum ersten Versuch. |
| `baseDelayMs` | `500` | Startwert des exponentiellen Backoffs (`baseDelayMs * 2^Versuch`, mit Jitter). |
| `maxDelayMs` | `10_000` | Obergrenze jeder Wartezeit. Nennt Billomat bei 429 eine längere Wartezeit, wirft das SDK sofort eine `RateLimitException`. |

- **429:** jeder Request, mit der Wartezeit aus `Retry-After` bzw. `X-Rate-Limit-Reset`.
- **502/503/504 und Netzwerkfehler:** nur GET, mit exponentiellem Backoff.
- **POST/PUT/DELETE** werden außer bei 429 nie wiederholt, **500** nie.

```php
$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
    retry: RetryPolicy::none(), // z. B. in einem Job, der selbst neu plant
);
```

Details: [docs/advanced/http-layer.md](advanced/http-layer.md#retries-und-rate-limits).

## HTTP-Header, die das SDK setzt

Pro Request sendet `BillomatHttpClient`:

| Header | Wert |
|---|---|
| `X-BillomatApiKey` | aus `BillomatConfig::$apiKey` |
| `X-AppId` | aus `BillomatConfig::$appId`, nur wenn beide App-Werte gesetzt sind |
| `X-AppSecret` | aus `BillomatConfig::$appSecret`, nur wenn beide App-Werte gesetzt sind |
| `Accept` | `application/json` |
| `Accept-Language` | aus `BillomatConfig::$language` (Standard `de-de`), fehlt bei `null` |
| `Content-Type` | `application/json` (nur bei POST/PUT mit Body) |

## Eigenen HTTP-Client injizieren

Das SDK spricht HTTP ausschließlich über PSR-18 (`Psr\Http\Client\ClientInterface`) und PSR-17-Factories. Du kannst jeden kompatiblen Client übergeben und beliebige Decorator einbauen — etwa Logging, Tracing oder Caching. Retries übernimmt das SDK bereits selbst (siehe [Retries](#retries-retrypolicy)); einen zusätzlichen Retry-Decorator brauchst du nicht.

### Beispiel: Guzzle

```php
use GuzzleHttp\Client;
use Justpilot\Billomat\BillomatClient;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
    httpClient: new Client(['timeout' => 15.0]),
);
```

### Beispiel: Request-/Response-Logging

```php
use Justpilot\Billomat\BillomatClient;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\Psr18Client;

$logger = /* PSR-3 Logger */;
$symfony = HttpClient::create(['timeout' => 10.0]);
$symfony->setLogger($logger); // die Clients aus HttpClient::create() sind LoggerAware

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
    httpClient: new Psr18Client($symfony), // Symfony-Clients in Psr18Client einwickeln
);
```

Achte beim Loggen darauf, den `X-BillomatApiKey`-Header zu maskieren. Siehe [SECURITY.md](../SECURITY.md).

### Beispiel: In Tests einen `MockHttpClient` injizieren

Das ist genau das Muster, das die Unit-Tests im SDK selbst verwenden:

```php
use Justpilot\Billomat\BillomatClient;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

$mock = new MockHttpClient(static function (string $method, string $url, array $options): MockResponse {
    // Assertions auf $method/$url/$options möglich
    return new MockResponse('{"clients":{"client":[]}}', ['http_code' => 200]);
});

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: 'test-key',
    httpClient: new Psr18Client($mock),
);
```

Mehr dazu in [docs/testing.md](testing.md).

## Eigenheiten der HTTP-Schicht

Billomat erwartet einige Query-String-Formate, die `http_build_query` nicht standardkonform abbilden würde. Das SDK behandelt diese Quirks in `BillomatHttpClient::buildBillomatQuery()`:

- Array-Werte werden als `key[]=v1&key[]=v2` serialisiert (auf der Leitung percent-kodiert als `key%5B%5D=v1`, was Billomat genauso versteht).
- `+` bleibt im Wert literal (z. B. `order_by=date+DESC`), weil Billomat `%2B` nicht als `+` interpretiert.

Wenn du eigene Filter über `list($filters)` weitergibst, ist beides transparent. Hintergrund: [docs/advanced/http-layer.md](advanced/http-layer.md).
