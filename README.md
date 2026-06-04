# Billomat PHP API SDK

[![PHP Version](https://img.shields.io/badge/PHP-8.4%2B-8892BF.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-Apache_2.0-blue.svg)](LICENSE)
[![Packagist](https://img.shields.io/badge/packagist-justpilot%2Fbillomat--php--api-orange.svg)](https://packagist.org/packages/justpilot/billomat-php-api)
[![PHPStan](https://img.shields.io/badge/PHPStan-level_max-brightgreen.svg)](https://phpstan.org/)
[![codecov](https://codecov.io/gh/justpilot/billomat-php-api/branch/main/graph/badge.svg)](https://codecov.io/gh/justpilot/billomat-php-api)

**Sprache / Language:** Deutsch (diese Datei) · [English](README.en.md)

Modernes, typisiertes PHP 8.4+ SDK für die [Billomat-API](https://www.billomat.com/api/) — basiert auf Symfony-Komponenten, mit Read-Modellen, getrennten Write-Optionen und einer sauberen Exception-Hierarchie.

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

- PHP 8.4 oder neuer
- Composer
- Symfony-Komponenten: aktuelles LTS (`^7.4`) oder Latest Stable (`^8.0`). Ältere Symfony-Versionen werden nicht unterstützt.
- Ein Billomat-Account mit aktiviertem API-Zugriff
- Ein API-Schlüssel aus den Account-Einstellungen unter `https://<billomatId>.billomat.net/app/settings/api`

`billomatId` ist der Subdomain-Teil deiner Billomat-URL. Bei `https://mycompany.billomat.net/` ist die ID also `mycompany`.

## Installation

```bash
composer require justpilot/billomat-php-api
```

## Quickstart

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\InvoiceCreateOptions;
use Justpilot\Billomat\Api\InvoiceItemCreateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\Enum\InvoicePdfType;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

// 1) Rechnung als Entwurf anlegen
$item = new InvoiceItemCreateOptions(quantity: 8.0, unitPrice: 95.00);
$item->title = 'Konzeption';

$options = new InvoiceCreateOptions(clientId: 12345);
$options->title = 'Webentwicklung Mai 2026';
$options->addItem($item);

$invoice = $billomat->invoices->create($options);

// 2) Rechnung abschließen (vergibt Nummer und erzeugt PDF)
$billomat->invoices->complete($invoice->id);

// 3) PDF herunterladen
$pdf = $billomat->invoices->pdf($invoice->id, InvoicePdfType::SIGNED, rawPdf: true);
file_put_contents(sprintf('invoice-%d.pdf', $invoice->id), $pdf);
```

Vollständige, ausführbare Beispiele liegen unter [examples/](examples/).

## Features

- PHP 8.4+, durchgängig typisiert (`final readonly` Models, Enums, Named Arguments).
- HTTP-Layer auf `symfony/http-client`; eigenes `HttpClientInterface` injizierbar.
- Eine `*Api`-Klasse je Billomat-Ressource mit einheitlichen Verben (`list`, `get`, `create`, `update`, `delete`) plus ressourcenspezifischen Aktionen (`complete`, `cancel`, `pdf`, `thumb` …).
- Auto-Pagination via `iterateAll()`-Generator und `listPage()` mit Metadaten.
- Strukturierte Exception-Hierarchie mit Mapping auf HTTP-Statuscodes (401/403, 404, 400/422).

## Ressourcen

Jede Ressource ist als `public readonly`-Eigenschaft auf dem `BillomatClient` zugreifbar. Die Tabelle gruppiert die Ressourcen nach Themengebiet — eine Zeile pro Doku-Datei.

### Stammdaten

| Ressource | Zugriff | Doku |
|---|---|---|
| Kunden | `$billomat->clients` | [docs/resources/clients.md](docs/resources/clients.md) |
| Kundenschlagworte | `$billomat->clientTags` | [docs/resources/client-tags.md](docs/resources/client-tags.md) |
| Ansprechpartner | `$billomat->contacts` | [docs/resources/contacts.md](docs/resources/contacts.md) |
| Lieferanten | `$billomat->suppliers`, `supplierTags`, `supplierPropertyValues` | [docs/resources/suppliers.md](docs/resources/suppliers.md) |
| Artikel | `$billomat->articles`, `articleTags`, `articlePropertyValues` | [docs/resources/articles.md](docs/resources/articles.md) |
| Property-Definitionen | `$billomat->articleProperties`, `clientProperties`, `supplierProperties`, `incomingProperties`, `userProperties` | [docs/resources/properties.md](docs/resources/properties.md) |

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
| Lookups (Countries, Currencies, Units, DunningLevels, Users, EmailTemplates, FreeTexts, ReminderTexts) | `$billomat->countries`, `currencies`, `units`, `dunningLevels`, `users`, `emailTemplates`, `freeTexts`, `reminderTexts` | [docs/resources/lookups.md](docs/resources/lookups.md) |

### Weitergehende Konzepte

Vertiefende Themen sind unter [docs/concepts/](docs/concepts/) gesammelt: [Authentifizierung](docs/concepts/authentication.md) (`X-AppId`/`X-AppSecret`), [API-Security](docs/concepts/api-security.md), [Pagination & Filtering](docs/concepts/pagination-and-filtering.md), [Custom Meta Attributes](docs/concepts/custom-meta-attributes.md), [Fehler & Rate-Limits](docs/concepts/errors-and-rate-limits.md) sowie [Webhooks](docs/concepts/webhooks.md) (nur UI-konfigurierbar, kein REST-Endpunkt — daher auch im SDK nicht abgebildet).

## Konfiguration

`BillomatClient::create()` ist der bequeme Einstieg:

```php
$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: '…',
    appId: null,        // optional, nur für eingetragene Drittanbieter-Apps
    appSecret: null,    // optional
    timeout: 10.0,      // Sekunden
    httpClient: null,   // optional eigener HttpClientInterface
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

Für List-Endpunkte stehen zusätzlich zu `list()` zwei Helfer bereit:

```php
// Auto-Pagination — lazy Generator, läuft über alle Seiten
foreach ($billomat->clients->iterateAll(['country_code' => 'DE']) as $client) {
    // …
}

// Eine Seite mit Metadaten
$result = $billomat->clients->listPage(['per_page' => 50, 'page' => 3]);
echo "Seite {$result->info->page} / " . ($result->info->totalPages() ?? '?');
```

Details und Beispiele: [docs/advanced/pagination.md](docs/advanced/pagination.md).

## Fehlerbehandlung

Alle vom SDK geworfenen Exceptions erben von `BillomatException`. HTTP-Fehler werden auf spezialisierte Subklassen abgebildet:

| Statuscode | Exception |
|---|---|
| 401, 403 | `AuthenticationException` |
| 404 | `NotFoundException` |
| 400, 422 | `ValidationException` |
| sonstige 4xx/5xx | `HttpException` |

`get($id)`-Methoden geben bei einer 404-Antwort `null` zurück, statt eine Exception zu werfen. Auf jeder `HttpException` sind `getStatusCode()` und `getResponseBody()` verfügbar — letzteres enthält den Roh-Body von Billomat und ist beim Debuggen oft hilfreich.

Beispiele und Patterns: [docs/error-handling.md](docs/error-handling.md), Hintergrund zu Rate-Limits in [docs/concepts/errors-and-rate-limits.md](docs/concepts/errors-and-rate-limits.md).

## Logging & HTTP-Client

Der Symfony-HTTP-Client lässt sich vor der Übergabe an `BillomatClient` dekorieren — für Logging, Tracing, Retry oder Tests reicht die Standard-Symfony-Toolbox:

```php
use Justpilot\Billomat\BillomatClient;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\RetryableHttpClient;
use Symfony\Component\HttpClient\TraceableHttpClient;

$inner = HttpClient::create(['max_duration' => 30.0]);
$decorated = new TraceableHttpClient(new RetryableHttpClient($inner));

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
    httpClient: $decorated,
);
```

Für Unit-Tests eignet sich `Symfony\Component\HttpClient\MockHttpClient` als Drop-in. Patterns und Eigenheiten des HTTP-Layers (Query-Encoding, `+` vs. `%2B`, Array-Filter) sind in [docs/advanced/http-layer.md](docs/advanced/http-layer.md) beschrieben.

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

Versionshistorie und Release-Notes nach [Keep a Changelog](https://keepachangelog.com/) in [CHANGELOG.md](CHANGELOG.md). Das Projekt folgt [Semantic Versioning](https://semver.org/).

## Lizenz

Apache License 2.0 — siehe [LICENSE](LICENSE).
