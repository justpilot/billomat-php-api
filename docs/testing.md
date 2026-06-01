# Testing

Das SDK wird vollständig mit **PHPUnit 12** getestet. Es gibt zwei Test-Arten:

- **Unit-Tests** unter `tests/` (außer `tests/Integration/`) — mocken die HTTP-Ebene mit Symfonys `MockHttpClient`. Benötigen keine Credentials, laufen in jedem Setup.
- **Integrationstests** unter `tests/Integration/` — sprechen den realen Billomat-Sandbox an. Werden ohne Credentials automatisch übersprungen.

## Tests ausführen

Alle Workflows laufen über Composer-Skripte — `composer.json` ist die Single Source of Truth:

```bash
composer install

composer test                                                # Unit-Tests (schnell, kein Netz)
composer test:integration                                    # nur Integrationstests
composer test:all                                            # alle Tests
composer test:coverage                                       # mit HTML+Clover-Coverage in build/

vendor/bin/phpunit tests/Api/ClientsApiTest.php              # einzelne Datei
vendor/bin/phpunit --filter it_lists_clients_and_passes_filters  # einzelne Methode
```

Die PHPUnit-Konfiguration steht in `phpunit.xml.dist`. Strikte Flags sind aktiv: `failOnWarning`, `failOnDeprecation`, `failOnNotice`, `failOnRisky`, `requireCoverageMetadata`, `beStrictAboutCoverageMetadata`. Jeder Test muss daher Coverage-Metadaten tragen (`#[CoversClass]` oder `#[CoversNothing]`).

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

Tests folgen dem **PHPUnit-12-Attribut-Stil**: `#[Test]` auf der Methode, `#[CoversClass]` auf der Klasse, statische Assertion-Aufrufe (`self::assertX()`):

```php
use Justpilot\Billomat\Api\ClientsApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\Client;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(ClientsApi::class)]
#[CoversClass(Client::class)]
final class MyApiTest extends TestCase
{
    #[Test]
    public function it_lists_things(): void
    {
        $captured = [];

        $mock = new MockHttpClient(function (string $method, string $url, array $options) use (&$captured): MockResponse {
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

- **`#[Test]` statt `test_`-Präfix.** Methodennamen lesen sich wie Sätze (`it_lists_clients_and_passes_filters`, `it_creates_a_new_client_via_post`).
- **`#[CoversClass(Foo::class)]` ist Pflicht** — die strikte PHPUnit-Konfiguration erzwingt sie. Mehrere Klassen einfach mehrfach attributieren.
- `BillomatHttpClient` wird **direkt** instanziiert und mit dem Mock verbunden. Nicht den `BillomatHttpClient` selbst mocken — er enthält den Query-String-Builder, der unter Test bleiben soll.
- Der `$captured`-Trick aus dem Closure-Argument findet sich an vielen Stellen — er erlaubt Assertions über Methode, URL, Header und Payload (`$options['json']` oder `$options['body']`).
- `MockResponse` kann auch HTTP-Fehler simulieren (`['http_code' => 404]`) — nützlich für Exception-Tests, siehe `tests/Api/AbstractApiExceptionTest.php`.

Eine reale, vollständig kommentierte Vorlage steht in `tests/Api/ClientsApiTest.php`.

## Integrationstests

Integrationstests erben von `Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase`. Diese Basisklasse stellt zwei Helfer bereit:

- `createBillomatClientOrSkip(): BillomatClient` — liest `BILLOMAT_ID` und `BILLOMAT_API_KEY` aus den Umgebungsvariablen, ruft `markTestSkipped()`, wenn etwas fehlt, und baut sonst einen voll konfigurierten `BillomatClient`.
- `faker(): \Faker\Generator` — gemeinsamer Faker (de_DE) zur Erzeugung von Testdaten.

Integrationstests tragen `#[CoversNothing]` auf Klassenebene (sie testen den vollen Stack, nicht eine einzelne Klasse) und `#[Group('integration')]` auf der Methode:

```php
namespace Justpilot\Billomat\Tests\Integration\MyResource;

use Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversNothing]
final class MyIntegrationTest extends AbstractBillomatIntegrationTestCase
{
    #[Test]
    #[Group('integration')]
    public function can_do_thing_against_sandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        // … reale API-Calls
    }
}
```

### Schutzregeln

- **`#[Group('integration')]` setzen.** Damit lässt sich die Suite per `vendor/bin/phpunit --exclude-group integration` rein offline laufen lassen.
- **Daten nach dem Test wieder löschen.** Sandbox-Accounts müllen sonst schnell zu. Wer einen Datensatz anlegt, sollte ihn am Ende wieder entfernen (`delete()` auf der jeweiligen Ressource).
- **Keine Produktiv-Keys.** Verwende ausschließlich einen separaten Sandbox-Account; siehe [SECURITY.md](../SECURITY.md).
- **`markTestSkipped()`-Branch korrekt halten.** Wenn ein Test ohne Credentials nicht aussagekräftig ist, muss er gracefully geskippt werden, sonst bricht die Suite anderswo.

## Coverage

```bash
composer test:coverage
```

Erzeugt einen HTML-Report unter `build/coverage/` und einen Clover-Report (`build/coverage.xml`) für CI-Integrationen wie Codecov oder Coveralls. Setzt Xdebug oder pcov voraus — das Skript setzt `XDEBUG_MODE=coverage` automatisch.

## Statische Analyse und Code-Style

Neben Tests laufen vier weitere Qualitätswerkzeuge:

```bash
composer lint        # PHP-CS-Fixer (Dry-Run, zeigt Diff)
composer lint:fix    # PHP-CS-Fixer automatisch anwenden
composer analyse     # PHPStan Level max
composer refactor:dry # Rector zeigt Vorschläge
composer refactor    # Rector wendet Vorschläge an
composer mutate      # Infection Mutation-Testing (langsam)

composer ci          # Lint + Analyse + Refactor-Dry + alle Tests
```

PHPStan nutzt eine Baseline (`phpstan-baseline.neon`), die bestehende Findings einfriert. Neue Findings führen zu CI-Fail. Beim Beheben Baseline neu generieren:

```bash
composer analyse:baseline
```

## Continuous Integration

`.github/workflows/ci.yml` führt bei jedem Push/PR drei Jobs aus:

1. **Tests** auf PHP 8.4 (Pflicht) und 8.5 (experimentell, `continue-on-error`).
2. **Quality** — `composer lint`, `composer analyse`, `composer refactor:dry`, `composer audit`.
3. **Mutation Testing** (nur PRs) — `composer mutate`.

Integrationstests laufen in CI **nicht** automatisch, da keine Sandbox-Credentials hinterlegt sind. Bei Bedarf können `BILLOMAT_ID`/`BILLOMAT_API_KEY` als GitHub-Secrets eingerichtet und ein separater Workflow getriggert werden.
