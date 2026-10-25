# Changelog

All notable changes to this project will be documented in this file.

The format is based on Keep a Changelog, and this project adheres to Semantic Versioning.

## [Unreleased]

See [UPGRADE-4.0.md](UPGRADE-4.0.md) for a step-by-step migration guide.

### Added
- **PSR-18 transport.** The SDK works with any PSR-18 client (Symfony, Guzzle, …) and PSR-17 factories, found via `php-http/discovery` or passed to `BillomatClient`. Without a client, Symfony's `Psr18Client` is used with `BillomatConfig::$timeout` when `symfony/http-client` is installed.
- **Automatic retries** via `BillomatConfig::$retry` (`RetryPolicy`, default: 2 retries, 500 ms base delay, 10 s cap). 429 responses are retried for every method after the wait Billomat announces (`Retry-After` or `X-Rate-Limit-Reset`); GET requests are also retried on 502/503/504 and network errors with exponential backoff and jitter. `RetryPolicy::none()` turns retries off.
- `RateLimitException` (429) and `TransportException` (no response, wraps the PSR-18 exception), both below `BillomatException`.
- **Error classification:** `BillomatException::isTransient()` (429, 502, 503, 504 and network errors) and `mayHaveBeenProcessed()` (`false` for 4xx, `true` for 5xx and network errors), so callers no longer inspect status codes or `getPrevious()` to decide whether to retry later or to check before resending a write request.
- `HttpException::getRetryAfter()`: seconds announced by Billomat, set for 429 (`Retry-After` or `X-Rate-Limit-Reset`) and for 502/503/504 (`Retry-After`).
- `WriteOptions` interface with `toArray()`, implemented by all `*CreateOptions`/`*UpdateOptions` and the other write options.
- `Clear::Field` empties a field in `*UpdateOptions` and `SettingsUpdateOptions`: `null` still means "not sent, unchanged"; `Clear::Field` is sent as JSON `null`, which makes Billomat clear the field.
- `with*()` methods on every options class return a copy with one field changed (PHP 8.5 `clone` with properties), e.g. `$options->withNote('…')`; `withItem()` on document create options appends a position.
- **Typed list filters** for the main resources: `ClientFilter`, `SupplierFilter`, `ArticleFilter`, `UserFilter`, `InvoiceFilter`, `OfferFilter`, `ConfirmationFilter`, `CreditNoteFilter`, `DeliveryNoteFilter`, `LetterFilter`, `ReminderFilter`, `IncomingFilter`, `RecurringFilter` and `TemplateFilter`, generated from the Billomat docs. They're immutable (named arguments plus `with*()`), use enums for status and payment types, `DateTimeImmutable` for `from`/`to` and lists for multi-value filters (sent comma-separated). `list()`, `listPage()` and `iterateAll()` accept them next to the plain arrays, which keep working. `extra` passes further query parameters.
- `OrderBy` builds `order_by`: `OrderBy::desc('date')->thenAsc('invoice_number')` gives `date+DESC,invoice_number+ASC`.
- **`Money` value object** (`Justpilot\Billomat\Model\Money`) for exact amounts, based on `BcMath\Number`: `Money::of('19.99')`, `sum()`, `add()`, `subtract()`, `multiply()`, `round()`, comparisons, `isZero()`/`isPositive()`/`isNegative()`, `format(2, ',', '.')` and `toString()` in Billomat's canonical form. Requires `ext-bcmath`.
- **Webhook helpers** in `Justpilot\Billomat\Webhook`: `WebhookEvent` (all 83 events, with `resource()`, `action()`, `envelopeKey()` and `modelClass()`), `WebhookHeaders::fromServerRequest()`/`fromArray()` for the `X-Billomat-Webhook-*` headers (request ID as int), and `WebhookPayload::fromJson()`/`fromArray()`, which unwraps the REST-style envelope and hydrates the matching model via `model()`. They work on stored requests as well. Errors throw `InvalidWebhookException`.
- **PSR-3 logging and PSR-14 events:** `BillomatClient` (and `create()`) take an optional `logger` and `events` dispatcher. The SDK logs successful responses (`debug`), retries (`warning`) and final failures (`notice` for 4xx, `error` for 5xx and network errors) with method, path, attempt, status and duration, never headers, query or body. It dispatches `RequestStarted`, `ResponseReceived`, `RetryScheduled` and `RequestFailed` (`Justpilot\Billomat\Event`). New runtime dependencies: `psr/log` and `psr/event-dispatcher` (interfaces only).
- **Unknown fields are kept:** every generated read model has `$extra` with the fields Billomat returns that the model doesn't map (yet), raw as in the response. `toArray()` includes them, so new Billomat fields no longer get lost. Mapped fields win over `extra` entries with the same key.
- `getOrFail()` on every resource and sub-resource with `get()`: returns the model or throws `NotFoundException` instead of returning `null`.
- `BillomatConfig::$language` (and `BillomatClient::create(language: …)`) sets the `Accept-Language` header, e.g. `'en-gb'`; default stays `'de-de'`, `null` omits the header.
- Exception messages include the method, the path and Billomat's error text, e.g. `Billomat responded with HTTP 422 to POST /clients. Name is missing`.
- `OffersApi::uncancel()`/`unclear()`, `ConfirmationsApi::uncancel()`/`unclear()` and `RemindersApi::uncancel()` for the `/uncancel` and `/unclear` endpoints (checked in the sandbox).
- **Contract fixtures:** anonymized sandbox responses under `tests/Fixtures/Contract/` and `ContractFixturesTest` catch drift between the Billomat API and the models in CI without credentials; `composer fixtures:record` re-records them (read-only GETs, see `docs/testing.md`).
- `User::$rights` (`array<string, AccessLevel>`): the access level per area (`articles`, `banking`, …) Billomat returns with every user; before, it ended up raw in `User::$extra`. New enum `AccessLevel` (`READ`, `UPDATE`, `DELETE`) with `label()` and `allows()` (`DELETE` allows `UPDATE` and `READ`). The generator has a `map` field type for such objects (area → enum value); empty and unknown values are dropped.
- `Client::$propertyValues`, `Supplier::$propertyValues` (the `client-property-values`/`supplier-property-values` Billomat returns with every client and supplier) and `Role::$created`/`$updated`, found by the contract fixtures.

