# Billomat PHP API SDK

[![PHP Version](https://img.shields.io/badge/PHP-8.5%2B-8892BF.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-Apache_2.0-blue.svg)](LICENSE)
[![Packagist](https://img.shields.io/badge/packagist-justpilot%2Fbillomat--php--api-orange.svg)](https://packagist.org/packages/justpilot/billomat-php-api)
[![PHPStan](https://img.shields.io/badge/PHPStan-level_max-brightgreen.svg)](https://phpstan.org/)
[![codecov](https://codecov.io/gh/justpilot/billomat-php-api/branch/main/graph/badge.svg)](https://codecov.io/gh/justpilot/billomat-php-api)

**Sprache / Language:** Deutsch (diese Datei) · [English](README.en.md)

Modernes, typisiertes PHP 8.5+ SDK für die [Billomat-API](https://www.billomat.com/api/) — läuft mit jedem PSR-18-HTTP-Client (Symfony, Guzzle, …), mit Read-Modellen, getrennten Write-Optionen und einer sauberen Exception-Hierarchie.

> Unoffizielles SDK. Dieses Projekt steht in keiner offiziellen Verbindung zur Billomat GmbH und wird unabhängig entwickelt und gepflegt.

## Inhalt

- [Voraussetzungen](#voraussetzungen)
- [Installation](#installation)
- [Quickstart](#quickstart)
- [Features](#features)
- [Ressourcen](#ressourcen)
- [Konfiguration](#konfiguration)
- [Pagination](#pagination)
- [Fehlerbehandlung](#fehlerbehandlung)
- [Logging & HTTP-Client](#logging--http-client)
- [Tests](#tests)
- [Beispiele](#beispiele)
- [Beitragen](#beitragen)
- [Sicherheit](#sicherheit)
- [Changelog](#changelog)
- [Lizenz](#lizenz)

## Voraussetzungen

- PHP 8.5 oder neuer
- Composer
- Ein PSR-18-HTTP-Client samt PSR-17-Factories, z. B. `symfony/http-client` mit `nyholm/psr7` oder `guzzlehttp/guzzle`
- Ein Billomat-Account mit aktiviertem API-Zugriff
- Ein API-Schlüssel aus den Account-Einstellungen unter `https://<billomatId>.billomat.net/app/settings/api`

`billomatId` ist der Subdomain-Teil deiner Billomat-URL. Bei `https://mycompany.billomat.net/` ist die ID also `mycompany`.

## Installation

```bash
composer require justpilot/billomat-php-api
```

Das SDK bringt keinen eigenen HTTP-Client mit, sondern braucht einen PSR-18-Client und PSR-17-Factories. Ist das Composer-Plugin `php-http/discovery` erlaubt, installiert es bei Bedarf automatisch eine Implementierung. Sonst einen Client explizit mitinstallieren:

```bash
composer require justpilot/billomat-php-api symfony/http-client nyholm/psr7
# oder
composer require justpilot/billomat-php-api guzzlehttp/guzzle
```

## Quickstart

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\InvoiceCreateOptions;
use Justpilot\Billomat\Api\InvoiceItemCreateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\Enum\PdfType;
use Justpilot\Billomat\Model\Money;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

// 1) Rechnung als Entwurf anlegen
$item = new InvoiceItemCreateOptions(quantity: 8.0, unitPrice: Money::of('95.00'), title: 'Konzeption');

$options = new InvoiceCreateOptions(clientId: 12345, title: 'Webentwicklung Mai 2026', items: [$item]);

$invoice = $billomat->invoices->create($options);

// 2) Rechnung abschließen (vergibt Nummer und erzeugt PDF)
$billomat->invoices->complete($invoice->id);

// 3) PDF herunterladen
$pdf = $billomat->invoices->pdf($invoice->id, PdfType::SIGNED, rawPdf: true);
file_put_contents(sprintf('invoice-%d.pdf', $invoice->id), $pdf);
```

Vollständige, ausführbare Beispiele liegen unter [examples/](examples/).

## Features

- PHP 8.5+, durchgängig typisiert (`final readonly` Models, Enums, Named Arguments).
- HTTP-Layer auf PSR-18/PSR-17: beliebiger Client (Symfony, Guzzle, …) injizierbar, sonst automatisch per `php-http/discovery` gefunden.
- Automatische Wiederholungen: 429 mit der von Billomat genannten Wartezeit (`Retry-After` bzw. `X-Rate-Limit-Reset`), GET-Requests zusätzlich bei 502/503/504 und Netzwerkfehlern mit exponentiellem Backoff — konfigurierbar über `RetryPolicy`.
- Eine `*Api`-Klasse je Billomat-Ressource mit einheitlichen Verben (`list`, `get`, `create`, `update`, `delete`) plus ressourcenspezifischen Aktionen (`complete`, `cancel`, `pdf`, `thumb` …).
- Auto-Pagination via `iterateAll()`-Generator und `listPage()` mit Metadaten.
- Typisierte List-Filter (`InvoiceFilter`, `ClientFilter`, …) mit Enums, `DateTimeImmutable` und `OrderBy`; einfache Arrays funktionieren weiterhin.
- Geldbeträge als exaktes `Money`-Value-Object (BcMath, Dezimal-Strings wie bei Billomat) statt `float` — ohne Rundungsfehler, mit Vergleich, Summen und deutscher Formatierung; siehe [docs/concepts/money.md](docs/concepts/money.md).
- Webhook-Empfang: `WebhookHeaders`, `WebhookPayload` und `WebhookEvent` (alle 83 Events) werten eingehende Requests aus und hydrieren das passende Model ([docs/concepts/webhooks.md](docs/concepts/webhooks.md)). Webhooks selbst werden nur im Billomat-UI angelegt.
- Strukturierte Exception-Hierarchie mit Mapping auf HTTP-Statuscodes (401/403, 404, 400/422, 429) und eigener `TransportException` für Netzwerkfehler.

## Ressourcen

Jede Ressource ist als `public readonly`-Eigenschaft auf dem `BillomatClient` zugreifbar. Die Tabelle gruppiert die Ressourcen nach Themengebiet — eine Zeile pro Doku-Datei.

### Stammdaten

| Ressource | Zugriff | Doku |
|---|---|---|
| Kunden | `$billomat->clients`, `clientPropertyValues` | [docs/resources/clients.md](docs/resources/clients.md) |
| Kundenschlagworte | `$billomat->clients->tags` | [docs/resources/client-tags.md](docs/resources/client-tags.md) |
| Ansprechpartner | `$billomat->contacts` | [docs/resources/contacts.md](docs/resources/contacts.md) |
| Lieferanten | `$billomat->suppliers`, `supplierTags`, `supplierPropertyValues` | [docs/resources/suppliers.md](docs/resources/suppliers.md) |
| Artikel | `$billomat->articles`, `articleTags`, `articlePropertyValues` | [docs/resources/articles.md](docs/resources/articles.md) |
| Property-Definitionen | `$billomat->articles->properties`, `clientProperties`, `supplierProperties`, `incomingProperties`, `userProperties` | [docs/resources/properties.md](docs/resources/properties.md) |

### Ausgangsbelege

| Ressource | Zugriff | Doku |
|---|---|---|
| Rechnungen | `$billomat->invoices`, `invoiceItems`, `invoicePayments`, `invoiceComments`, `invoiceTags` | [docs/resources/invoices.md](docs/resources/invoices.md), [items](docs/resources/invoice-items.md), [payments](docs/resources/invoice-payments.md), [comments](docs/resources/invoice-comments.md), [tags](docs/resources/invoice-tags.md) |
| Abo-Rechnungen | `$billomat->recurrings`, `recurringItems`, `recurringTags`, `recurringEmailReceivers` | [docs/resources/recurrings.md](docs/resources/recurrings.md) |
| Angebote | `$billomat->offers`, `offerItems`, `offerComments`, `offerTags` | [docs/resources/offers.md](docs/resources/offers.md) |
| Auftragsbestätigungen | `$billomat->confirmations`, `confirmationItems`, `confirmationComments`, `confirmationTags` | [docs/resources/confirmations.md](docs/resources/confirmations.md) |
| Lieferscheine | `$billomat->deliveryNotes`, `deliveryNoteItems`, `deliveryNoteComments`, `deliveryNoteTags` | [docs/resources/delivery-notes.md](docs/resources/delivery-notes.md) |
| Gutschriften | `$billomat->creditNotes`, `creditNoteItems`, `creditNoteComments`, `creditNoteTags`, `creditNotePayments` | [docs/resources/credit-notes.md](docs/resources/credit-notes.md) |
| Mahnungen | `$billomat->reminders`, `reminderItems`, `reminderTags` | [docs/resources/reminders.md](docs/resources/reminders.md) |
| Briefe | `$billomat->letters`, `letterComments`, `letterTags` | [docs/resources/letters.md](docs/resources/letters.md) |

### Eingangsbelege

| Ressource | Zugriff | Doku |
|---|---|---|
| Eingangsrechnungen | `$billomat->incomings`, `incomingComments`, `incomingPayments`, `incomingTags`, `incomingPropertyValues` | [docs/resources/incomings.md](docs/resources/incomings.md) |
| Eingangsrechnungs-Kategorien | `$billomat->incomingCategories` | [docs/resources/incoming-categories.md](docs/resources/incoming-categories.md) |
| Posteingang (Inbox) | `$billomat->inboxDocuments` | [docs/resources/inbox-documents.md](docs/resources/inbox-documents.md) |

### Account & Hilfs-Ressourcen

| Ressource | Zugriff | Doku |
|---|---|---|
| Konto-Info | `$billomat->account` | [docs/resources/account.md](docs/resources/account.md) |
| Aktivitätsverlauf | `$billomat->activities` | [docs/resources/activities.md](docs/resources/activities.md) |
| Suche | `$billomat->search` | [docs/resources/search.md](docs/resources/search.md) |
| Einstellungen | `$billomat->settings` | [docs/resources/settings.md](docs/resources/settings.md) |
| Steuersätze | `$billomat->taxes` | [docs/resources/taxes.md](docs/resources/taxes.md) |
| Steuerfreie Länder | `$billomat->countryTaxes` | [docs/resources/settings-tax-free-countries.md](docs/resources/settings-tax-free-countries.md) |
| Rollen | `$billomat->roles` | [docs/resources/settings-roles.md](docs/resources/settings-roles.md) |
| Vorlagen | `$billomat->templates` | [docs/resources/templates.md](docs/resources/templates.md) |
| Lookups (Countries, Currencies, Units, Users, EmailTemplates, FreeTexts, ReminderTexts) | `$billomat->countries`, `currencies`, `units`, `users`, `emailTemplates`, `freeTexts`, `reminderTexts` | [docs/resources/lookups.md](docs/resources/lookups.md) |

### Weitergehende Konzepte

Vertiefende Themen sind unter [docs/concepts/](docs/concepts/) gesammelt: [Authentifizierung](docs/concepts/authentication.md) (`X-AppId`/`X-AppSecret`), [API-Security](docs/concepts/api-security.md), [Pagination & Filtering](docs/concepts/pagination-and-filtering.md), [Custom Meta Attributes](docs/concepts/custom-meta-attributes.md), [Fehler & Rate-Limits](docs/concepts/errors-and-rate-limits.md), [Geldbeträge](docs/concepts/money.md) (`Money`) sowie [Webhooks](docs/concepts/webhooks.md) (nur UI-konfigurierbar, kein REST-Endpunkt — daher auch im SDK nicht abgebildet).

## Konfiguration

`BillomatClient::create()` ist der bequeme Einstieg:

```php
$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: '…',
    appId: null,        // optional, nur für eingetragene Drittanbieter-Apps
    appSecret: null,    // optional
    timeout: 10.0,      // Sekunden (nur für den vom SDK erzeugten Client)
    httpClient: null,   // optional eigener PSR-18-Client
    retry: new RetryPolicy(maxRetries: 2), // RetryPolicy::none() schaltet Wiederholungen ab
);
```

Für volle Kontrolle (etwa eigene `baseUri`) lässt sich `BillomatConfig` direkt verwenden:

```php
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Config\BillomatConfig;

$config = new BillomatConfig(
    billomatId: 'mycompany',
    apiKey: '…',
    timeout: 15.0,
);

$billomat = new BillomatClient($config);
```

Details zu allen Optionen und zur HTTP-Client-Injection: [docs/configuration.md](docs/configuration.md).

## Pagination

Für List-Endpunkte stehen zusätzlich zu `list()` zwei Helfer bereit. Gefiltert wird bei den Haupt-Ressourcen (Kunden, Lieferanten, Artikel, Benutzer, Vorlagen und alle Dokumenttypen) am besten über die typisierte Filterklasse (`ClientFilter`, `InvoiceFilter`, …); ein einfaches Array mit Billomat-Parametern funktioniert weiterhin.

```php
use Justpilot\Billomat\Api\ClientFilter;
use Justpilot\Billomat\Api\InvoiceFilter;
use Justpilot\Billomat\Api\OrderBy;
use Justpilot\Billomat\Model\Enum\InvoiceStatus;

// Auto-Pagination — lazy Generator, läuft über alle Seiten
foreach ($billomat->clients->iterateAll(new ClientFilter(countryCode: 'DE')) as $client) {
    // …
}

// Eine Seite mit Metadaten
$result = $billomat->clients->listPage(new ClientFilter(page: 3, perPage: 50));
echo "Seite {$result->info->page} / " . ($result->info->totalPages() ?? '?');

// Mehrfachwerte, Datum und Sortierung typisiert
$open = $billomat->invoices->list(new InvoiceFilter(
    status: [InvoiceStatus::OPEN, InvoiceStatus::OVERDUE],
    from: new DateTimeImmutable('2026-01-01'),
    orderBy: OrderBy::desc('date'),
));
```

Details und Beispiele: [docs/advanced/pagination.md](docs/advanced/pagination.md).

## Fehlerbehandlung

Alle vom SDK geworfenen Exceptions erben von `BillomatException`. HTTP-Fehler werden auf spezialisierte Subklassen abgebildet:

| Statuscode | Exception |
|---|---|
| 401, 403 | `AuthenticationException` |
| 404 | `NotFoundException` |
| 400, 422 | `ValidationException` |
| 429 | `RateLimitException` |
| sonstige 4xx/5xx | `HttpException` |
| keine Antwort (DNS, Timeout, Verbindungsabbruch) | `TransportException` |

`get($id)`-Methoden geben bei einer 404-Antwort `null` zurück, statt eine Exception zu werfen. `$e->isTransient()` sagt, ob ein späterer Versuch helfen kann (429, 502–504, Netzwerkfehler), `$e->mayHaveBeenProcessed()`, ob Billomat einen fehlgeschlagenen Schreib-Request trotzdem verarbeitet haben kann. Auf jeder `HttpException` sind `getStatusCode()`, `getRetryAfter()` und `getResponseBody()` verfügbar — letzteres enthält den Roh-Body von Billomat und ist beim Debuggen oft hilfreich.

Beispiele und Patterns: [docs/error-handling.md](docs/error-handling.md), Hintergrund zu Rate-Limits in [docs/concepts/errors-and-rate-limits.md](docs/concepts/errors-and-rate-limits.md).

## Logging & HTTP-Client

`httpClient` nimmt jeden PSR-18-Client (`Psr\Http\Client\ClientInterface`) entgegen. Ohne Angabe nutzt das SDK Symfonys `Psr18Client` mit dem konfigurierten Timeout, falls `symfony/http-client` installiert ist, sonst den per `php-http/discovery` gefundenen Client. Ein eigener Client konfiguriert seinen Timeout selbst. Einen Symfony-`HttpClientInterface` — etwa mit Logging oder Tracing dekoriert — wickelst du in `Psr18Client` ein:

```php
use Justpilot\Billomat\BillomatClient;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\TraceableHttpClient;

$inner = HttpClient::create(['timeout' => 10.0, 'max_duration' => 30.0]);
$decorated = new TraceableHttpClient($inner);

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
    httpClient: new Psr18Client($decorated),
);
```

Wiederholungen bei 429 und transienten Fehlern übernimmt das SDK selbst (`RetryPolicy`); ein zusätzlicher `RetryableHttpClient` ist nicht nötig.

Für Unit-Tests eignet sich `Symfony\Component\HttpClient\MockHttpClient`, eingewickelt in `Psr18Client`. Patterns und Eigenheiten des HTTP-Layers (Query-Encoding, `+` vs. `%2B`, Array-Filter) sind in [docs/advanced/http-layer.md](docs/advanced/http-layer.md) beschrieben.

## Tests

```bash
composer install
composer test            # Unit-Tests (schnell, ohne Netz)
composer test:all        # inkl. Integration gegen Sandbox (mit .env.test.local)
composer ci              # vollständige Qualitätspipeline: Lint + PHPStan + Rector + Tests
```

Die Unit-Suite läuft mit `MockHttpClient` und benötigt keine Credentials. Integrationstests unter `tests/Integration/` werden übersprungen, solange `BILLOMAT_ID` und `BILLOMAT_API_KEY` nicht gesetzt sind. Mehr dazu in [docs/testing.md](docs/testing.md).

## Beispiele

Lauffähige, kommentierte Skripte liegen unter [examples/](examples/) — vom Anlegen eines Kunden bis zum Abrufen eines PDFs. Jede Datei nimmt die Credentials aus Umgebungsvariablen, damit sie ohne Code-Änderungen direkt ausführbar ist.

Wechsel von `phobetor/billomat` oder `vrok/billomat-client`? Siehe [docs/migration-from-phobetor.md](docs/migration-from-phobetor.md).

## Beitragen

Pull Requests sind willkommen. Der Beitragsleitfaden inklusive Coding-Standards, Test-Anforderungen und dem Drei-Schichten-Muster pro Ressource liegt in [CONTRIBUTING.md](CONTRIBUTING.md). AI-Coding-Agents finden den projektspezifischen Kontext in [AGENTS.md](AGENTS.md) nach der [agents.md](https://agents.md/)-Konvention.

## Sicherheit

Hinweise zum Umgang mit API-Keys und zur verantwortlichen Meldung von Sicherheitslücken stehen in [SECURITY.md](SECURITY.md).

## Changelog

Versionshistorie und Release-Notes nach [Keep a Changelog](https://keepachangelog.com/) in [CHANGELOG.md](CHANGELOG.md). Das Projekt folgt [Semantic Versioning](https://semver.org/). Upgrade von 3.x auf 4.0: [UPGRADE-4.0.md](UPGRADE-4.0.md).

## Lizenz

Apache License 2.0 — siehe [LICENSE](LICENSE).
