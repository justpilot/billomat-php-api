# Contributing

Thanks for contributing to `justpilot/billomat-php-api`. This document describes the workflow, the coding standards and what we expect from pull requests.

## Language

- **Code comments and docblocks are German** (with real umlauts: ä/ö/ü/ß). Identifiers (classes, methods, properties) are English.
- **Everything else is English:** commit messages, pull requests, issues, `CHANGELOG.md`, `UPGRADE-*.md`, README.

## Workflow

1. Open an issue first (bug fixes included), so scope and approach are agreed on before you write code.
2. Fork the repository and create a branch with a descriptive name, e.g. `feat/add-articles-resource` or `fix/payment-list-pagination`.
3. Keep commits small and focused. Commit messages are English, in the imperative ("Add articles list endpoint"); we use [Conventional Commits](https://www.conventionalcommits.org/) prefixes (`feat:`, `fix:`, `docs:`, `test:`, `chore:`, `feat!:` for breaking changes).
4. Open the pull request against **`4.x`** (the branch for 4.0; `main` only receives 3.x fixes until 4.0 is released). Explain the why, not only the what, and link the issue.
5. `composer ci` must pass locally before you request a review.

## Coding standards

- **PHP 8.5+.** Use modern language features: `final readonly` classes for read models and options, constructor property promotion, named arguments, enums, `clone()` with properties for withers.
- **`declare(strict_types=1);`** at the top of every PHP file.
- **`final`** for API classes, **`final readonly`** for models in `src/Model/` and write options.
- **PSR-4:** `Justpilot\Billomat\` → `src/`, `Justpilot\Billomat\Tests\` → `tests/`.
- **No global state.** Configuration goes through `BillomatConfig`, HTTP through `BillomatHttpClient` / `BillomatHttpClientInterface`.
- **Runtime dependencies are PSR interfaces only** (plus `php-http/discovery`). Never add a concrete HTTP client or framework package to `require`.
- **Amounts are `Money`**, never `float`. Fixed value sets are enums in `src/Model/Enum/` (with the `FromApi` trait).

[AGENTS.md](AGENTS.md) describes the architecture and all conventions in detail; it's written for AI coding agents but is the most complete reference for humans, too.

## Three layers per resource

Every Billomat resource follows the same pattern; look at `ClientsApi`, `ClientCreateOptions`, `ClientUpdateOptions` and `Model\Client` for an example.

1. **`src/Api/{Resource}Api.php`** extends `AbstractApi` and exposes the verbs (`list`, `get`, `getOrFail`, `create`, `update`, `delete`) plus resource-specific actions. It unwraps Billomat's envelope (`{"clients": {"client": [...]}}` or `{"client": {...}}`).
2. **`src/Api/{Resource}CreateOptions.php` / `{Resource}UpdateOptions.php`**: immutable write options (`final readonly`, named arguments, `with*()` withers, `Clear::Field` to empty a field in updates).
3. **`src/Model/{Resource}.php`**: `final readonly` read model with `fromArray()` and `toArray()`.

**Most models, options and list filters are generated** by `tools/generate.php` from `docs/spec/billomat.json` and a definition in `tools/codegen/definitions/`. Their class docblock says "Generiert aus …". Never edit them by hand: change the definition and run `composer generate`. CI runs `composer generate:check`.

## Adding a resource

1. Add a codegen definition (or the handwritten classes for resources that don't fit) and run `composer generate`.
2. Write the `*Api` class by hand and add it as a lazy property hook on `BillomatClient` (same pattern as the other APIs).
3. New enums go into `src/Model/Enum/` (`enum X: string { use FromApi; case … }`, optionally `label()`).
4. Add unit tests in `tests/Api/{Resource}ApiTest.php` (see below) and optionally an integration test under `tests/Integration/{Resource}/`.
5. Add a documentation page under `docs/resources/<resource-name>.md` and add it to the table in `docs/resources/README.md`.

## Tests

- Unit tests use `Symfony\Component\HttpClient\MockHttpClient` with `MockResponse`, wrapped in `Psr18Client`: build a `BillomatConfig`, then `new BillomatHttpClient(new Psr18Client($mock), $config)`, then the `*Api` class. Don't mock `BillomatHttpClient` itself. Retry tests pass a `sleep:` closure so they don't actually wait.
- Assert both the hydrated model and the outgoing request (method, URL, headers, payload).
- **PHPUnit 13 attributes:** `#[Test]` on every test method (no `test_` prefix), `#[CoversClass(Foo::class)]` on the class, static assertions (`self::assertX()`).
- Generated code is covered by reflection-based tests (`tests/Model/ModelRoundTripTest.php`, `tests/Api/Options/GeneratedOptionsTest.php`); it needs no per-class tests for its field mapping.
- Integration tests live under `tests/Integration/`, extend `AbstractBillomatIntegrationTestCase`, use `createBillomatClientOrSkip()` and carry `#[CoversNothing]`. They are skipped without `BILLOMAT_ID`/`BILLOMAT_API_KEY`. Mind the Billomat sandbox rate limit (1,000 requests per 15 minutes).

```bash
composer test                                       # unit tests (fast, no network)
composer test:integration                           # integration tests against the sandbox
composer test:all                                   # both
composer ci                                         # lint + PHPStan + Rector + generate:check + all tests
vendor/bin/phpunit tests/Api/ClientsApiTest.php     # one file
vendor/bin/phpunit --filter itListsClients          # one method
```

More in [docs/testing.md](docs/testing.md).

## Quality tools

```bash
composer lint             # PHP-CS-Fixer (dry run)
composer lint:fix         # PHP-CS-Fixer (apply)
composer analyse          # PHPStan level max (no baseline)
composer refactor:dry     # Rector suggestions
composer refactor         # apply Rector
composer generate         # regenerate models/options/filters
composer generate:check   # fail if generated files are out of date
composer mutate           # Infection mutation testing (~3 min, all of src/)
composer audit            # security audit of the dependencies
```

**PHPStan:** level max without a baseline. Fix findings instead of adding `@phpstan-ignore` comments, ignore rules or casts that only silence them.

**Infection:** on pull requests CI mutates only the changed lines and requires 90 % MSI; locally: `composer mutate -- --git-diff-lines --git-diff-base=origin/4.x --ignore-msi-with-no-mutations` (the last flag keeps a change without any mutants, e.g. only property hooks, from failing with 0 %). The full run on `main`/`4.x` uses the thresholds from `infection.json5` (95 %). Infection runs with strict coverage: a test that executes a class outside its `CoversClass`/`UsesClass` attributes is risky and aborts the run.

**Rector vs. PHP-CS-Fixer:** if one tool reverts the other's change, that's a configuration bug; please open an issue instead of committing a workaround.

## Please discuss first

- Breaking changes to public classes, methods or properties. On `4.x` they're allowed until 4.0.0, but each needs a CHANGELOG entry and a before/after section in `UPGRADE-4.0.md`.
- New dependencies in `composer.json`. Explain why in the PR.
- Changes to `BillomatHttpClient::buildBillomatQuery()`. Its quirks (literal `+`, `key[]=v` arrays) work around Billomat behaviour; see [docs/advanced/http-layer.md](docs/advanced/http-layer.md).

## Changelog

Add notable changes to the `[Unreleased]` section of `CHANGELOG.md`, grouped by `Added`, `Changed`, `Fixed` and `Removed`, in English. The section gets its version on release.

## PR checklist

- [ ] `composer ci` passes locally
- [ ] Unit tests for new code (`#[Test]`, `#[CoversClass]`)
- [ ] Generated files changed only via definitions + `composer generate`
- [ ] New resources: docs under `docs/resources/` and an entry in its README
- [ ] `CHANGELOG.md` updated (and `UPGRADE-4.0.md` for breaking changes)
- [ ] Code comments in German, identifiers in English
- [ ] `composer analyse` passes without new ignores