### Changed
- Development: PHPStan runs with the bleeding-edge ruleset and without `ignoreErrors` for the tests; the tests capture the outgoing `MockHttpClient` request as a typed `CapturedRequest` (method, URL, query, headers, decoded JSON body) instead of nested `mixed` arrays. No change to the public API.
- The PHPStan baseline is empty and `phpstan-baseline.neon` is gone (`composer analyse:baseline` too): the findings were fixed in the code. Filter and query parameters are typed `array<string, scalar|array<scalar|null>|null>`, list responses are normalized in one place (`AbstractApi`), and `ListCaster::toNestedList()` reads the `taxes`/`*-items` wrappers of the generated models. Behaviour is unchanged, except that a response body that is a JSON list instead of an object now throws `UnexpectedResponseException`.
- **BC break: amounts are `Money` instead of `float`** in all read models and write options (`totalGross`, `totalNet`, `*Unreduced`, `totalReduction`, `openAmount`, `paidAmount`, `discountAmount`, `unitPrice`, `salesPrice`–`salesPrice5`, `purchasePrice`, payment `amount`, `chargeAmount`, `total`, `revenue*`/`costs*`, `finapiTransactionAmount`, the `amount` of tax rows and `InvoiceGroup` totals). Payloads send amounts as decimal strings, `toArray()` exports them as strings. Quantities, tax and discount rates and exchange rates (`quote`) stay `float`.
- **BC break: `ext-bcmath` is required.**
- **BC break: tax rows are `TaxSummary` objects.** `taxes` on invoices, offers, credit notes, confirmations, delivery notes and recurrings is a `list<TaxSummary>` (`name`, `rate`, `amount` as `Money`) instead of a list of arrays; `toArray()` exports the rows as before, with the amount as a string.
- **BC break: `OfferStatus::ACCEPTED`/`REJECTED` are now `WON`/`LOST`.** Billomat sets won and lost offers to `WON` and `LOST` (checked in the sandbox), so `win()`/`lose()` used to leave `$offer->status` at `null`, and `OfferFilter(status: [OfferStatus::ACCEPTED])` sent a value Billomat doesn't know.
- **BC break: more fields are enums instead of strings.** `Client::$taxRule` (`TaxRule`), `$netGross` (`NetGross`) and the five `*Type` fields `discountRateType`, `discountDaysType`, `dueDaysType`, `reminderDueDaysType`, `offerValidityDaysType` (`ValueType`), in the model and in `ClientCreateOptions`/`ClientUpdateOptions`; `*PropertyValue::$type` (`PropertyType`), `InvoiceGroup::$status` (`InvoiceStatus`), `FreeText::$type` (new `FreeTextType`) and `EmailTemplate::$type` (new `EmailTemplateType`). Unknown values hydrate to `null`.
- **BC break: `InvoicePaymentType`, `InvoicePdfType` and `InvoiceItemType` are now `PaymentType`, `PdfType` and `ItemType`** (same namespace `Justpilot\Billomat\Model\Enum`, same cases). They were always used for credit notes, incomings, offers, letters, reminders and all document items as well.
- **BC break: `ArticleType` is merged into `ItemType`.** Both had the cases `PRODUCT` and `SERVICE`; `Article::$type` and the `type` of `ArticleCreateOptions`/`ArticleUpdateOptions` are `?ItemType` now.
- **BC break: payment types are `list<PaymentType>` instead of a comma-separated string:** `Invoice::$paymentTypes`, `Recurring::$paymentTypes`, `Client::$defaultPaymentTypes` and the matching create/update options. Payloads and `toArray()` still send the comma-separated string; an empty list is not sent, `Clear::Field` empties the field. Unknown values are dropped when hydrating.
- **BC break: every enum has the same `fromApi(?string): ?self`** (trait `Model\Enum\FromApi`): `null`, `""` and unknown values give `null`. Enums that had no `fromApi()` (`NetGross`, `TaxRule`, `ValueType`, `SupplyDateType`, `RecurringAction`, `RecurringCycle`, the `*CommentActionKey` enums, …) get one; a few returned a case for `""` before.
- **BC break: `RecurringEmailReceiver::$type` is `?RecurringEmailReceiverType`** and `RecurringEmailReceiverType::fromApi()` returns `null` for unknown values instead of silently falling back to `TO` (a typo like `bc` used to turn a BCC receiver into a visible To receiver). Lower-case values (`bcc`) are still accepted.
- **BC break: `Role::$permissions` is `array<string, AccessLevel>`** instead of `array<string, ?string>`. Areas without access (`""`) and unknown values are left out instead of being `null`. `Role::toArray()` exports the permissions as top-level fields like Billomat (`'articles' => 'DELETE'`) instead of under `permissions`. `Role::AREAS` lists the known areas.
- **BC break: `InvoiceMailOptions::$paperWeight` is `?int`** (80 or 90) instead of `?string`, like `letterPaperWeight` on recurrings.
- **BC break: `pdf(…, rawPdf: true)` checks the body.** All seven `pdf()` methods throw `UnexpectedResponseException` when Billomat answers 2xx with an empty body or something that does not start with `%PDF-`, instead of returning it as a PDF. Custom APIs can use the new `AbstractApi::getPdf()`.
- **BC break: PHP 8.5 is now the minimum version** (`"php": "^8.5"`). CI runs on PHP 8.5 (required) and 8.6 (experimental); Rector uses the PHP 8.5 rule set.
- **Read-only API methods are marked `#[\NoDiscard]`:** `get()`, `getOrFail()`, `getMyself()`, `list()`, `listPage()`, `listGrouped()`, `iterateAll()`, `cloud()`, `pdf()`, `thumb()`, `avatar()` and `SearchApi::query()`. PHP 8.5 emits a warning when their result is ignored; cast to `(void)` to discard it on purpose. Write methods (`create()`, `update()`, `delete()`, …) are not marked.
- **BC break: `BillomatConfig` validates its input.** The Billomat ID must be a valid subdomain (letters, digits, inner hyphens, at most 63 characters), and the base URI is parsed with PHP 8.5's `Uri\Rfc3986\Uri` and must be absolute. Otherwise the constructor throws an `InvalidArgumentException`, instead of sending requests to a wrong host.
- **BC break: `symfony/http-client` is no longer a dependency.** Install a PSR-18 client and PSR-17 factories (e.g. `symfony/http-client` + `nyholm/psr7`, or `guzzlehttp/guzzle`). `BillomatClient::__construct()` and `create()` take a `Psr\Http\Client\ClientInterface` instead of a Symfony `HttpClientInterface`; wrap Symfony clients in `Psr18Client`.
- **BC break: `BillomatHttpClientInterface::request()` returns a PSR-7 `ResponseInterface` and throws the SDK exceptions itself.** Custom `AbstractApi` subclasses no longer catch Symfony exceptions; read the body with `(string) $response->getBody()`.
- **BC break:** a 2xx response whose body is not a JSON object throws `UnexpectedResponseException` instead of `JsonException`.
- **`BillomatClient` creates its APIs lazily:** the 30 resource properties are PHP 8.4 property hooks (`public private(set)`), so an API object is only built on first access and reused afterwards. `BillomatClient` is no longer a `readonly` class; the properties can't be assigned from outside (they couldn't before either).
- **BC break: sub-resources hang off their parent resource.** `$billomat->invoiceComments` becomes `$billomat->invoices->comments`, `$billomat->clientTags` becomes `$billomat->clients->tags`, and so on for all items, payments, comments, tags, properties, property values and recurring e-mail receivers (see the table in UPGRADE-4.0.md). They are created on first access.
- **BC break: one generic class per kind of sub-resource.** `CommentsApi`, `TagsApi`, `ItemsApi`, `PaymentsApi`, `PropertiesApi` and `PropertyValuesApi` replace the 39 per-resource classes (`InvoiceCommentsApi`, `ClientTagsApi`, …); they are typed with `@template`, so PHPStan and IDEs know the model of `$billomat->invoices->comments->get()`. `ReminderItemsApi` and `RecurringEmailReceiversApi` stay as classes. `create()`/`update()` throw an `InvalidArgumentException` when passed the options of another resource (e.g. `OfferCommentCreateOptions` to `$billomat->invoices->comments`), as do comment filters with another resource's `*CommentActionKey`.
- **BC break: `listBy{Parent}()` is now `list()`** on comments, tags, items, recurring e-mail receivers and contacts (`ContactsApi::listByClient()` → `list()`). `list()` on payments and property values takes the parent ID as optional first argument: `list(?int $parentId = null, array $filters = [])`, likewise `listPage()` and `iterateAll()` on property values.
- **BC break: methods that always returned `true` now return `void`:** `delete()`, `complete()`, `cancel()`, `uncancel()`, `clear()`, `undo()`, `win()`, `lose()`, `email()`, `mail()`, `encash()`, `upload()` and `uploadSignature()`. Errors were and are reported as exceptions.
- **BC break: options are immutable.** All `*CreateOptions`/`*UpdateOptions`, `EmailOptions`, `InvoiceMailOptions`, `InboxDocumentCreateOptions` and `SettingsUpdateOptions` are `final readonly` classes; every field is a constructor parameter with a default, so they are built with named arguments (`new ClientCreateOptions(name: 'ACME')`) instead of assigning properties. Required fields (e.g. `clientId` on `InvoiceCreateOptions`) stay the first positional parameters.
- **BC break: positions go into the constructor.** `addItem()` and `getItems()` on `InvoiceCreateOptions`, `OfferCreateOptions`, `ConfirmationCreateOptions`, `CreditNoteCreateOptions`, `DeliveryNoteCreateOptions` and `RecurringCreateOptions` are replaced by the `items:` parameter, the `$items` property and `withItem()`.
- **BC break: one `EmailOptions` class** replaces `InvoiceEmailOptions`, `OfferEmailOptions`, `ConfirmationEmailOptions`, `CreditNoteEmailOptions`, `DeliveryNoteEmailOptions`, `ReminderEmailOptions` and `LetterEmailOptions`, which had identical fields.
- **BC break: `ClientCreateOptions::$dunningRun` defaults to `null`** and is only sent when set, so Billomat's account default applies. Before, `dunning_run=0` was always sent.
- Sub-resource `get()` throws `UnexpectedResponseException` when the response lacks the expected envelope; some classes returned `null` before.
- Array query parameters go out as `key%5B%5D=v` (PSR-7 URIs encode the brackets per RFC 3986, which servers decode to the same `key[]`).
- `composer generate` fails when a spec field has a fixed set of values but its definition maps it to no enum (`'enum' => 'X'`, or `'enum' => false` to keep the string on purpose).
- Mutation testing covers the generated code on purpose: reflection-based tests check every read model's `fromArray()`/`toArray()` round trip and every write option's payload against the `Billomat-Feld` in its docblock. Covered MSI rises from 77 % to 97 % (generated code 73 % → 99.7 %), `minMsi`/`minCoveredMsi` from 60 to 95. `PublicVisibility`/`ProtectedVisibility` are disabled (about 1,500 mutants that never survived). CI mutates only the changed lines on pull requests (`--git-diff-lines`, 90 %) and all code on pushes to `main`/`4.x`.

