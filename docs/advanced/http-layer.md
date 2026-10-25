# HTTP-Layer

Die HTTP-Schicht des SDK ist absichtlich dünn. Sie setzt auf PSR-18 (HTTP-Client) und PSR-17 (Request-/Stream-Factories) und kapselt fünf Aufgaben: Authentifizierungs-Header setzen, URL und Query-String bauen, JSON-Body serialisieren, 4xx/5xx in SDK-Exceptions übersetzen und Requests gemäß `RetryPolicy` wiederholen. Dieses Dokument beschreibt die wenigen, aber wichtigen Eigenheiten — und wie du eingreifen kannst.

## Klassen und Verantwortlichkeiten

| Klasse | Aufgabe | Datei |
|---|---|---|
| `BillomatHttpClientInterface` | Vertrag: `request(method, path, query, json): ResponseInterface` (PSR-7). Die Response hat immer einen Status unter 400; Fehler kommen als Exception. | `src/Http/BillomatHttpClientInterface.php` |
| `BillomatHttpClient` | Konkrete Implementierung. Bekommt einen PSR-18-`ClientInterface` + `BillomatConfig` injiziert, optional PSR-17-Factories (sonst per `php-http/discovery`). Übernimmt Fehler-Mapping und Retries. | `src/Http/BillomatHttpClient.php` |
| `AbstractApi` | Wrapper über `BillomatHttpClientInterface` mit JSON-Decoding und Hüllen-Entpackung. | `src/Api/AbstractApi.php` |

`BillomatClient` instanziiert intern ein `BillomatHttpClient` und reicht es an alle `*Api`-Klassen weiter. Eine eigene Implementierung von `BillomatHttpClientInterface` lässt sich nicht in `BillomatClient` einhängen — der Hebel ist der darunterliegende PSR-18-Client (siehe nächster Abschnitt).

## PSR-18-Client austauschen

Der schnelle Hebel: nicht den `BillomatHttpClient` ersetzen, sondern den darunterliegenden PSR-18-Client. `BillomatClient::create()` und der Konstruktor akzeptieren jeden `Psr\Http\Client\ClientInterface`; der Konstruktor nimmt zusätzlich eigene PSR-17-`RequestFactoryInterface`/`StreamFactoryInterface` entgegen.

Ohne Angabe nutzt das SDK Symfonys `Psr18Client` mit `BillomatConfig::$timeout`, falls `symfony/http-client` installiert ist, sonst den per `php-http/discovery` gefundenen Client (Guzzle, …). Ein eigener Client konfiguriert seinen Timeout selbst — `$timeout` gilt dann nicht.

Einen Symfony-`HttpClientInterface` wickelst du in `Psr18Client` ein:

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

Mit Guzzle sieht es genauso aus: `httpClient: new GuzzleHttp\Client(['timeout' => 10.0])`. Mit diesem Pattern bekommst du Logging, Tracing, Auth-Decoration etc., ohne SDK-internen Code zu ändern. Retries übernimmt das SDK selbst (siehe unten); ein zusätzlicher Retry-Decorator würde Wiederholungen doppeln.

## Retries und Rate-Limits

`BillomatHttpClient` wiederholt Requests nach `BillomatConfig::$retry` (`RetryPolicy`, Default: `maxRetries: 2`, `baseDelayMs: 500`, `maxDelayMs: 10_000`):

| Situation | Methoden | Verhalten |
|---|---|---|
| 429 Too Many Requests | alle | Wartet die von Billomat genannte Zeit (`Retry-After`, sonst `X-Rate-Limit-Reset`), aber nur wenn sie ≤ `maxDelayMs` ist — sonst sofort `RateLimitException`. Ohne Header: exponentielles Backoff. |
| 502, 503, 504 | nur GET | Exponentielles Backoff mit Jitter (`baseDelayMs * 2^Versuch`, gedeckelt bei `maxDelayMs`). |
| Netzwerkfehler (PSR-18 `NetworkExceptionInterface`) | nur GET | Wie 502–504. |
| 500 und alle anderen Fehler | — | Kein Retry. |

POST/PUT/DELETE werden außer bei 429 nie wiederholt, weil sie bei Billomat nicht idempotent sind (z. B. `PUT /invoices/{id}/complete`). Bei 429 hat Billomat den Request nicht verarbeitet, daher ist die Wiederholung dort sicher.

```php
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Config\RetryPolicy;

$config = new BillomatConfig(
    billomatId: 'mycompany',
    apiKey: '…',
    retry: new RetryPolicy(maxRetries: 4, maxDelayMs: 30_000),
    // retry: RetryPolicy::none(), // Wiederholungen abschalten
);
```

Sind alle Versuche verbraucht, wirft der Client die Exception der letzten Antwort (siehe [Fehlerbehandlung](../error-handling.md)); Netzwerkfehler kommen als `TransportException` mit der PSR-18-Exception als `getPrevious()`.

## Eigenheit 1: `+` bleibt im Query-String literal

