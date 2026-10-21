# Fehlerbehandlung

Alle vom SDK geworfenen Fehler erben von `Justpilot\Billomat\Exception\BillomatException`. Die HTTP-Fehler von Billomat werden auf eine kleine, spezialisierte Hierarchie abgebildet, damit du gezielt auf einzelne Fehlerklassen reagieren kannst.

## Exception-Hierarchie

```text
\RuntimeException
└── BillomatException
    ├── HttpException
    │   ├── AuthenticationException
    │   ├── ValidationException
    │   └── NotFoundException
    └── UnexpectedResponseException
```

| Klasse | Datei | Bedeutung |
|---|---|---|
| `BillomatException` | `src/Exception/BillomatException.php` | Basis-Marker. Fängst du diese, fängst du alles vom SDK. |
| `HttpException` | `src/Exception/HttpException.php` | Generischer HTTP-Fehler. Hält Status-Code, Roh-Body, vorhergehende Exception. |
| `AuthenticationException` | `src/Exception/AuthenticationException.php` | 401/403 — ungültiger oder fehlender API-Key, Berechtigung fehlt. |
| `NotFoundException` | `src/Exception/NotFoundException.php` | 404 — Ressource existiert nicht. |
| `ValidationException` | `src/Exception/ValidationException.php` | 400/422 — Payload abgelehnt, Pflichtfeld fehlt, Wert ungültig. |
| `UnexpectedResponseException` | `src/Exception/UnexpectedResponseException.php` | 2xx, aber der Body passt nicht zur erwarteten Struktur (seit 3.2). |

## Mapping HTTP-Status → Exception

`AbstractApi::mapHttpException()` ist die zentrale Übersetzungstabelle:

| HTTP-Status | Geworfene Exception |
|---|---|
| 401 Unauthorized | `AuthenticationException` |
| 403 Forbidden | `AuthenticationException` |
| 404 Not Found | `NotFoundException` |
| 400 Bad Request | `ValidationException` |
| 422 Unprocessable Entity | `ValidationException` |
| sonstige 4xx, 5xx | `HttpException` |

Jede dieser Exceptions trägt:

- `getStatusCode(): int` — den HTTP-Code von Billomat,
- `getResponseBody(): ?string` — den unveränderten Antwort-Body (oder `null`, falls er nicht gelesen werden konnte). Hilfreich, weil Billomat Validierungsdetails als JSON-Struktur mitschickt,
- `getPrevious(): ?\Throwable` — die ursprüngliche Symfony-Exception.

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
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Api\ClientCreateOptions;

$opts = new ClientCreateOptions();
$opts->name = 'Beispiel GmbH';
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
    // fängt jede SDK-Exception (HttpException + Subklassen + andere)
}
```

## Hinweise zu nicht-HTTP-Fehlern

API-Methoden werfen eine `UnexpectedResponseException`, wenn Billomat zwar mit 2xx antwortet, der Antwort-Body aber nicht zur erwarteten Struktur passt — z. B. wenn das Wrapper-Element `client` oder `invoice` fehlt. Das ist defensiv und sollte in der Praxis nicht auftreten; falls doch, lohnt sich ein Issue mit dem Roh-Body.

Bis einschließlich 3.1 war das eine einfache `\RuntimeException`. Da `UnexpectedResponseException` über `BillomatException` weiterhin von `\RuntimeException` erbt, greifen bestehende `catch (\RuntimeException)`-Blöcke unverändert.

Antwortet Billomat mit 2xx und einem Body, der kein gültiges JSON ist, wirft `json_decode()` eine `\JsonException`. Diese ist (noch) keine `BillomatException`.
