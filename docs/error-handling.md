# Fehlerbehandlung

Alle vom SDK geworfenen Fehler erben von `Justpilot\Billomat\Exception\BillomatException`. Die HTTP-Fehler von Billomat werden auf eine kleine, spezialisierte Hierarchie abgebildet, damit du gezielt auf einzelne Fehlerklassen reagieren kannst.

## Exception-Hierarchie

```text
\RuntimeException
└── BillomatException
    ├── HttpException
    │   ├── AuthenticationException
    │   ├── ValidationException
    │   ├── NotFoundException
    │   └── RateLimitException
    ├── TransportException
    └── UnexpectedResponseException
```

| Klasse | Datei | Bedeutung |
|---|---|---|
| `BillomatException` | `src/Exception/BillomatException.php` | Basis-Marker. Fängst du diese, fängst du alles vom SDK. |
| `HttpException` | `src/Exception/HttpException.php` | Generischer HTTP-Fehler. Hält Status-Code und Roh-Body. |
| `AuthenticationException` | `src/Exception/AuthenticationException.php` | 401/403 — ungültiger oder fehlender API-Key, Berechtigung fehlt. |
| `NotFoundException` | `src/Exception/NotFoundException.php` | 404 — Ressource existiert nicht. |
| `ValidationException` | `src/Exception/ValidationException.php` | 400/422 — Payload abgelehnt, Pflichtfeld fehlt, Wert ungültig. |
| `RateLimitException` | `src/Exception/RateLimitException.php` | 429 — Rate-Limit erschöpft und die Wartezeit liegt über `RetryPolicy::$maxDelayMs` (bzw. alle Versuche verbraucht). `getRetryAfter(): ?int` nennt die Sekunden bis zum nächsten Fenster. |
| `TransportException` | `src/Exception/TransportException.php` | Keine Antwort von Billomat (DNS, Verbindungsabbruch, Timeout). Die Exception des PSR-18-Clients steckt in `getPrevious()`. |
| `UnexpectedResponseException` | `src/Exception/UnexpectedResponseException.php` | 2xx, aber der Body passt nicht zur erwarteten Struktur (seit 3.2). |

## Mapping HTTP-Status → Exception