- **Documentation website** at [billomat-php.justpilot.io](https://billomat-php.justpilot.io), built with VitePress from `docs/` (local search, edit links, sidebar from the page titles) and deployed to GitHub Pages by `.github/workflows/docs.yml`; links to files outside `docs/` point to GitHub.
- **README is an English landing page** (≈100 lines: dynamic badges, install, quickstart, features, documentation map, supported versions); `README.en.md` is gone, the resource table moved to `docs/resources/README.md` with the nested 4.x access paths.
- `CONTRIBUTING.md`, `SECURITY.md`, the pull request template and the issue templates are in English and describe the 4.x workflow (PRs against `4.x`, generated code, PSR-only dependencies, supported versions 4.x/3.x). Packagist keywords drop `symfony`/`http-client` and add `psr-3`, `psr-14`, `money` and `e-invoice`.
- **The examples in `examples/` follow the 4.x API and are checked in CI:** PHPStan (level max), PHP-CS-Fixer and Rector cover them like `src/` and `tests/`. A shared `examples/bootstrap.php` reads the environment variables and builds the client; the scripts use `getOrFail()`, `Money`, `OrderBy` and the enums, and `examples/README.md` is in English.

### Removed
- **BC break: everything marked `@deprecated since 3.1` is gone.**
  - `DunningLevelsApi`, `$billomat->dunningLevels` and the `DunningLevel` model (`/dunning-levels` answers with 404). Use `$billomat->reminderTexts` for reminder levels.
  - `AbstractApi::putEmptyResponse()` (use `putVoid()`).
  - The read-model properties Billomat no longer returns: `Article::$unit`/`$purchasePriceCurrencyCode`/`$categoryId`, `Client::$customerportalUrl`, `Contact::$note`, `*Property::$position`, `TaxRate::$accountId`, `ReminderText::$dueDays`, `Country::$nameDe`/`$eu`, `User::$phone`/`$mobile`/`$fax`, `Invoice::$customerportalUrl`, `CreditNote::$customerportalUrl`, `Offer`/`Confirmation::$discountRate`/`$discountDate`/`$discountDays`/`$discountAmount`/`$customerportalUrl`, `Letter::$letterNumber`/`$number`/`$numberPre`/`$numberLength`/`$note`/`$customerportalUrl`, `Reminder::$reminderNumber`/`$number`/`$numberPre`/`$numberLength`/`$totalNet`/`$quote`, `Recurring::$numberPre`, `Incoming::$supplyDate`/`$dueDays`/`$paidAt`/`$intro`, `DeliveryNote::$totalGross`/`$totalNet`/`$netGross`/`$reduction`/`$totalGrossUnreduced`/`$totalNetUnreduced`/`$currencyCode`/`$quote`/`$customerportalUrl`, `$created` on `InvoiceItem`/`OfferItem`/`CreditNoteItem`/`ConfirmationItem`, `ReminderItem::$totalGross`/`$totalNet`/`$created` and `DeliveryNoteItem::$unitPrice`/`$taxName`/`$taxRate`/`$reduction`/`$totalGross`/`$totalNet`/`$totalGrossUnreduced`/`$totalNetUnreduced`/`$created`. They also disappear from the models' constructors and `toArray()`. Fields that are writable stay in the `*CreateOptions`/`*UpdateOptions`.
- `AbstractApi::mapHttpException()` and `HttpException::fromResponse()` (Symfony-specific; the mapping now lives in `BillomatHttpClient`).
- **BC break: status methods for endpoints Billomat doesn't have.** `OffersApi::undo()` and `ConfirmationsApi::undo()` are gone (`PUT …/undo` answers 404); use `uncancel()` or `unclear()`. `DeliveryNotesApi` and `LettersApi` lose `cancel()`, `clear()` and `undo()`: Billomat answers `cancel` with 400 ("cancellation not supported") and `clear`/`undo` with 404, so these methods could never succeed.

## [3.4.0] - 2026-10-24

### Changed
- **`TaxRate` samt `TaxRateCreateOptions` sowie die Read-Models `FreeText`, `EmailTemplate`, `ReminderText` und `CountryTax` werden generiert** (Definitionen `taxes.php`, `free-texts.php`, `email-templates.php`, `reminder-texts.php`, `country-taxes.php` unter `tools/codegen/definitions/`). Öffentliche API und `toArray()`-Ausgaben sind unverändert; neu sind nur Docblocks an jeder Property. Der Generator kann dafür jetzt abweichende Property-Namen und `toArray()`-Schlüssel (`EmailTemplate::$body` aus `text`, `ReminderText::$sort` aus `sorting`), Bool-Werte als 1/0 im Read-Model und Konstruktor-Parameter mit Default.
- **`Template` samt `TemplateCreateOptions`/`TemplateUpdateOptions`, die fünf Attribut-Definitionen `ArticleProperty`, `ClientProperty`, `SupplierProperty`, `IncomingProperty`, `UserProperty` und die geteilten `PropertyCreateOptions` werden generiert** (Definitionen `templates.php` und `*-properties.php` unter `tools/codegen/definitions/`, die Attribute teilen sich `shared/properties.php`). Öffentliche API und `toArray()`-Ausgaben sind unverändert; neu sind nur Docblocks an jeder Property. Der Generator kann dafür jetzt Null-Felder in `toArray()` des Read-Models weglassen (`filterNulls`). `Role` bleibt handgeschrieben, weil es die Rechte-Felder als `permissions`-Map bündelt.
- **Die Schlagworte aller zwölf Ressourcen werden generiert:** je `*Tag`, `*TagCloudEntry` und `*TagCreateOptions` für Artikel, Kunden, Lieferanten, Angebote, Auftragsbestätigungen, Rechnungen, Gutschriften, Lieferscheine, Mahnungen, Briefe, Abo-Rechnungen und Eingangsrechnungen (Definitionen `*-tags.php` unter `tools/codegen/definitions/`, gemeinsam über `shared/tags.php`). Öffentliche API und `toArray()`-Ausgaben sind unverändert; neu sind nur Docblocks an jeder Property. Der Generator kann dafür jetzt mehrere Definitionen aus einer Datei erzeugen und Ressourcen ohne Spec-Eintrag (die Tags von Auftragsbestätigungen, Lieferscheinen und Briefen sowie die Tag-Clouds dokumentiert Billomat nicht) ohne Drift-Abgleich generieren.
- **Kommentare, Zahlungen und Attributwerte werden generiert:** `*Comment` samt `*CommentCreateOptions` für Angebote, Auftragsbestätigungen, Rechnungen, Gutschriften, Lieferscheine, Briefe und Eingangsrechnungen, `*Payment` samt `*PaymentCreateOptions` für Rechnungen, Gutschriften und Eingangsrechnungen sowie `*PropertyValue` samt `*PropertyValueCreateOptions` für Artikel, Kunden, Lieferanten und Eingangsrechnungen (Definitionen `*-comments.php`, `*-payments.php` und `*-property-values.php` unter `tools/codegen/definitions/`, gemeinsam über `shared/comments.php`, `shared/payments.php` und `shared/property-values.php`). Öffentliche API und `toArray()`-Ausgaben sind unverändert; neu sind nur Docblocks an jeder Property. Der Generator kann dafür jetzt die Reihenfolge der `toArray()`-Schlüssel getrennt vom Konstruktor festlegen (`toArray`), Datumsfelder in Options als `DateTimeImmutable` führen (`dateObjects`) und Felder vom Typ `mixed` (Attributwerte).
- **Die Positionen der Dokumente werden generiert:** `*Item` für Angebote, Auftragsbestätigungen, Rechnungen, Gutschriften, Lieferscheine, Mahnungen und Abo-Rechnungen sowie die zugehörigen `*ItemCreateOptions` (außer Mahnungen, deren Positionen Billomat selbst anlegt). Definitionen `*-items.php` unter `tools/codegen/definitions/`, gemeinsam über `shared/items.php`; die Positionen von Auftragsbestätigungen und Lieferscheinen dokumentiert Billomat nicht und werden ohne Drift-Abgleich generiert. Öffentliche API und `toArray()`-Ausgaben sind unverändert; neu sind nur Docblocks an jeder Property.
- **Briefe, Mahnungen, Lieferscheine, Auftragsbestätigungen und Eingangsrechnungen werden generiert:** `Letter`, `Reminder`, `DeliveryNote`, `Confirmation` und `Incoming` samt ihren `*CreateOptions`/`*UpdateOptions` (Definitionen `letters.php`, `reminders.php`, `delivery-notes.php`, `confirmations.php` und `incomings.php` unter `tools/codegen/definitions/`). Öffentliche API und `toArray()`-Ausgaben sind unverändert; neu sind nur Docblocks an jeder Property. Der Generator kann dafür jetzt verschachtelte Listen im Read-Model (Steuerzeilen `taxes` und Positionen `*-items`, normalisiert über die neue interne Hilfsklasse `ListCaster`), `addItem()`/`getItems()` in den Create-Options und je Options-Klasse abweichende Typen (`ConfirmationUpdateOptions::$discountRate` bleibt `float`).
- **Rechnungen, Angebote, Gutschriften und Abo-Rechnungen werden generiert:** `Invoice`, `Offer`, `CreditNote` und `Recurring` samt ihren `*CreateOptions`/`*UpdateOptions` (Definitionen `invoices.php`, `offers.php`, `credit-notes.php` und `recurrings.php` unter `tools/codegen/definitions/`). Öffentliche API und `toArray()`-Ausgaben sind unverändert; neu sind nur Docblocks an jeder Property. Damit sind alle Dokument-Ressourcen generiert.
- **Die Read-Models `Country`, `Currency` und `User` werden generiert** (Definitionen `countries.php`, `currencies.php` und `users.php` unter `tools/codegen/definitions/`, ohne Drift-Abgleich, weil Billomat die Felder nicht dokumentiert). Öffentliche API und `toArray()`-Ausgaben sind unverändert; neu sind nur Docblocks an jeder Property. `Account`, `AccountQuota`, `Activity` und `SearchResult` bleiben handgeschrieben, weil sie eigene Methoden (`quota()`, `isUnlimited()`, `isSystemActivity()`) bzw. einen eingebetteten `Client` haben.
- **`IncomingCategory`, `RecurringEmailReceiver` und `RecurringEmailReceiverCreateOptions` werden generiert** (Definitionen `incoming-categories.php` und `recurring-email-receivers.php` unter `tools/codegen/definitions/`). Öffentliche API und `toArray()`-Ausgaben sind unverändert; neu sind nur Docblocks an jeder Property, der Doku-Link der Empfänger zeigt jetzt auf die richtige Billomat-Seite (`abo-rechnungen/empfaenger/`). Der Generator kann dafür jetzt nicht-nullbare Enums, die über `fromApi()` hydriert werden.
- **`TaxRate::$isDefault` und `Template::$isDefault` lesen `is_default` wie alle anderen Models über `ScalarCaster`.** Bisher galt nur `1`/`"1"` (bei `Template` auch `true`) als `true`; jetzt auch z. B. `"true"`. `"0"`, `"false"`, `""` und ein fehlendes Feld ergeben weiterhin `false`.
- **Die Positionen von `Invoice`, `Offer`, `CreditNote`, `Recurring`, `Reminder`, `DeliveryNote` und `Confirmation` überspringen Einträge, die keine Objekte sind**, statt einen `TypeError` zu werfen, und sind immer eine Liste (`list<…>`), auch wenn Billomat sie mit Schlüsseln liefert.
- **Enum-Felder von `Template`, den `*Property`-Models, `InvoicePayment`/`CreditNotePayment`/`IncomingPayment` (`type`) sowie `Invoice::$status` werfen bei Werten, die keine Strings sind, keinen `TypeError` mehr**, sondern ergeben `null` wie unbekannte Werte.

## [3.3.0] - 2026-10-24

### Changed
- **`Client`, `Contact`, `Supplier` und `Article` samt ihren `*CreateOptions`/`*UpdateOptions` sowie das Read-Model `Unit` werden generiert.** `composer generate` erzeugt sie aus `docs/spec/billomat.json` und je einer Definition unter `tools/codegen/definitions/` (`clients.php`, `contacts.php`, `suppliers.php`, `articles.php`, `units.php`). Typen, Defaults und Enum-Werte der schreibbaren Felder kommen damit direkt aus der Billomat-Doku. Öffentliche API und Payloads sind unverändert (Properties, Typen, Defaults, Konstruktor-Reihenfolge und `toArray()`-Ausgaben gegen den bisherigen Stand geprüft); geändert haben sich nur Docblocks und die Reihenfolge der Properties in den Options. `composer generate:check` läuft in der CI und schlägt fehl, wenn generierte Dateien von Hand geändert wurden oder die Spec ein Feld nennt, das die Definition nicht kennt.

## [3.2.0] - 2026-10-21

### Changed
- **`UnexpectedResponseException` statt `\RuntimeException`.** Antwortet Billomat mit 2xx, passt der Body aber nicht zur erwarteten Struktur (z. B. fehlt das Wrapper-Element `client`), werfen die API-Methoden jetzt `Justpilot\Billomat\Exception\UnexpectedResponseException`. Damit fängt `catch (BillomatException)` auch diesen Fall. Kein BC-Break: Die neue Exception erbt über `BillomatException` weiterhin von `\RuntimeException`, Meldungstexte sind unverändert.

### Removed
- **Abhängigkeit `symfony/http-foundation`.** Sie wurde nur für die `Response::HTTP_*`-Konstanten im Exception-Mapping gebraucht. Konsumenten installieren dadurch ein Paket weniger und erben keine Sicherheits-Bumps mehr daraus (zuletzt CVE-2026-108082). Wer `symfony/http-foundation` selbst nutzt, muss es im eigenen `composer.json` verlangen.

## [3.1.0] - 2026-10-20

### Added
- **`ClientPropertyValuesApi`** (`$billomat->clientPropertyValues`) für `/client-property-values` samt `ClientPropertyValue`-Modell und `ClientPropertyValueCreateOptions`. Werte von Kunden-Attributen ließen sich bisher nicht über das SDK lesen oder setzen; für Artikel, Lieferanten und Eingangsrechnungen gab es das Gegenstück schon.
- **`customfield` und Zeitstempel auf allen Read-Modellen**, für die Billomat sie liefert: `?string $customfield` (59 Modelle), `?DateTimeImmutable $updated` (22) und `?DateTimeImmutable $created`, wo es bisher fehlte (9). Welche Ressource welches Feld liefert, ist in `tests/Model/MetadataFieldsTest.php` festgehalten.
- **E-Rechnungs-Felder** (ZUGFeRD/Factur-X, XRechnung), die Billomat liefert, aber nicht dokumentiert – nur lesend: `Client::$facturxIdentifier`/`$sellerIdentifier`, `Account::$taxAuthorityNumber`/`$facturxFieldsEnabled`, `Invoice::$facturxReference`, `InboxDocument::$xrechnungExtractionsId`.
- **Neue Beleg-Felder:** `Reminder::$paidAmount`/`$openAmount`/`$reminderLevel`/`$reminderLevelName`/`$isOld`, `ReminderItem::$total`/`$isCharge`, `Recurring::$status`/`$inProgress`/`$ultimo`/`$discountAmount`/`$totalNetUnreduced`/`$totalGrossUnreduced`, die Versandoptionen `Recurring::$emailFilename`/`$emailBcc`/`$letterColor`/`$letterDuplex`/`$letterPaperWeight` sowie `$offerId`/`$confirmationId`, `Offer::$validityDate`, `OfferItem::$optional`, `CreditNoteItem::$taxRateReverseCharge`, `DeliveryNote::$offerId`, `Letter::$supplierId`, `Invoice::$digProceeded` und `$totalReduction` auf Rechnung, Angebot, Auftragsbestätigung, Gutschrift und Abo-Rechnung. Die beschreibbaren davon waren in den `*Options` schon vorhanden, ließen sich aber nicht zurücklesen.
- **Neue Felder an Stammdaten und Nebenressourcen:** `Article::$archived`/`$type` (`ArticleType`)/`$revenueAccountNumber`/`$costCenter`/`$purchasePriceNetGross` (`NetGross`), `Client::$digExclude`, `Supplier::$isEuCountry`/`$locale`/`$creditorAccountNumber`/`$creditorIdentifier`/`$costsGross`/`$costsNet`, `Incoming::$clientNumber`/`$expenseAccountNumber`/`$category`/`$pageCount`/`$hasAttachment`, `Contact::$name`/`$address`/`$www`/`$locale`, an allen Zahlungen `$currencyCode`/`$quote`/`$paymentGroup`/`$finapiTransactionId`/`$finapiTransactionAmount`/`$documentTitle`, an allen Beleg-Kommentaren `$public`/`$byClient`/`$emailId`/`$clientId`, `InboxDocument::$userId`/`$documentType`/`$pageCount`/`$fileUrl`/`$metadata`, `User::$locale`/`$timezone`/`$isAccountOwner`, `FreeText::$type`/`$name`/`$isDefault`, `TaxRate::$canDelete`, `Template::$isBackgroundAvailable` sowie `Country::$id`/`Currency::$id`.
- Neue Felder aus den aktuellen API-Antworten: `EmailTemplate::$type`/`$bcc`, `ReminderText::$chargeName`/`$chargeDescription`/`$chargeAmount`, `*Property::$isNvl` (Article/Client/Supplier/Incoming/User) und `PropertyCreateOptions::$isNvl`.

### Fixed
- **Status nach `complete()` ging verloren.** Billomat liefert inzwischen `COMPLETED` (Auftragsbestätigungen, Briefe) bzw. `CREATED` (Lieferscheine). Die Enums `ConfirmationStatus`, `LetterStatus` und `DeliveryNoteStatus` kennen diese Werte jetzt; vorher war `status` danach `null`.
- **Felder, die immer `null` blieben, weil Billomat sie umbenannt hat:** `EmailTemplate::$body` liest jetzt `text`, `ReminderText::$sort` liest `sorting`, `Supplier::$bankBic` liest `bank_swift`, `Incoming::$incomingNumber` liest `number`. Die alten Feldnamen bleiben jeweils als Fallback.
- `Role::$permissions` enthält jetzt auch `suppliers`, `incomings`, `letters`, `banking`, `net_income_statement` und `accounting_export`.
- Spec-Audit ordnete `kunden/attribute` fälschlich `ClientPropertiesApi` (Definitionen) zu und meldete die fehlenden Attribut-Werte deshalb nicht.

### Deprecated
- **71 Read-Model-Properties, die Billomat nicht (mehr) liefert und nicht dokumentiert** – sie waren immer `null` (bzw. `0.0`). Am Docblock jeweils mit `@deprecated` markiert, Entfernung in 4.0. Betroffen u. a.: `customerportalUrl` (Kunde, Rechnung, Angebot, Auftragsbestätigung, Gutschrift, Lieferschein, Brief), die Nummernfelder an `Letter` und `Reminder`, alle Beträge/Steuern an `DeliveryNote` und `DeliveryNoteItem`, `created` an Belegpositionen, `discount*` an `Offer`/`Confirmation`, `Incoming::$supplyDate`/`$dueDays`/`$paidAt`/`$intro`, `Article::$unit`/`$purchasePriceCurrencyCode`/`$categoryId`, `position` an den Attribut-Definitionen, `User::$phone`/`$mobile`/`$fax`, `TaxRate::$accountId`, `Country::$nameDe`/`$eu`, `ReminderText::$dueDays`, `Contact::$note`. Felder, die die Billomat-Doku noch beschreibt (z. B. `freeTextId`), bleiben unverändert.
- **`DunningLevelsApi` / `$billomat->dunningLevels`.** `/dunning-levels` antwortet mit `404 resource not found` und ist in der Billomat-Doku nicht beschrieben. Mahnstufen über `ReminderTextsApi` lesen. Wird in 4.0 entfernt.

### Documentation
- Read-Modell-Tabellen unter `docs/resources/` um alle neuen Properties ergänzt; veraltete Properties stehen jeweils als Hinweis unter der Tabelle.
- `docs/spec/billomat.json` aus der aktuellen Billomat-Doku neu erzeugt: Das Feld `address` beim Anlegen von Belegen ist dort jetzt als veraltet markiert („wird nicht mehr verarbeitet – bitte contact_id oder client_id verwenden“).

## [3.0.0] - 2026-10-20

### Breaking Changes
- **Symfony 7.4 wird nicht mehr unterstützt.** Alle `symfony/*`-Abhängigkeiten verlangen jetzt `^8.1`; `symfony/http-foundation` mindestens `^8.1.7`. Die öffentliche SDK-API ist unverändert. **Migration:** Projekte auf Symfony 8.1+ können ohne Codeänderung auf `^3.0` wechseln; Projekte auf Symfony 7.x bleiben auf `^2.1`.

### Removed
- Ungenutzte Laufzeit-Abhängigkeiten `symfony/serializer` und `symfony/options-resolver` entfernt. Weder `src/` noch die Tests haben sie verwendet; Konsumenten installieren dadurch weniger transitive Pakete.

### Security
- **CVE-2026-108082** (ReDoS in `HeaderUtils::split()` über den `Cache-Control`-Header) in `symfony/http-foundation` ist durch die neue Untergrenze `^8.1.7` ausgeschlossen. Das SDK nutzt aus `http-foundation` nur die `Response::HTTP_*`-Konstanten und war deshalb nicht direkt angreifbar; `composer audit` hat bei Konsumenten trotzdem angeschlagen.

### Build
- Dev-Tooling aktualisiert: PHPUnit 12.5 → 13.4, Infection 0.29 → 0.35, PHPStan 2.2 → 2.3 (Extensions 2.1), Rector 2.4 → 2.7, PHP-CS-Fixer 3.95.27.
- `rector.php`: Das in Rector 2.7 entfernte `PHPUnitSetList::PHPUNIT_100` wurde durch `withComposerBased(phpunit: true)` ersetzt (wählt die Regeln passend zur installierten PHPUnit-Version). Neue Rector-Regeln haben redundante `@return $this`-Docblocks und überflüssige `null`-Named-Args entfernt.
- CI-Matrix: Symfony-7.4-Job entfernt, Floor ist jetzt Symfony 8.1.
- **Mutation-Testing im CI wieder lauffähig.** Unter Coverage hat PHPUnit 442 Unit-Tests als „Risky“ geflaggt (inzidentell ausgeführte Klassen wie `BillomatConfig`, `BillomatHttpClient`, `ScalarCaster` ohne Coverage-Metadaten), wodurch Infection schon beim initialen Testlauf abbrach. 86 Testklassen haben die fehlenden `#[UsesClass]`-Attribute erhalten; die Suite läuft unter Coverage jetzt ohne Risky-Tests.
- `infection.json5`: `minMsi`/`minCoveredMsi` von 70/75 auf 60 gesenkt. Die alten Schwellen wurden wegen des Abbruchs nie geprüft; der tatsächliche Score liegt bei 62 %. Die Schwellen dienen jetzt als Untergrenze gegen Regressionen und sollen mit besseren Tests schrittweise angehoben werden.

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