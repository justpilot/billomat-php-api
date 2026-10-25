<!-- Quelle: https://www.billomat.com/api/grundlagen/fehler/ + https://www.billomat.com/api/grundlagen/zugriffsbegrenzung/ -->

# Fehler und Rate-Limits

Billomat antwortet bei Problemen mit einem `4xx`/`5xx`-Statuscode und einer kurzen Klartext-Meldung im Body. Dieses SDK übersetzt das in eine kleine Hierarchie typisierter Exceptions; alle erben von `BillomatException`.

## Status-Code-Mapping

`BillomatHttpClient::request()` (siehe `src/Http/BillomatHttpClient.php`) übersetzt jede Antwort mit Status ≥ 400 — nach eventuellen automatischen Wiederholungen — in:

| Status | SDK-Exception |
|---|---|
| 401, 403 | `AuthenticationException` |
| 404 | `NotFoundException` |
| 400, 422 | `ValidationException` |
| 429 | `RateLimitException` (erbt von `HttpException`) |
| Sonstige `4xx`/`5xx` | `HttpException` |
| keine Antwort (DNS, Timeout, Verbindungsabbruch) | `TransportException` (PSR-18-Exception in `getPrevious()`) |

Die Billomat-Fehlermeldung landet in der Exception-Message, z. B. `Billomat responded with HTTP 404 to GET /invoices/1. Resource not found`. Der rohe Response-Body bleibt zusätzlich erhalten (`getResponseBody()`):

```xml
<?xml version="1.0" encoding="UTF-8"?>
<errors>
    <error>Resource not found</error>
</errors>
```

## Fehler klassifizieren

Jede `BillomatException` beantwortet die zwei Fragen, die ein Aufrufer meist stellt, ohne dass er Statuscodes oder `getPrevious()` auswerten muss:

| Fehler | `isTransient()` | `mayHaveBeenProcessed()` | `getRetryAfter()` |
|---|---|---|---|
| 400, 401, 403, 404, 422, sonstige 4xx | `false` | `false` | `null` |
| 429 | `true` | `false` | `Retry-After` bzw. `X-Rate-Limit-Reset` |
| 502, 503, 504 | `true` | `true` | `Retry-After`, falls gesetzt |
| 500 und sonstige 5xx | `false` | `true` | `null` |
| `TransportException` (Netzwerkfehler, Timeout) | `true` | `true` | – |
| `UnexpectedResponseException` (2xx mit unerwartetem Body) | `false` | `true` | – |

- **`isTransient()`**: Ein späterer Versuch kann Erfolg haben. Die automatischen Retries des SDK sind dann schon ausgeschöpft; sinnvoll ist ein späterer Neuversuch, z. B. per Queue.
- **`mayHaveBeenProcessed()`**: Billomat kann den Request trotz des Fehlers verarbeitet haben. Vor einem erneuten POST per `get()`/`list()` prüfen, ob die Rechnung schon existiert. Bei `false` (4xx) hat Billomat den Request sicher abgelehnt.

```php
use Justpilot\Billomat\Exception\BillomatException;
use Justpilot\Billomat\Exception\HttpException;

try {
    $invoice = $billomat->invoices->create($options);
} catch (BillomatException $e) {
    if ($e->mayHaveBeenProcessed()) {
        // Erst nachsehen, ob die Rechnung schon angelegt wurde.
    }
    if ($e->isTransient()) {
        $job->releaseIn($e instanceof HttpException ? $e->getRetryAfter() ?? 60 : 60);
    }
}
```

## `get($id)` mit 404

`AbstractApi::getJsonOrNull()` schluckt `NotFoundException` und liefert `null` zurück. Praktisch:

```php
$invoice = $billomat->invoices->get(999_999); // null statt Exception
if ($invoice === null) { /* nicht vorhanden */ }
```

Alle anderen 404 (z.B. unbekannter Endpunkt) fliegen weiter.

## Rate-Limits