`BillomatHttpClient::request()` (siehe `src/Http/BillomatHttpClient.php`) übersetzt jede Antwort mit Status ≥ 400 in eine SDK-Exception — erst nachdem die automatischen Wiederholungen (siehe [HTTP-Layer](advanced/http-layer.md#retries-und-rate-limits)) ausgeschöpft sind:

| HTTP-Status | Geworfene Exception |
|---|---|
| 401 Unauthorized | `AuthenticationException` |
| 403 Forbidden | `AuthenticationException` |
| 404 Not Found | `NotFoundException` |
| 400 Bad Request | `ValidationException` |
| 422 Unprocessable Entity | `ValidationException` |
| 429 Too Many Requests | `RateLimitException` |
| sonstige 4xx, 5xx | `HttpException` |
| keine Antwort (Netzwerkfehler) | `TransportException` |

Jede `HttpException` (inkl. Subklassen) trägt:

- `getMessage(): string` — z. B. `Billomat responded with HTTP 422 to POST /clients. <Fehlertext von Billomat>`. Den Fehlertext liest das SDK aus `{"errors": {"error": …}}` (JSON) bzw. `<error>…</error>` (XML); fehlt er, endet die Message nach dem Pfad,
- `getStatusCode(): int` — den HTTP-Code von Billomat (identisch mit `getCode()`),
- `getResponseBody(): ?string` — den unveränderten Antwort-Body. Hilfreich, weil Billomat Validierungsdetails als JSON-Struktur mitschickt.

## Sonderfall: `get($id)` gibt `null` statt 404

Methoden wie `ClientsApi::get(int $id)`, `InvoicesApi::get(int $id)` etc. nutzen intern `AbstractApi::getJsonOrNull()`. Diese fängt eine 404-Antwort und gibt stattdessen `null` zurück. Du musst also bei „Datensatz existiert nicht“ **nicht** `try/catch` schreiben:

```php
$client = $billomat->clients->get(99999);

if ($client === null) {
    // Kunde existiert nicht — kein Fehler, einfach behandeln
    echo "Kein Kunde mit ID 99999 vorhanden.\n";
    return;
}

echo $client->name;
```

Andere 4xx/5xx-Antworten (z. B. 401 bei abgelaufenem Key) werfen weiterhin wie gewohnt eine Exception.

## Patterns

### Fehler differenziert behandeln

```php
use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\RateLimitException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Api\ClientCreateOptions;

$opts = new ClientCreateOptions(name: 'Beispiel GmbH');
// (vergessen: email, falls Pflicht im Account)

try {
    $client = $billomat->clients->create($opts);
} catch (AuthenticationException $e) {
    // API-Key falsch, fehlend oder verfallen
    error_log('Billomat-Auth fehlgeschlagen: ' . $e->getMessage());
    throw $e;
} catch (ValidationException $e) {
    // Billomat hat den Payload abgelehnt — Details im Body
    error_log(sprintf(
        'Validierung fehlgeschlagen (HTTP %d): %s',
        $e->getStatusCode(),
        $e->getResponseBody() ?? '(kein Body)',
    ));
    throw $e;
} catch (RateLimitException $e) {
    // Limit erschöpft, SDK hat nicht länger gewartet
    $wait = $e->getRetryAfter(); // Sekunden oder null
    throw $e;
} catch (HttpException $e) {
    // Alles andere (500er, unbekannte 4xx)
    error_log(sprintf('Billomat-HTTP-Fehler %d', $e->getStatusCode()));
    throw $e;
}
```

### Validierungsdetails extrahieren

Billomat liefert bei 400/422 in der Regel JSON mit Details. Der Body ist roh als String verfügbar; du parst ihn nach Bedarf:

```php
use Justpilot\Billomat\Exception\ValidationException;

try {
    $billomat->invoices->create($options);
} catch (ValidationException $e) {
    $body = $e->getResponseBody();

    if ($body !== null) {
        $decoded = json_decode($body, true);
        // Struktur hängt vom Endpoint ab; Billomat liefert oft
        // { "errors": { "error": "Field X is missing" } }
        var_dump($decoded);
    }

    throw $e;
}
```

### Alles auf einmal abfangen

Wenn du nur „irgendetwas mit Billomat ging schief“ behandeln willst:

```php
use Justpilot\Billomat\Exception\BillomatException;

try {
    $billomat->invoices->complete($id);
} catch (BillomatException $e) {
    // fängt jede SDK-Exception (HttpException + Subklassen, TransportException, …)
}
```

## Hinweise zu nicht-HTTP-Fehlern

API-Methoden werfen eine `UnexpectedResponseException`, wenn Billomat zwar mit 2xx antwortet, der Antwort-Body aber nicht zur erwarteten Struktur passt — z. B. wenn das Wrapper-Element `client` oder `invoice` fehlt. Das ist defensiv und sollte in der Praxis nicht auftreten; falls doch, lohnt sich ein Issue mit dem Roh-Body.

Bis einschließlich 3.1 war das eine einfache `\RuntimeException`. Da `UnexpectedResponseException` über `BillomatException` weiterhin von `\RuntimeException` erbt, greifen bestehende `catch (\RuntimeException)`-Blöcke unverändert.

Auch ein 2xx-Body, der kein gültiges JSON oder kein JSON-Objekt ist, führt zu einer `UnexpectedResponseException` (die ursprüngliche `\JsonException` steckt in `getPrevious()`).

Kommt gar keine Antwort zurück (DNS-Fehler, Verbindungsabbruch, Timeout), wirft das SDK eine `TransportException`. GET-Requests werden vorher gemäß `RetryPolicy` wiederholt, POST/PUT/DELETE nicht.
