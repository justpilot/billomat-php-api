# HTTP-Layer

Die HTTP-Schicht des SDK ist absichtlich dünn. Sie kapselt drei Aufgaben: Authentifizierungs-Header setzen, URL und Query-String bauen, JSON-Body serialisieren. Dieses Dokument beschreibt die wenigen, aber wichtigen Eigenheiten — und wie du eingreifen kannst.

## Klassen und Verantwortlichkeiten

| Klasse | Aufgabe | Datei |
|---|---|---|
| `BillomatHttpClientInterface` | Vertrag: `request(method, path, query, json): ResponseInterface` | `src/Http/BillomatHttpClientInterface.php` |
| `BillomatHttpClient` | Konkrete Implementierung. Bekommt `HttpClientInterface` + `BillomatConfig` injiziert. | `src/Http/BillomatHttpClient.php` |
| `AbstractApi` | Wrapper über `BillomatHttpClientInterface` mit JSON-Decoding und Exception-Mapping. | `src/Api/AbstractApi.php` |

`BillomatClient` instanziiert intern ein `BillomatHttpClient` und reicht es an alle `*Api`-Klassen weiter. Eine eigene Implementierung von `BillomatHttpClientInterface` lässt sich aktuell nicht ohne Verzicht auf die Factory `BillomatClient::create()` einhängen — wer das braucht, instanziert `BillomatClient` manuell und kann den darin gehaltenen `BillomatHttpClient` durch die Symfony-Layer ersetzen (siehe nächster Abschnitt).

## Symfony-`HttpClientInterface` austauschen

Der schnelle Hebel: nicht den `BillomatHttpClient` ersetzen, sondern den darunterliegenden Symfony-Client. Beide Konstruktoren akzeptieren ihn:

```php
use Justpilot\Billomat\BillomatClient;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\RetryableHttpClient;
use Symfony\Component\HttpClient\TraceableHttpClient;

$inner = HttpClient::create([
    'max_duration' => 30.0,
]);

$decorated = new TraceableHttpClient(new RetryableHttpClient($inner));

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
    httpClient: $decorated,
);
```

Mit diesem Pattern bekommst du Logging, Tracing, Retry, Auth-Decoration etc., ohne SDK-internen Code zu ändern.

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

`getRaw()` macht den Request, ruft `$response->getContent()` auf und mappt HTTP-Fehler in SDK-Exceptions. Rückgabe ist der Binär-String.

### Pattern B: Direkter Request für Endpoints mit Modus-Schalter

Verwendet von `InvoicesApi::pdf()`, weil derselbe Endpoint je nach `format=pdf` entweder JSON oder Binärdaten liefert. In dem Fall ruft die Methode `$this->http->request(...)` direkt auf und ruft entweder `getContent()` (Binär) oder `getJson(...)` (für die strukturierte Variante).

Beim Schreiben eigener Ressourcen orientiere dich an diesen zwei Patterns; ein generischer Helfer für „Endpoint mit Inhalt-Switch“ existiert (noch) nicht.

## Response-Hüllen entpacken

Billomat verpackt Antworten konsistent in eine Hülle:

- Liste: `{ "clients": { "client": [ {…}, {…} ] } }`
- Einzelobjekt: `{ "client": { … } }`

Eigenheit: Bei genau **einem** Listeneintrag liefert Billomat manchmal ein einzelnes Objekt statt einer einelementigen Liste. Jede `*Api::list()`-Implementierung normalisiert das per `array_is_list()`-Check (bzw. `isset($node['id'])`-Heuristik bei verschachtelten Strukturen wie Invoice Items). Wenn du eine neue Ressource hinzufügst, übernimm dieses Muster — siehe `ClientsApi::list()`, `InvoicesApi::list()`, `InvoiceItemsApi::listByInvoice()`.

## Was die Schicht **nicht** macht

- Kein Rate-Limiting, kein Backoff out-of-the-box. Wenn nötig, wickle den Symfony-Client mit `RetryableHttpClient` ein.
- Keine automatischen Wiederholungen bei Idempotenz-fähigen Operationen.
- Kein Caching von GET-Responses.
- Keine Validierung des Payloads, bevor er Billomat erreicht. Das SDK schickt, was du angibst — Validierungsfehler liefert Billomat als `ValidationException` zurück, siehe [docs/error-handling.md](../error-handling.md).
