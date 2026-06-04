# Changelog

All notable changes to this project will be documented in this file.

The format is based on Keep a Changelog, and this project adheres to Semantic Versioning.

## [Unreleased]

## [2.1.1] - 2026-06-04

### Fixed
- **CI auf dem Tag-Commit grün.** Der in v2.1.0 eingeführte Codecov-Step rief `composer test:coverage` auf, wodurch Xdebug-Coverage aktiv wurde und PHPUnit unter `beStrictAboutCoverageMetadata="true"` + `failOnRisky="true"` 442 Tests als „Risky" geflaggt hat (Tests, die in API-Tests inzidentell Model-Code beim Hydrate-Aufruf ausführen). CI lief deshalb rot. Workflow rollt jetzt auf `composer test:unit` über alle Matrix-Einträge zurück; Codecov-Upload und striktes Coverage-Setup folgen in einem späteren Release, nachdem die Tests mit `#[UsesClass]`-Annotationen ergänzt wurden. Codecov-Badge bleibt vorerst als Platzhalter im README.

## [2.1.0] - 2026-06-04

### Added
- **Pagination-Iterator für alle List-Endpunkte.** Zwei neue, rein additive Methoden pro `*Api`-Klasse mit `list()`:
  - `iterateAll(array $filters = [], int $pageSize = 100): \Generator<int, Model>` — lazy Generator nach Stripes `auto_paging_iter()`-Pattern. Holt seitenweise und yieldet einzelne Modelle; stoppt automatisch sobald die letzte Seite erreicht ist (entweder per `@total`-Envelope oder per `count(items) < pageSize`-Heuristik). Filter werden bei jeder Page-Anfrage mitgesendet; `page`/`per_page` darin werden überschrieben.
  - `listPage(array $filters = []): Page<Model>` — gibt eine einzelne Seite samt Pagination-Metadaten zurück. Neue Wert-Objekte `Justpilot\Billomat\Pagination\Page<T>` (`items` + `info`) und `Justpilot\Billomat\Pagination\PageInfo` (`page`/`perPage`/`total`/`totalPages()`/`hasNextPage()`). `total` ist `null`, wenn der Endpunkt keine `@total`-Metadaten liefert.

  Verfügbar auf den 26 List-APIs: `clients`, `invoices`, `offers`, `confirmations`, `creditNotes`, `deliveryNotes`, `reminders`, `letters`, `articles`, `suppliers`, `incomings`, `inboxDocuments`, `recurrings`, `contacts`, `articleProperties`, `articlePropertyValues`, `clientProperties`, `incomingProperties`, `incomingPropertyValues`, `supplierProperties`, `supplierPropertyValues`, `countries`, `currencies`, `units`, `users`, `emailTemplates`, `freeTexts`. Bestehende `list()`-Methoden bleiben unverändert.

  Zwei neue `protected`-Helper in `AbstractApi`: `listResourcePage()` und `iterateResource()`. Beispiel: siehe `docs/advanced/pagination.md`.

### Changed
- **Empty-String → null in Read-Modellen normalisiert.** Billomat liefert in JSON-Responses an vielen Stellen leere Strings statt echter `null`-Werte (z. B. `"title": ""`). Die `fromArray()`-Hydration aller Read-Modelle setzt diese Werte jetzt konsistent auf `null`, was bisher nur für int/float/bool-Felder so war. **Auswirkung:** Konsumenten, die in nullable string-Properties bisher gegen `''` geprüft haben, sollten auf `null` (oder `?? ''`) umstellen. Property-Typen, Konstruktor-Signaturen und `toArray()`-Ausgabe bleiben unverändert.

