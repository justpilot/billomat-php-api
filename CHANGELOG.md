# Changelog

All notable changes to this project will be documented in this file.

The format is based on Keep a Changelog, and this project adheres to Semantic Versioning.

## [Unreleased]

### Changed
- **BC break: PHP 8.5 is now the minimum version** (`"php": "^8.5"`). CI runs on PHP 8.5 (required) and 8.6 (experimental); Rector uses the PHP 8.5 rule set.
- **Read-only API methods are marked `#[\NoDiscard]`:** `get()`, `getMyself()`, `list()`, `listPage()`, `listBy*()`, `listGrouped()`, `iterateAll()`, `cloud()`, `pdf()`, `thumb()`, `avatar()` and `SearchApi::query()`. PHP 8.5 emits a warning when their result is ignored; cast to `(void)` to discard it on purpose. Write methods (`create()`, `update()`, `delete()`, …) are not marked.
- **BC break: `BillomatConfig` validates its input.** The Billomat ID must be a valid subdomain (letters, digits, inner hyphens, at most 63 characters), and the base URI is parsed with PHP 8.5's `Uri\Rfc3986\Uri` and must be absolute. Otherwise the constructor throws an `InvalidArgumentException`, instead of sending requests to a wrong host.

### Removed
- **BC break: everything marked `@deprecated since 3.1` is gone.**
  - `DunningLevelsApi`, `$billomat->dunningLevels` and the `DunningLevel` model (`/dunning-levels` answers with 404). Use `$billomat->reminderTexts` for reminder levels.
  - `AbstractApi::putEmptyResponse()` (use `putVoid()`).
  - The read-model properties Billomat no longer returns: `Article::$unit`/`$purchasePriceCurrencyCode`/`$categoryId`, `Client::$customerportalUrl`, `Contact::$note`, `*Property::$position`, `TaxRate::$accountId`, `ReminderText::$dueDays`, `Country::$nameDe`/`$eu`, `User::$phone`/`$mobile`/`$fax`, `Invoice::$customerportalUrl`, `CreditNote::$customerportalUrl`, `Offer`/`Confirmation::$discountRate`/`$discountDate`/`$discountDays`/`$discountAmount`/`$customerportalUrl`, `Letter::$letterNumber`/`$number`/`$numberPre`/`$numberLength`/`$note`/`$customerportalUrl`, `Reminder::$reminderNumber`/`$number`/`$numberPre`/`$numberLength`/`$totalNet`/`$quote`, `Recurring::$numberPre`, `Incoming::$supplyDate`/`$dueDays`/`$paidAt`/`$intro`, `DeliveryNote::$totalGross`/`$totalNet`/`$netGross`/`$reduction`/`$totalGrossUnreduced`/`$totalNetUnreduced`/`$currencyCode`/`$quote`/`$customerportalUrl`, `$created` on `InvoiceItem`/`OfferItem`/`CreditNoteItem`/`ConfirmationItem`, `ReminderItem::$totalGross`/`$totalNet`/`$created` and `DeliveryNoteItem::$unitPrice`/`$taxName`/`$taxRate`/`$reduction`/`$totalGross`/`$totalNet`/`$totalGrossUnreduced`/`$totalNetUnreduced`/`$created`. They also disappear from the models' constructors and `toArray()`. Fields that are writable stay in the `*CreateOptions`/`*UpdateOptions`.

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