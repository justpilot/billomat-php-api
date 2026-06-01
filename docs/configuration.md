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
| `timeout` | `float` | `10.0` | Symfony-HttpClient-Timeout in Sekunden für jeden Request. |
| `httpClient` | `?HttpClientInterface` | `null` | Optionaler eigener Symfony-`HttpClientInterface`. Wenn `null`, wird `HttpClient::create()` verwendet. |

Die Methode signiert als:

```php
public static function create(
    string $billomatId,
    string $apiKey,
    ?string $appId = null,
    ?string $appSecret = null,
    float $timeout = 10.0,
    ?HttpClientInterface $httpClient = null,
): self
```

## Volle Kontrolle: `BillomatConfig` direkt verwenden

Für Sonderfälle (eigene `baseUri`, Dependency-Injection-Container) lässt sich der Client manuell zusammenbauen:

```php
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Config\BillomatConfig;

$config = new BillomatConfig(
    billomatId: 'mycompany',
    apiKey: '…',
    appId: null,
    appSecret: null,
    baseUri: 'https://%s.billomat.net/api/',
    timeout: 15.0,
);

$billomat = new BillomatClient($config);
```

`BillomatConfig` ist `final readonly`. Der Default für `baseUri` enthält ein `%s`-Platzhalter, das `getBaseUri()` mit der `billomatId` per `sprintf` ersetzt. Für reine Tests gegen einen lokalen Mock-Server kann hier eine andere URL gesetzt werden.

## HTTP-Header, die das SDK setzt

Pro Request sendet `BillomatHttpClient`:

| Header | Wert |
|---|---|
| `X-BillomatApiKey` | aus `BillomatConfig::$apiKey` |
| `X-AppId` | aus `BillomatConfig::$appId`, nur wenn beide App-Werte gesetzt sind |
| `X-AppSecret` | aus `BillomatConfig::$appSecret`, nur wenn beide App-Werte gesetzt sind |
| `Accept` | `application/json` |
| `Accept-Language` | `de-de` |
| `Content-Type` | `application/json` (nur bei POST/PUT mit Body) |

## Eigenen HTTP-Client injizieren

Symfonys `HttpClientInterface` ist die einzige externe Abhängigkeit auf HTTP-Ebene. Du kannst beliebige Decorator einbauen — etwa Logging, Retry, Tracing oder Caching.

### Beispiel: Retry bei transienten Fehlern

```php
use Justpilot\Billomat\BillomatClient;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\RetryableHttpClient;

$inner = HttpClient::create();
$retry = new RetryableHttpClient($inner); // Defaults: 3 Versuche, exponentielles Backoff

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
    httpClient: $retry,
);
```

### Beispiel: Request-/Response-Logging

```php
use Justpilot\Billomat\BillomatClient;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\LoggingHttpClient;

$logger = /* PSR-3 Logger */;
$client = new LoggingHttpClient(HttpClient::create(), $logger);

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
    httpClient: $client,
);
```

Achte beim Loggen darauf, den `X-BillomatApiKey`-Header zu maskieren. Siehe [SECURITY.md](../SECURITY.md).

### Beispiel: In Tests einen `MockHttpClient` injizieren

Das ist genau das Muster, das die Unit-Tests im SDK selbst verwenden:

```php
use Justpilot\Billomat\BillomatClient;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

$mock = new MockHttpClient(static function (string $method, string $url, array $options): MockResponse {
    // Assertions auf $method/$url/$options möglich
    return new MockResponse('{"clients":{"client":[]}}', ['http_code' => 200]);
});

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: 'test-key',
    httpClient: $mock,
);
```

Mehr dazu in [docs/testing.md](testing.md).

## Eigenheiten der HTTP-Schicht

Billomat erwartet einige Query-String-Formate, die `http_build_query` nicht standardkonform abbilden würde. Das SDK behandelt diese Quirks in `BillomatHttpClient::buildBillomatQuery()`:

- Array-Werte werden als `key[]=v1&key[]=v2` serialisiert.
- `+` bleibt im Wert literal (z. B. `order_by=date+DESC`), weil Billomat `%2B` nicht als `+` interpretiert.

Wenn du eigene Filter über `list($filters)` weitergibst, ist beides transparent. Hintergrund: [docs/advanced/http-layer.md](advanced/http-layer.md).