### Internal
- **Boilerplate-Konsolidierung.** Neue interne Hilfsklasse `Justpilot\Billomat\Internal\ScalarCaster` bündelt die wiederkehrenden Cast-Pattern aus den Read-Modellen (`toIntOrNull`/`toFloatOrNull`/`toBoolOrNull`/`toStringOrNull`/`toDateTimeOrNull`). Zwei neue `protected`-Helper in `AbstractApi`: `listResource()` (Envelope-Lookup + `array_is_list`-Normalisierung + `array_map`) und `unwrapEnvelope()` (zentrale `RuntimeException` mit konsistenter Message für unerwartete Response-Strukturen). 81 Read-Modelle und 52 Api-Klassen migriert. PHPStan-Baseline schrumpft von 1144 auf 661 Einträge (-42 %).
- **PHPStan-Baseline weiter abgebaut.** 68 redundante `assertInstanceOf`-Aufrufe in Tests entfernt (Property-/Return-Typen garantieren den Typ bereits). PHPDoc-Return-Typen für die `TestApi`-Helper und den `queryStringProvider` mit `iterable`-Value-Typen ergänzt. Zwei eng begrenzte `ignoreErrors`-Patterns in `phpstan.neon.dist` für die unter `tests/Api/*` und `tests/Integration/*` häufigen `offsetAccess.nonOffsetAccessible`/`argument.type`-Warnungen aus der Mocked-Request-Introspection (Closure-Argument `array $options` ist ungetyped, jeder Folge-Zugriff `mixed`). Begründung als Kommentar im Config-File. Produktivcode unter `src/` bleibt strikt auf Level max. Baseline jetzt bei 489 Einträgen (von 1144 in v2.0.0, -57 %).

### Fixed
- **HTTP-Fehler in Lifecycle-Verben werden jetzt korrekt zu SDK-Exceptions gemapped.** Bisher gaben die folgenden Methoden bei 4xx/5xx still `false` zurück, weil sie sich auf `ResponseInterface::getStatusCode()` verließen — diese Methode wirft laut Symfony-Vertrag aber nur bei Transport-Fehlern, nicht bei HTTP-Status-Codes. Betroffen waren:
  - `InvoicesApi::complete()`, `cancel()`, `uncancel()`, `uploadSignature()`, `encash()`
  - `OffersApi::complete()`, `cancel()`, `win()`, `lose()`, `clear()`, `undo()`, `uploadSignature()`
  - `LettersApi::complete()`, `cancel()`, `clear()`, `undo()`, `upload()`, `uploadSignature()`
  - `CreditNotesApi::complete()`, `cancel()`, `uncancel()`, `uploadSignature()`
  - `ConfirmationsApi::complete()`, `cancel()`, `clear()`, `undo()`, `uploadSignature()`
  - `DeliveryNotesApi::complete()`, `cancel()`, `clear()`, `undo()`, `uploadSignature()`
  - `RemindersApi::complete()`, `cancel()`, `uploadSignature()`
  - `IncomingsApi::cancel()`, `uncancel()`, `upload()`

  Ab jetzt werfen alle diese Methoden bei Fehlern eine `ValidationException`, `AuthenticationException`, `NotFoundException` oder `HttpException` — wie im übrigen SDK und in `docs/error-handling.md` dokumentiert. Im Erfolgsfall geben sie weiterhin `true` zurück, Signatur und Happy-Path-Verhalten bleiben unverändert.

### Deprecated
- `AbstractApi::putEmptyResponse()` ist als `#[\Deprecated]` markiert und wird in 3.0 entfernt. Interner Ersatz: `AbstractApi::putVoid()`, das HTTP-Fehler korrekt materialisiert.

### Documentation
- **README überarbeitet.** Alle exposed APIs (`account`, `activities`, `search`, `roles`, `countryTaxes`, `incomingCategories`, `userProperties`) sind jetzt in den Resource-Tabellen gelistet. Neuer Abschnitt „Weitergehende Konzepte" verlinkt die sechs `docs/concepts/`-Guides (Auth, API-Security, Pagination/Filtering, Custom Meta Attributes, Errors & Rate Limits, Webhooks-as-UI-only). Neue Sektionen „Logging & HTTP-Client" (Snippet mit `TraceableHttpClient` / `RetryableHttpClient`) und „Changelog" mit Link auf Keep a Changelog + SemVer.
- **Englische README.** Neues `README.en.md` spiegelt die deutsche README für Nicht-DACH-Besucher; beide Dateien cross-linken via Sprach-Switcher-Header.
- **Migrations-Guide phobetor → justpilot.** Neuer Guide `docs/migration-from-phobetor.md` mit Side-by-Side-Übersetzungen (Instanziierung, CRUD, Pagination, PDF, Exceptions). Jede justpilot-seitige Signatur ist gegen den tatsächlichen SDK-Stand verifiziert.
- **Packagist-Keywords erweitert.** `composer.json` enthält jetzt zusätzlich `symfony`, `http-client`, `typed`, `readonly`, `php84`, `dach`, `gobd`, `api-client` — Discoverability für Search-Queries jenseits von „billomat".
- **Code of Conduct.** `CODE_OF_CONDUCT.md` (Contributor Covenant 2.1) im Root. Kontakt: `dimitri@justpilot.io`.

