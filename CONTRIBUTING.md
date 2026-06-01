# Beitragsleitfaden

Danke, dass du zu `justpilot/billomat-php-api` beitragen möchtest. Dieses Dokument beschreibt Workflow, Coding-Standards und Erwartungen an Pull Requests.

## Workflow

1. Eröffne ein Issue (auch für Bugfixes), damit Scope und Ansatz vorab geklärt sind.
2. Forke das Repository und erzeuge einen Feature-Branch mit sprechendem Namen, z. B. `feature/add-articles-resource` oder `fix/payment-list-pagination`.
3. Halte Commits klein und thematisch fokussiert. Englische Commit-Messages im Imperativ („Add articles list endpoint“).
4. Reiche einen Pull Request gegen `main` ein und beschreibe das Warum (nicht nur das Was). Verweise im PR-Text auf das Issue.
5. Lokale Qualitätspipeline (`composer ci`) muss grün sein, bevor Review angefordert wird.

## Coding-Standards

- **PHP 8.4+**. Verwende moderne Sprachfeatures: `final readonly` Klassen für Read-Modelle, `readonly` Properties für Konfiguration, Constructor Property Promotion, Named Arguments, Enums.
- **`declare(strict_types=1);` am Anfang jeder PHP-Datei**.
- **`final` für API-Klassen**, `final readonly` für Read-Modelle in `src/Model/`.
- **PSR-4**:
  - `Justpilot\Billomat\` → `src/`
  - `Justpilot\Billomat\Tests\` → `tests/`
- **Kommentar-Sprache: Deutsch.** Identifier (Klassen, Methoden, Properties) bleiben Englisch. Halte dich an den Stil bestehender Dateien.
- **Keine globalen Zustände.** Konfiguration läuft über `BillomatConfig`, HTTP-Zugriff über `BillomatHttpClient` bzw. `BillomatHttpClientInterface`.

## Drei-Schichten-Muster pro Ressource

Jede Billomat-Ressource folgt demselben Muster — orientiere dich für eine neue Ressource an den bestehenden Beispielen (`ClientsApi`, `ClientCreateOptions`, `ClientUpdateOptions`, `Model\Client`).

1. **`src/Api/{Resource}Api.php`** — erbt von `AbstractApi`. Exponiert die Verben (`list`, `get`, `create`, `update`, `delete`) plus ressourcenspezifische Aktionen. Kümmert sich um das Auspacken der Billomat-Response-Hülle (`{ "clients": { "client": [...] } }` bzw. `{ "client": { … } }`). Beim Listen muss `array_is_list()` geprüft werden, weil Billomat bei genau einem Eintrag ein Objekt statt einer Liste liefert (siehe `ClientsApi::list()`).
2. **`src/Api/{Resource}CreateOptions.php` und/oder `{Resource}UpdateOptions.php`** — typisierte Payload-Klassen. Public nullable Properties, Pflichtfelder im Konstruktor. `toArray()` wandelt CamelCase nach snake_case und filtert `null`-Felder mit `array_filter`.
3. **`src/Model/{Resource}.php`** — `final readonly` Read-Modell mit `fromArray(array $data): self` für die Hydration und `toArray(): array` für Debug-/Log-Ausgaben. Snake_case-↔-CamelCase-Mapping lebt ausschließlich in diesen beiden Methoden.

## Neue Ressource hinzufügen

1. Lege die drei Dateien wie oben beschrieben an.
2. Falls die Ressource neue Enums benötigt, lege sie in `src/Model/Enum/` an (`enum X: string { case … }`, optional `fromApi(?string)` und `label()` Methoden).
3. Registriere die `*Api`-Klasse als `public readonly`-Property auf `BillomatClient` und instanziiere sie im Konstruktor mit `$this->http` als Argument.
4. Schreibe Unit-Tests in `tests/Api/{Resource}ApiTest.php` (siehe nächster Abschnitt).
5. Optional: Integrationstest unter `tests/Integration/{Resource}/`.
6. Lege eine Dokumentationsseite unter `docs/resources/<resource-name>.md` an und verlinke sie in der README-Tabelle.

## Tests

- Unit-Tests verwenden `Symfony\Component\HttpClient\MockHttpClient` mit `MockResponse`. Sie konstruieren `BillomatConfig`, dann `BillomatHttpClient`, dann die `*Api`-Klasse direkt — `BillomatHttpClient` selbst wird nicht gemockt.
- Asserts sollten sowohl das hydratisierte Modell als auch den abgesetzten Request (Methode, URL, Header, Payload) prüfen.
- **PHPUnit-12-Attribut-Stil:** `#[Test]` auf jeder Test-Methode (kein `test_`-Präfix), `#[CoversClass(Foo::class)]` auf der Klasse (mehrfach, falls mehrere SUTs), statische Assertions (`self::assertX()`).
- Integrationstests gehören unter `tests/Integration/`, erben von `AbstractBillomatIntegrationTestCase` und verwenden `createBillomatClientOrSkip()`. Sie müssen `markTestSkipped()` aufrufen, wenn `BILLOMAT_ID`/`BILLOMAT_API_KEY` fehlen.
- Integrationstests tragen `#[CoversNothing]` auf Klassenebene und `#[Group('integration')]` auf der Methode.

