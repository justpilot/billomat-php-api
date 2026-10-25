# Testing

The SDK is tested with **PHPUnit 13**. There are two kinds of tests:

- **Unit tests** under `tests/` (except `tests/Integration/`) — mock the HTTP layer with Symfony's `MockHttpClient`, wrapped in `Psr18Client`. They need no credentials and run in any setup.
- **Integration tests** under `tests/Integration/` — talk to a real Billomat sandbox. They are skipped automatically without credentials.

## Running tests

All workflows run via Composer scripts — `composer.json` is the single source of truth:

```bash
composer install

composer test                                                # unit tests (fast, no network)
composer test:integration                                    # integration tests only
composer test:all                                            # all tests
composer test:coverage                                       # with HTML + Clover coverage in build/

vendor/bin/phpunit tests/Api/ClientsApiTest.php              # single file
vendor/bin/phpunit --filter it_lists_clients_and_passes_filters  # single method
```

The PHPUnit configuration lives in `phpunit.xml.dist`. Strict flags are enabled: `failOnWarning`, `failOnDeprecation`, `failOnNotice`, `failOnRisky`, `requireCoverageMetadata`, `beStrictAboutCoverageMetadata`. Every test therefore needs coverage metadata (`#[CoversClass]` or `#[CoversNothing]`).

## Bootstrap and credentials

`tests/bootstrap.php` loads two files via `symfony/dotenv`:

1. `.env.test` — committed template with empty values and inline comments documenting every expected environment variable (required vs. optional, which HTTP header it ends up in).

2. `.env.test.local` — overrides `.env.test` with real sandbox credentials. This file is listed in `.gitignore` and must never be committed.

The bootstrap calls `usePutenv()`, so the values are readable via `getenv()`, not only via `$_ENV`/`$_SERVER`. The integration tests need this because they read the credentials with `getenv('BILLOMAT_ID')`.

## Unit tests: the `MockHttpClient` pattern

Tests use the **PHPUnit 13 attribute style**: `#[Test]` on the method, `#[CoversClass]` on the class, static assertion calls (`self::assertX()`):

```php
use Justpilot\Billomat\Api\ClientsApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\Client;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
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
        $http = new BillomatHttpClient(new Psr18Client($mock), $config);
        $api = new ClientsApi($http);

        $result = $api->list(['per_page' => 50]);

        self::assertSame([], $result);
        self::assertSame('GET', $captured['method']);
        // …more assertions on URL/headers/payload
    }
}
```

Key points:

- **`#[Test]` instead of a `test_` prefix.** Method names read like sentences (`it_lists_clients_and_passes_filters`, `it_creates_a_new_client_via_post`).
- **`#[CoversClass(Foo::class)]` is required** — the strict PHPUnit configuration enforces it. Repeat the attribute for multiple classes.
- `BillomatHttpClient` is instantiated **directly** and connected to the mock. `MockHttpClient` is a Symfony client, not a PSR-18 client — hence `new Psr18Client($mock)`. Don't mock `BillomatHttpClient` itself: it contains the query string builder, error mapping and retry logic, which should stay under test.
- The `$captured` closure trick appears in many places — it allows assertions on method, URL, headers and payload. Because the request goes through PSR-18, `$options['body']` contains the JSON body as a string (check it with `json_decode()`) and `$options['headers']` is a list of `"Name: value"` strings; there is no `'json'` key. Array filters appear in the URL as `key%5B%5D=v`.
- `MockResponse` can also simulate HTTP errors (`['http_code' => 404]`) — useful for exception tests, see `tests/Api/AbstractApiExceptionTest.php` and `tests/Http/BillomatHttpClientErrorTest.php`.
- For retry tests (429, 502/503/504), pass `BillomatHttpClient` a `sleep:` closure (`Closure(int $ms): void`) that only records the delay — otherwise the test actually waits. Alternatively set `retry: RetryPolicy::none()` in the `BillomatConfig`.

A real, fully commented template is `tests/Api/ClientsApiTest.php`.

## Integration tests

Integration tests extend `Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase`. The base class provides two helpers:

- `createBillomatClientOrSkip(): BillomatClient` — reads `BILLOMAT_ID` and `BILLOMAT_API_KEY` from the environment, calls `markTestSkipped()` if either is missing, and otherwise builds a fully configured `BillomatClient`.
- `faker(): \Faker\Generator` — shared Faker instance (de_DE) for generating test data.

Integration tests carry `#[CoversNothing]` at class level (they test the full stack, not a single class) and `#[Group('integration')]` on the method:

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
        // … real API calls
    }
}
```

### Ground rules

- **Set `#[Group('integration')]`.** This lets you run the suite fully offline with `vendor/bin/phpunit --exclude-group integration`.
- **Delete test data afterwards.** Sandbox accounts fill up quickly otherwise. If a test creates a record, it should remove it at the end (`delete()` on the resource).
- **No production keys.** Use a separate sandbox account only; see [SECURITY.md](../SECURITY.md).
- **Keep the `markTestSkipped()` branch working.** A test that is meaningless without credentials must skip gracefully, otherwise the suite breaks elsewhere.

## Coverage

```bash
composer test:coverage
```

Creates an HTML report under `build/coverage/` and a Clover report (`build/coverage.xml`) for CI integrations such as Codecov or Coveralls. Requires Xdebug or pcov — the script sets `XDEBUG_MODE=coverage` automatically.

## Static analysis and code style

Besides the tests, these quality tools are available:

```bash
composer lint            # PHP-CS-Fixer (dry run, shows the diff)
composer lint:fix        # apply PHP-CS-Fixer
composer analyse         # PHPStan level max
composer refactor:dry    # show Rector suggestions
composer refactor        # apply Rector suggestions
composer mutate          # Infection mutation testing (~3 minutes)
composer generate:check  # fail if generated files under src/ are out of date

composer ci              # lint + analyse + refactor:dry + generate:check + all tests
```

PHPStan runs without a baseline: every finding fails CI and is fixed in the code.

## Continuous integration

`.github/workflows/ci.yml` runs three jobs on every push/PR:

1. **Tests** on PHP 8.5 (required) and 8.6 (experimental, `continue-on-error`).
2. **Quality** — `composer lint`, `composer analyse`, `composer refactor:dry`, `composer audit`.
3. **Mutation testing** — on PRs only the changed lines (`--git-diff-lines`, 90 % MSI); on `main`/`4.x` the full run with the thresholds from `infection.json5`.

Integration tests do **not** run automatically in CI, because no sandbox credentials are configured. If needed, `BILLOMAT_ID`/`BILLOMAT_API_KEY` can be set up as GitHub secrets and a separate workflow triggered.