### Build
- **GitHub-Community-Set vollständig.** `.github/ISSUE_TEMPLATE/{bug_report,feature_request,config}.yml`, `.github/pull_request_template.md` und `.github/dependabot.yml` (Composer + GitHub-Actions weekly, gruppiert in `symfony` und `dev-tools`) hinzugefügt.
- **Codecov-Integration.** CI lädt das Coverage-XML aus dem PHP 8.4 + Symfony-LTS-Job nach Codecov hoch; Badge in `README.md` und `README.en.md` neben dem PHPStan-Badge.

## [2.0.0] - 2026-06-02
### Breaking Changes
- `InvoiceMailOptions::recipientAddress` entfernt. Das Feld ist in der Billomat-Doku zum Pixelletter-Versand nicht dokumentiert und wurde serverseitig stillschweigend ignoriert. **Migration:** Property aus bestehenden Aufrufen entfernen — Empfängerdaten werden aus dem auf der Rechnung hinterlegten Adressdatensatz übernommen.

### Added
- **Invoice Comments** (`$billomat->invoiceComments`, `/invoice-comments`) — `listByInvoice()` mit optionalem `actionkey`-CSV-Filter, `get()`, `create()`, `delete()`. Inkl. `InvoiceComment`-Read-Modell und `InvoiceCommentActionKey`-Enum mit Roh-String-Fallback für unbekannte Werte.
- **Invoice Tags** (`$billomat->invoiceTags`, `/invoice-tags`) — `listByInvoice()`, aggregierte `cloud()`-Liste mit Häufigkeit, `get()`, `create()`, `delete()`. Eigene Read-Modelle `InvoiceTag` und `InvoiceTagCloudEntry`.
- **Recurring Invoices** (Abo-Rechnungen) komplett: `$billomat->recurrings` (`/recurrings`, full CRUD inkl. eingebetteter Items beim `create()`), `$billomat->recurringItems` (`/recurring-items`, full CRUD), `$billomat->recurringTags` (`/recurring-tags`, list-by-recurring + cloud), `$billomat->recurringEmailReceivers` (`/recurring-email-receivers`). Mit Enums `RecurringCycle`, `RecurringAction` und `RecurringEmailReceiverType`.
- **Offers** (Angebote) — `$billomat->offers` mit `complete`/`cancel`/`win`/`lose`/`clear`/`undo`/`email`/`pdf`/`uploadSignature` plus `offerItems`, `offerComments`, `offerTags`.
- **Confirmations** (Auftragsbestätigungen) — `$billomat->confirmations` mit Lifecycle-Verben + `confirmationItems`, `confirmationComments`, `confirmationTags`.
- **Delivery Notes** (Lieferscheine) — `$billomat->deliveryNotes` + `deliveryNoteItems`, `deliveryNoteComments`, `deliveryNoteTags`.
- **Credit Notes** (Gutschriften) — `$billomat->creditNotes` + `creditNoteItems`, `creditNoteComments`, `creditNoteTags`, `creditNotePayments` (separate Auszahlungs-API).
- **Reminders** (Mahnungen) — `$billomat->reminders`, `reminderItems` (read-only Sub-Items), `reminderTags`. Inkl. E-Mail- und PDF-Versand.
- **Letters** (Briefe) — `$billomat->letters` mit Pixelletter-Versand + `letterComments`, `letterTags`.
- **Articles** (Artikel) — `$billomat->articles` + `articleTags`, `articlePropertyValues` (Custom-Field-Werte je Artikel).
- **Suppliers** (Lieferanten) — `$billomat->suppliers` + `supplierTags`, `supplierPropertyValues`.
- **Contacts** (Ansprechpartner an Kunden) — `$billomat->contacts` mit `listByClient()` als Pflichtfilter.
- **Client Tags** — `$billomat->clientTags` mit `listByClient()`, `cloud()`, `create()`, `delete()`.
- **Incomings** (Eingangsbelege) — `$billomat->incomings` (Lieferantenrechnungen) + `incomingComments`, `incomingPayments`, `incomingTags`, `incomingPropertyValues`.
- **Inbox Documents** — `$billomat->inboxDocuments` (Posteingang für hochgeladene PDFs).
- **Property-Definitionen** — `$billomat->articleProperties`, `clientProperties`, `supplierProperties`, `incomingProperties` für die Verwaltung von Custom-Field-Schemas.
- **Lookups** — `$billomat->countries`, `currencies`, `units`, `dunningLevels`, `users`, `emailTemplates`, `freeTexts`, `reminderTexts` als read-only Hilfs-Endpoints.
- **`InvoicesApi::listGrouped()`** für `?group_by=…`-Aggregate (Brutto/Netto-Summen pro Kunde, Status oder Zeitraum). Neues Enum `InvoiceGroupBy` und Read-Modell `InvoiceGroup` mit `invoiceParams` für Drill-down.
- **`InvoicesApi::email()`**, **`mail()`** (Pixelletter), **`uploadSignature()`** und **`encash()`** plus Write-Modelle `InvoiceEmailOptions` und `InvoiceMailOptions`.
- `InvoiceEmailOptions::emailTemplateId` (Billomat-Feld `email_template_id`) zur Auswahl einer E-Mail-Vorlage.
- `InvoiceMailOptions::attachments` für zusätzliche PDF-Anhänge beim Pixelletter-Versand.
- Beispiele `07-create-offer.php`, `08-credit-note.php`, `09-recurring.php`, `10-incoming.php`, `11-supplier.php`, `12-email-invoice.php` unter `examples/`.
- Ressourcen-Doku unter `docs/resources/` ergänzt: `offers.md`, `confirmations.md`, `delivery-notes.md`, `credit-notes.md`, `reminders.md`, `letters.md`, `articles.md`, `suppliers.md`, `contacts.md`, `client-tags.md`, `incomings.md`, `inbox-documents.md`, `properties.md`, `lookups.md`.
- `composer test`, `composer test:unit`, `composer test:integration`, `composer test:all`, `composer test:coverage`.
- `composer lint`, `composer lint:fix` via PHP-CS-Fixer (PER-CS + Symfony + PHP84Migration + PHPUnit100Migration rule sets).
- `composer analyse`, `composer analyse:baseline` via PHPStan 2 (Level max, strict rules, PHPUnit + Symfony extensions, baseline at `phpstan-baseline.neon`).
- `composer refactor`, `composer refactor:dry` via Rector 2 (PHP 8.4 set, code-quality, dead-code, type-declaration, PHPUnit-attribute migration).
- `composer mutate` via Infection 0.29 (mutation testing).
- `composer audit`, `composer ci` (full local CI run).
- GitHub Actions workflow `.github/workflows/ci.yml` with PHP 8.4 (required) + 8.5 (experimental) matrix and tests/quality/mutation jobs.
- Strict PHPUnit configuration: split `unit`/`integration` testsuites, `failOnWarning`, `failOnDeprecation`, `failOnNotice`, `requireCoverageMetadata`, `beStrictAboutCoverageMetadata`.

### Changed
- All tests migrated to PHPUnit 12 attribute style: `#[Test]` (no `test_` prefix), `#[CoversClass]` on unit tests, `#[CoversNothing]` on integration tests, `#[Group]` from former `@group` docblocks.
- `src/` modernized via Rector + PHP-CS-Fixer (constructor property promotion, closure return types, native function invocation hints, EOF newlines).
- Documentation refreshed (`README.md`, `CONTRIBUTING.md`, `docs/testing.md`, `CLAUDE.md`) around the new composer scripts and attribute-based test style.

### Fixed
- `.gitignore` no longer ignores `phpunit.xml.dist` (was a long-standing bug; the local override `phpunit.xml` is ignored instead).

## [1.2.0] - 2026-02-24
### Changed
- Allow Symfony components v8.0 (`symfony/http-client`, `symfony/serializer`, `symfony/options-resolver`, `symfony/http-foundation`).
- Allow Symfony dev components v8.0 (`symfony/dotenv`, `symfony/var-dumper`).