Tests und Qualitäts-Pipeline ausführen:

```bash
composer test                                         # Unit-Tests (schnell)
composer test:integration                             # nur Integrationstests
composer test:all                                     # alle Tests
composer ci                                           # Lint + Analyse + Rector-Dry + Tests
vendor/bin/phpunit tests/Api/ClientsApiTest.php       # einzelne Datei
vendor/bin/phpunit --filter it_lists_clients          # einzelne Methode
```

Detaillierte Test-Doku unter [docs/testing.md](docs/testing.md).

## Qualität: PHPStan, CS-Fixer, Rector, Infection

Alle Werkzeuge laufen über Composer-Skripte:

```bash
composer lint           # PHP-CS-Fixer (Dry-Run, zeigt Diff)
composer lint:fix       # PHP-CS-Fixer anwenden
composer analyse        # PHPStan Level max (mit phpstan-baseline.neon)
composer analyse:baseline   # Baseline regenerieren
composer refactor:dry   # Rector Vorschläge
composer refactor       # Rector anwenden
composer mutate         # Infection Mutation Testing
composer audit          # Sicherheits-Audit der Abhängigkeiten
```

**PHPStan-Baseline:** `phpstan-baseline.neon` friert bestehende Findings ein. Neue Findings führen zu CI-Fail. Wer bestehende Findings behebt, regeneriert die Baseline mit `composer analyse:baseline` und committet das Ergebnis.

**Rector + CS-Fixer Konflikte:** Sollte CS-Fixer eine Rector-Änderung wieder zurückdrehen oder umgekehrt, ist das ein Konfigurationsfehler. Bitte als Issue melden statt Workarounds einzuchecken.

## Was nicht ohne Absprache

- Breaking Changes an öffentlichen Klassen, Methoden oder Properties. Falls notwendig, vorher in einem Issue diskutieren — geht nur mit Major-Version-Bump.
- Neue Top-Level-Abhängigkeiten in `composer.json`. Begründe den Bedarf im PR.
- Änderungen an `BillomatHttpClient::buildBillomatQuery()`. Die Eigenheiten (`+` literal, `key[]=v1`-Arrays) sind Workarounds für Billomat-Quirks; siehe [docs/advanced/http-layer.md](docs/advanced/http-layer.md).

## Changelog

Trage relevante Änderungen unter einem `[Unreleased]`-Abschnitt in `CHANGELOG.md` ein, gegliedert nach `Added`, `Changed`, `Fixed`, `Removed`. Beim Release wird der Abschnitt versioniert.

## PR-Checkliste

- [ ] `composer ci` läuft lokal grün (Lint + PHPStan + Rector-Dry + Tests)
- [ ] Unit-Tests für neuen Code vorhanden (mit `#[Test]` und `#[CoversClass]`)
- [ ] Bei neuen Ressourcen: Dokumentation unter `docs/resources/` plus README-Eintrag
- [ ] `CHANGELOG.md` aktualisiert
- [ ] Kommentare auf Deutsch, Identifier auf Englisch
- [ ] Keine Breaking Changes ohne Absprache
- [ ] Keine neuen `phpstan-baseline.neon`-Einträge ohne Rückfrage (lieber Findings beheben)
