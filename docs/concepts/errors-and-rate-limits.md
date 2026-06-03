<!-- Quelle: https://www.billomat.com/api/grundlagen/fehler/ + https://www.billomat.com/api/grundlagen/zugriffsbegrenzung/ -->

# Fehler und Rate-Limits

Billomat antwortet bei Problemen mit einem `4xx`/`5xx`-Statuscode und einer kurzen Klartext-Meldung im Body. Dieses SDK übersetzt das in eine kleine Hierarchie typisierter Exceptions; alle erben von `BillomatException`.

## Status-Code-Mapping

`AbstractApi::mapHttpException()` (siehe `src/Api/AbstractApi.php`) übersetzt Symfonys `HttpExceptionInterface` in:

| Status | SDK-Exception |
|---|---|
| 401, 403 | `AuthenticationException` |
| 404 | `NotFoundException` |
| 400, 422 | `ValidationException` |
| 429 | `HttpException` (Rate-Limit) |
| Sonstige `4xx`/`5xx` | `HttpException` |

Der rohe Response-Body bleibt in der Exception erhalten (`getResponseBody()`), so dass die Billomat-Fehlermeldung sichtbar bleibt:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<errors>
    <error>Resource not found</error>
</errors>
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

Das SDK ruft beide nicht automatisch ab; bei Bedarf lassen sie sich über die Response-Header des darunter liegenden Symfony-`HttpClient` lesen.

### 429 — Limit überschritten

Wird das Kontingent im Fenster aufgebraucht, antwortet Billomat mit `429 Too Many Requests`. Die Fehlermeldung nennt eine Sekunden-Zahl bis zum Reset:

```xml
<error>Maximum number of requests reached. Try again in 246 seconds.</error>
```

Empfehlung: in eigenen Retry-Schleifen `X-Rate-Limit-Reset` auswerten, statt fest 15 Minuten zu warten — der nächste Fenster-Start liegt meist deutlich näher.

### Registrierte vs. unregistrierte App

| Modus | Kontingent | App-Header nötig |
|---|---|---|
| **unregistriert** | tariflich zugesicherte Anzahl Aufrufe pro 15 Min — **geteilt** über alle nicht registrierten Dienste auf dem Account | nein |
| **registriert** | volles Tarif-Kontingent **pro App pro Account** | `X-AppId` + `X-AppSecret` |

App-Registrierung erfolgt im Billomat-UI unter *Einstellungen → Administration → Apps*; das SDK reicht die Werte über `BillomatConfig` durch (siehe [Authentifizierung](authentication.md)).

### Webhooks sparen Aufrufe

Webhooks zählen **nicht** auf das Rate-Limit. Statt regelmässig Listen abzufragen lohnt sich ein Webhook-Empfänger pro relevantem Event (siehe [Webhooks](webhooks.md)).

## Eigene Retry-Strategie

Das SDK retryt selbst nicht. Wer das braucht, kann den `HttpClientInterface` per Konstruktor in `BillomatClient` austauschen und Symfonys `RetryableHttpClient` mit einer 429-aware Strategie davorhängen.