`http_build_query` (PHP-Standard) konvertiert `+` immer zu `%2B`. Billomat akzeptiert `%2B` aber nicht als Trennzeichen in zusammengesetzten Werten — typisches Beispiel: Sortierung mit `order_by=date+DESC`. Würde das SDK `%2B` schicken, würde Billomat den Filter ignorieren.

Lösung: `BillomatHttpClient::buildBillomatQuery()` baut den Query-String von Hand. Werte werden mit `rawurlencode()` codiert, anschließend wird `%2B` wieder durch `+` ersetzt. Resultat:

```text
?order_by=date+DESC&per_page=50
```

So funktionieren alle Filter, die auf der Billomat-Doku `<feld>+ASC` oder `<feld>+DESC` heißen, ohne Workaround.

## Eigenheit 2: Arrays als `key[]=v1&key[]=v2`

Wenn der Wert eines Filter-Schlüssels ein Array ist, serialisiert das SDK ihn als wiederholten `key[]`-Eintrag — nicht als `key=v1,v2` und nicht als `key[0]=v1`. Das ist das einzige Format, das Billomat akzeptiert.

```php
$billomat->invoices->list([
    'status' => ['OPEN', 'OVERDUE'],
]);
```

Ergibt:

```text
?status[]=OPEN&status[]=OVERDUE
```

Auf der Leitung stehen die Klammern percent-kodiert (`?status%5B%5D=OPEN&status%5B%5D=OVERDUE`), weil die PSR-7-URI `[` und `]` im Query kodiert. Billomat dekodiert das wie `status[]`; in Tests auf die kodierte Form prüfen.

`null` als Wert (sowohl Top-Level als auch innerhalb eines Arrays) wird stillschweigend übersprungen — der Schlüssel landet nicht im Query-String.

## Wertkonvertierung

`BillomatHttpClient::encodeBillomatQueryValue()` regelt das Casting der einzelnen Werte:

| PHP-Typ | Wire-Repräsentation |
|---|---|
| `bool` | `1` / `0` |
| `int`, `float` | `(string)` |
| `string` | unverändert |

Anschließend `rawurlencode`, danach die `%2B`-→-`+`-Korrektur.

Wenn du strukturierte Daten serialisieren willst (z. B. `DateTimeImmutable`), wandle sie vorher in einen String — sonst landet ein PHP-Object-Cast im Query-String.

## Binäre Responses

Manche Billomat-Endpunkte können binäre Daten zurückgeben — etwa PDFs oder Thumbnails. Zwei Patterns finden sich im SDK:

### Pattern A: `AbstractApi::getRaw()` für rein lesende Binärabrufe

Verwendet von `TemplatesApi::thumb()`:

```php
public function thumb(int $id, TemplateThumbFormat $format = TemplateThumbFormat::PNG): string
{
    return $this->getRaw("/templates/{$id}/thumb", [
        'format' => $format->value,
    ]);
}
```

`getRaw()` macht den Request und gibt `(string) $response->getBody()` zurück. HTTP-Fehler hat zu diesem Zeitpunkt bereits `BillomatHttpClient::request()` als SDK-Exception geworfen. Rückgabe ist der Binär-String.

### Pattern B: Endpoints mit Modus-Schalter

Verwendet von `InvoicesApi::pdf()`, weil derselbe Endpoint je nach `format=pdf` entweder JSON oder Binärdaten liefert. Die Methode ruft je nach Modus `getRaw(...)` (Binär) oder `getJson(...)` (für die strukturierte Variante) auf.

Beim Schreiben eigener Ressourcen orientiere dich an diesen zwei Patterns; ein generischer Helfer für „Endpoint mit Inhalt-Switch“ existiert (noch) nicht.

## Response-Hüllen entpacken

Billomat verpackt Antworten konsistent in eine Hülle:

- Liste: `{ "clients": { "client": [ {…}, {…} ] } }`
- Einzelobjekt: `{ "client": { … } }`

Eigenheit: Bei genau **einem** Listeneintrag liefert Billomat manchmal ein einzelnes Objekt statt einer einelementigen Liste. Jede `*Api::list()`-Implementierung normalisiert das per `array_is_list()`-Check (bzw. `isset($node['id'])`-Heuristik bei verschachtelten Strukturen wie Invoice Items). Wenn du eine neue Ressource hinzufügst, übernimm dieses Muster — siehe `ClientsApi::list()`, `InvoicesApi::list()`, `InvoiceItemsApi::listByInvoice()`.

## Was die Schicht **nicht** macht

- Kein proaktives Rate-Limiting: Das SDK drosselt nicht vorab anhand von `X-Rate-Limit-Remaining`, sondern reagiert erst auf 429.
- Keine Wiederholung von POST/PUT/DELETE außer bei 429 und keine Wiederholung bei 500.
- Kein Caching von GET-Responses.
- Keine Validierung des Payloads, bevor er Billomat erreicht. Das SDK schickt, was du angibst — Validierungsfehler liefert Billomat als `ValidationException` zurück, siehe [docs/error-handling.md](../error-handling.md).
