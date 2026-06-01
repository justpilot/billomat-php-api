# Billomat PHP API SDK

[![PHP Version](https://img.shields.io/badge/PHP-8.4%2B-8892BF.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-Apache_2.0-blue.svg)](LICENSE)
[![Packagist](https://img.shields.io/badge/packagist-justpilot%2Fbillomat--php--api-orange.svg)](https://packagist.org/packages/justpilot/billomat-php-api)
[![Tests](https://img.shields.io/badge/tests-PHPUnit_12-blue.svg)](https://phpunit.de/)
[![PHPStan](https://img.shields.io/badge/PHPStan-level_max-brightgreen.svg)](https://phpstan.org/)

Modernes, typisiertes PHP 8.4+ SDK für die [Billomat-API](https://www.billomat.com/api/) — basiert auf Symfony-Komponenten, mit Read-Modellen, getrennten Write-Optionen und einer sauberen Exception-Hierarchie.

> Unoffizielles SDK. Dieses Projekt steht in keiner offiziellen Verbindung zur Billomat GmbH und wird unabhängig entwickelt und gepflegt.

## Inhalt

- [Features](#features)
- [Voraussetzungen](#voraussetzungen)
- [Installation](#installation)
- [Quickstart](#quickstart)
- [Ressourcen](#ressourcen)
- [Konfiguration](#konfiguration)
- [Fehlerbehandlung](#fehlerbehandlung)
- [Tests](#tests)
- [Beispiele](#beispiele)
- [Beitragen](#beitragen)
- [Sicherheit](#sicherheit)
- [Lizenz](#lizenz)

## Features

- PHP 8.4+ mit `final readonly` Models, Enums, Constructor Property Promotion und Named Arguments.
- HTTP-Layer auf Basis von `symfony/http-client`. Eigene Client-Implementierung injizierbar (Logging, Retry, Mock).
- Eine `*Api`-Klasse pro Billomat-Ressource mit einheitlichen Verben (`list`, `get`, `create`, `update`, `delete`) plus ressourcenspezifischen Aktionen (`complete`, `cancel`, `pdf`, `thumb` …).
- Typisierte Write-Modelle (`*CreateOptions` / `*UpdateOptions`) statt loser Arrays.
- Zentrale Exception-Hierarchie mit Mapping auf HTTP-Status-Codes (401/403, 404, 400/422).
- Vollständige Test-Suite (Unit-Tests mit `MockHttpClient`, optionale Integrationstests gegen den Billomat-Sandbox).
- Reine Library — kein Framework-Bootstrap, keine globale Konfiguration.

## Voraussetzungen

- PHP 8.4 oder neuer
- Composer
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

## Ressourcen

Jede Ressource ist als `public readonly`-Eigenschaft auf dem `BillomatClient` zugreifbar.

| Ressource | Zugriff | Endpunkte | Doku |
|---|---|---|---|
| Kunden | `$billomat->clients` | `/clients`, `/clients/myself` | [docs/resources/clients.md](docs/resources/clients.md) |
| Rechnungen | `$billomat->invoices` | `/invoices`, `/invoices/{id}/complete`, `/invoices/{id}/cancel`, `/invoices/{id}/pdf` | [docs/resources/invoices.md](docs/resources/invoices.md) |
| Rechnungspositionen | `$billomat->invoiceItems` | `/invoice-items` | [docs/resources/invoice-items.md](docs/resources/invoice-items.md) |
| Zahlungen | `$billomat->invoicePayments` | `/invoice-payments` | [docs/resources/invoice-payments.md](docs/resources/invoice-payments.md) |
| Steuersätze | `$billomat->taxes` | `/taxes` | [docs/resources/taxes.md](docs/resources/taxes.md) |
| Vorlagen | `$billomat->templates` | `/templates`, `/templates/{id}/thumb` | [docs/resources/templates.md](docs/resources/templates.md) |
| Einstellungen | `$billomat->settings` | `/settings` | [docs/resources/settings.md](docs/resources/settings.md) |

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

## Fehlerbehandlung

Alle vom SDK geworfenen Exceptions erben von `BillomatException`. HTTP-Fehler werden auf spezialisierte Subklassen abgebildet:

| Statuscode | Exception |
|---|---|
| 401, 403 | `AuthenticationException` |
| 404 | `NotFoundException` |
| 400, 422 | `ValidationException` |
| sonstige 4xx/5xx | `HttpException` |

`get($id)`-Methoden geben bei einer 404-Antwort `null` zurück, statt eine Exception zu werfen. Auf jeder `HttpException` sind `getStatusCode()` und `getResponseBody()` verfügbar — letzteres enthält den Roh-Body von Billomat und ist beim Debuggen oft hilfreich.

Beispiele und Patterns: [docs/error-handling.md](docs/error-handling.md).

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

## Beitragen

Pull Requests sind willkommen. Der Beitragsleitfaden inklusive Coding-Standards, Test-Anforderungen und dem Drei-Schichten-Muster pro Ressource liegt in [CONTRIBUTING.md](CONTRIBUTING.md).

## Sicherheit

Hinweise zum Umgang mit API-Keys und zur verantwortlichen Meldung von Sicherheitslücken stehen in [SECURITY.md](SECURITY.md).

## Lizenz

Apache License 2.0 — siehe [LICENSE](LICENSE).