Die API zählt Aufrufe in **15-Minuten-Fenstern**, festgelegt auf `XX:00–XX:14`, `XX:15–XX:29`, `XX:30–XX:44`, `XX:45–XX:59` (Server-Zeit, nicht Wall-Clock vom Client). Nicht verbrauchte Aufrufe verfallen am Fensterwechsel — Kontingent ist nicht ansparbar.

### Header bei jeder Antwort

| Header | Bedeutung |
|---|---|
| `X-Rate-Limit-Remaining` | verbleibende Aufrufe im aktuellen Fenster |
| `X-Rate-Limit-Reset` | Epoch-Sekunden des nächsten Fensterstarts |

Das SDK wertet `X-Rate-Limit-Reset` bei einer 429-Antwort aus (siehe unten). `X-Rate-Limit-Remaining` liest es nicht — wer vorab drosseln will, kann die Header mit einem Decorator um den PSR-18-Client herum mitlesen.

### 429 — Limit überschritten

Wird das Kontingent im Fenster aufgebraucht, antwortet Billomat mit `429 Too Many Requests`. Die Fehlermeldung nennt eine Sekunden-Zahl bis zum Reset:

```xml
<error>Maximum number of requests reached. Try again in 246 seconds.</error>
```

Das SDK wartet dann selbst: Es nimmt die Wartezeit aus `Retry-After` (falls gesetzt) bzw. `X-Rate-Limit-Reset` und wiederholt den Request — bei 429 jede Methode, auch POST/PUT/DELETE, weil Billomat den Request nicht verarbeitet hat. Das passiert aber nur, wenn die Wartezeit höchstens `RetryPolicy::$maxDelayMs` (Default 10 s) beträgt und noch Versuche übrig sind. Sonst wirft das SDK sofort eine `RateLimitException`; kommt sie beim Aufrufer an, sind die Retries ausgeschöpft. `getRetryAfter()` nennt die Sekunden bis zum nächsten Fenster (oder `null`, wenn Billomat keine Angabe gemacht hat):

```php
use Justpilot\Billomat\Exception\RateLimitException;

try {
    $billomat->invoices->list();
} catch (RateLimitException $e) {
    $job->releaseIn($e->getRetryAfter() ?? 60); // z. B. Queue-Job später erneut einplanen
}
```

### Registrierte vs. unregistrierte App

| Modus | Kontingent | App-Header nötig |
|---|---|---|
| **unregistriert** | tariflich zugesicherte Anzahl Aufrufe pro 15 Min — **geteilt** über alle nicht registrierten Dienste auf dem Account | nein |
| **registriert** | volles Tarif-Kontingent **pro App pro Account** | `X-AppId` + `X-AppSecret` |

App-Registrierung erfolgt im Billomat-UI unter *Einstellungen → Administration → Apps*; das SDK reicht die Werte über `BillomatConfig` durch (siehe [Authentifizierung](authentication.md)).

### Webhooks sparen Aufrufe

Webhooks zählen **nicht** auf das Rate-Limit. Statt regelmäßig Listen abzufragen lohnt sich ein Webhook-Empfänger pro relevantem Event (siehe [Webhooks](webhooks.md)).

## Automatische Wiederholungen

`BillomatConfig::$retry` (`RetryPolicy`, Default: `maxRetries: 2`, `baseDelayMs: 500`, `maxDelayMs: 10_000`) legt fest, was das SDK wiederholt:

| Fall | Methoden | Wartezeit |
|---|---|---|
| 429 | alle | `Retry-After` bzw. `X-Rate-Limit-Reset`; länger als `maxDelayMs` → sofort `RateLimitException` |
| 502, 503, 504 | nur GET | exponentielles Backoff mit Jitter |
| Netzwerkfehler | nur GET | exponentielles Backoff mit Jitter |
| 500, sonstige Fehler | — | kein Retry |

POST/PUT/DELETE werden außer bei 429 nie wiederholt, weil sie bei Billomat nicht idempotent sind. Wer eine eigene Strategie braucht (z. B. längeres Warten auf das nächste Fenster in einem Hintergrund-Job), schaltet die eingebauten Wiederholungen mit `RetryPolicy::none()` ab oder erhöht `maxDelayMs` — siehe [Konfiguration](../configuration.md#retries-retrypolicy).
