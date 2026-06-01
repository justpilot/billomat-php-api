# Testing

Das SDK wird vollständig mit PHPUnit getestet. Es gibt zwei Test-Arten:

- **Unit-Tests** unter `tests/` (außer `tests/Integration/`) — mocken die HTTP-Ebene mit Symfonys `MockHttpClient`. Benötigen keine Credentials, laufen in jedem Setup.
- **Integrationstests** unter `tests/Integration/` — sprechen den realen Billomat-Sandbox an. Werden ohne Credentials automatisch übersprungen.

## Tests ausführen

```bash
composer install
vendor/bin/phpunit                                           # alle Tests
vendor/bin/phpunit --testdox                                 # Pretty-Output (default via phpunit.xml.dist)
vendor/bin/phpunit tests/Api/ClientsApiTest.php              # eine Datei
vendor/bin/phpunit --filter test_it_lists_clients_and_passes_filters  # eine Methode
vendor/bin/phpunit tests/Integration                         # nur Integrationstests
```

Die Konfiguration steht in `phpunit.xml.dist` (Bootstrap, Test-Suite-Definition, Coverage-Quelle).

## Bootstrap und Credentials

`tests/bootstrap.php` lädt zwei Dateien per `symfony/dotenv`:

1. `.env.test` — eingechecktes Template mit leeren Werten als Dokumentation der erwarteten Umgebungsvariablen:

   ```text
   BILLOMAT_ID=
   BILLOMAT_API_KEY=
   BILLOMAT_APP_ID=
   BILLOMAT_APP_SECRET=
   ```

2. `.env.test.local` — überschreibt `.env.test` mit echten Sandbox-Credentials. Diese Datei ist in `.gitignore` aufgeführt und darf niemals committet werden.

Der Bootstrap ruft `usePutenv()` auf — die Werte werden also via `getenv()` lesbar, nicht nur in `$_ENV`/`$_SERVER`. Das ist nötig, weil die Integrationstests die Credentials per `getenv('BILLOMAT_ID')` abholen.

## Unit-Tests: Muster mit `MockHttpClient`

Das gängige Muster aller Unit-Tests sieht so aus:

```php
use Justpilot\Billomat\Api\ClientsApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class MyApiTest extends TestCase
{
    public function test_it_lists_things(): void
    {
        $captured = [];

        $mock = new MockHttpClient(function (string $method, string $url, array $options) use (&$captured) {
            $captured['method'] = $method;
            $captured['url'] = $url;
            $captured['options'] = $options;

            return new MockResponse(
                json_encode(['clients' => ['client' => []]], JSON_THROW_ON_ERROR),
                ['http_code' => 200],
            );
        });

        $config = new BillomatConfig(billomatId: 'mycompany', apiKey: 'secret-key');
        $http = new BillomatHttpClient($mock, $config);
        $api = new ClientsApi($http);

        $result = $api->list(['per_page' => 50]);

        self::assertSame([], $result);
        self::assertSame('GET', $captured['method']);
        // …weitere Assertions auf URL/Header/Payload
    }
}
```

Wichtige Punkte:

- `BillomatHttpClient` wird **direkt** instanziiert und mit dem Mock verbunden. Nicht den `BillomatHttpClient` selbst mocken — er enthält den Query-String-Builder, der unter Test bleiben soll.
- Den `$captured`-Trick aus dem Closure-Argument findest du an vielen Stellen — er erlaubt Assertions über die ausgehende Methode, URL, Header und den Payload (`$options['json']` oder `$options['body']`).
- `MockResponse` kann auch HTTP-Fehler simulieren (`['http_code' => 404]`) — nützlich für Exception-Tests, siehe `tests/Api/AbstractApiExceptionTest.php`.

Eine reale, vollständig kommentierte Vorlage steht in `tests/Api/ClientsApiTest.php`.

## Integrationstests

Integrationstests erben von `Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase`. Diese Basisklasse stellt zwei Helfer bereit:

- `createBillomatClientOrSkip(): BillomatClient` — liest `BILLOMAT_ID` und `BILLOMAT_API_KEY` aus den Umgebungsvariablen, ruft `markTestSkipped()`, wenn etwas fehlt, und baut sonst einen voll konfigurierten `BillomatClient`.
- `faker(): \Faker\Generator` — gemeinsamer Faker (de_DE) zur Erzeugung von Testdaten.

Vorlage:

```php
namespace Justpilot\Billomat\Tests\Integration\MyResource;

use Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase;

final class MyIntegrationTest extends AbstractBillomatIntegrationTestCase
{
    /**
     * @group integration
     */
    public function test_can_do_thing_against_sandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        // … reale API-Calls
    }
}
```

### Schutzregeln

- **`@group integration` setzen.** Damit lässt sich die Suite per `phpunit --exclude-group integration` rein offline laufen lassen.
- **Daten nach dem Test wieder löschen.** Sandbox-Accounts müllen sonst schnell zu. Wer einen Datensatz anlegt, sollte ihn am Ende wieder entfernen (`delete()` auf der jeweiligen Ressource).
- **Keine Produktiv-Keys.** Verwende ausschließlich einen separaten Sandbox-Account; siehe [SECURITY.md](../SECURITY.md).
- **`markTestSkipped()`-Branch korrekt halten.** Wenn ein Test ohne Credentials nicht aussagekräftig ist, muss er gracefully geskippt werden, sonst bricht die Suite anderswo.

## Coverage

`phpunit.xml.dist` deklariert das Quellverzeichnis (`src/`) als Coverage-Source. Coverage selbst wird nicht standardmäßig erzeugt — bei Bedarf:

```bash
XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-html build/coverage
```

(Setzt voraus, dass Xdebug oder pcov installiert sind.)
