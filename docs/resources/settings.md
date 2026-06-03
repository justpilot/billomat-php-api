<!-- Quelle: https://www.billomat.com/api/einstellungen/ -->

# Settings (Account-Einstellungen)

API-Wrapper für die Account-weiten Einstellungen unter `/settings`. Liefert Konfigurations-Defaults für Nummernkreise, Pflichtangaben in Dokumenten, Farben des Kundenportals und mehr.

## Zugriff

```php
$billomat->settings
```

`Justpilot\Billomat\Api\SettingsApi`.

## Endpunkt-Übersicht

| Methode | HTTP | Pfad |
|---|---|---|
| `get()` | GET | `/settings` |
| `update($options)` | PUT | `/settings` |

Es gibt keine `list`/`create`/`delete` — Einstellungen sind ein Singleton pro Account.

## Methoden

### `get(): Settings`

```php
$settings = $billomat->settings->get();

echo $settings->currencyCode;           // z. B. "EUR"
echo $settings->invoiceNumberPre;        // z. B. "RE-"
echo $settings->dueDays;                 // z. B. 14
```

Wirft eine `\RuntimeException`, wenn die API ein unerwartetes Response-Format liefert (sollte praktisch nicht vorkommen).

### `update(SettingsUpdateOptions $options): Settings`

Partial-Update. Nur Felder, die in `SettingsUpdateOptions` gesetzt sind, werden gesendet.

```php
use Justpilot\Billomat\Api\SettingsUpdateOptions;
use Justpilot\Billomat\Model\Enum\NetGross;

$opts = new SettingsUpdateOptions();
$opts->dueDays = 21;
$opts->netGross = NetGross::NET;
$opts->defaultEmailSender = 'rechnungen@meinefirma.de';

$updated = $billomat->settings->update($opts);
```

## Write-Modell: `SettingsUpdateOptions`

Nur die wirklich änderbaren Felder sind hier abgebildet — Read-only-Felder wie `*_number_next` fehlen bewusst.

### Kundenportal & Branding

| Property | Billomat-Feld | Typ |
|---|---|---|
| `bgcolor` | `bgcolor` | `?string` (Hex) |
| `color1` | `color1` | `?string` (Hex) |
| `color2` | `color2` | `?string` (Hex) |
| `color3` | `color3` | `?string` (Hex) |

### Sprache, Währung, Preisbasis

| Property | Billomat-Feld | Typ |
|---|---|---|
| `currencyCode` | `currency_code` | `?string` |
| `locale` | `locale` | `?string` (z. B. `de_DE`) |
| `netGross` | `net_gross` | `?NetGross` |

### SEPA & Nummernlogik

| Property | Billomat-Feld | Typ |
|---|---|---|
| `sepaCreditorId` | `sepa_creditor_id` | `?string` |
| `numberRangeMode` | `number_range_mode` | `?NumberRangeMode` |

### Kundennummern

| Property | Billomat-Feld | Typ |
|---|---|---|
| `clientNumberPre` | `client_number_pre` | `?string` |
| `clientNumberLength` | `client_number_length` | `?int` |

### Rechnungs-Defaults

| Property | Billomat-Feld | Typ |
|---|---|---|
| `invoiceNumberPre` | `invoice_number_pre` | `?string` |
| `invoiceNumberLength` | `invoice_number_length` | `?int` |
| `invoiceFilename` | `invoice_filename` | `?string` |
| `dueDays` | `due_days` | `?int` |
| `discountRate` | `discount_rate` | `?float` |
| `discountDays` | `discount_days` | `?int` |

### Angebots-Defaults

| Property | Billomat-Feld | Typ |
|---|---|---|
| `offerNumberPre` | `offer_number_pre` | `?string` |
| `offerNumberLength` | `offer_number_length` | `?int` |
| `offerValidityDays` | `offer_validity_days` | `?int` |

### Druck & E-Mail

| Property | Billomat-Feld | Typ |
|---|---|---|
| `printVersion` | `print_version` | `?bool` (als `1`/`0` serialisiert) |
| `defaultEmailSender` | `default_email_sender` | `?string` |

## Read-Modell: `Settings`

`final readonly class Settings`. Umfasst ~ 60 Properties; die wichtigsten Gruppen:

### Metadaten

| Property | Typ |
|---|---|
| `created`, `updated` | `?\DateTimeImmutable` |

### Account-Grundlagen

| Property | Typ |
|---|---|
| `bgcolor`, `color1`, `color2`, `color3` | `?string` |
| `currencyCode`, `locale` | `?string` |
| `netGross` | `?NetGross` |
| `sepaCreditorId` | `?string` |
| `numberRangeMode` | `?NumberRangeMode` |

### Nummernkreise (Präfix, Länge, „Next“ als read-only)

Pro Dokumenttyp existiert ein Block `xNumberPre`, `xNumberLength`, `xNumberNext` (read-only):

- `articleNumberPre/Length/Next`
- `clientNumberPre/Length/Next`
- `invoiceNumberPre/Length/Next`
- `offerNumberPre/Length/Next`
- `confirmationNumberPre/Length/Next`
- `creditNoteNumberPre/Length/Next`
- `deliveryNoteNumberPre/Length/Next`

### Dokument-Texte

Pro Dokumenttyp gibt es `xLabel`, `xIntro`, `xNote`, `xFilename`:

- `invoiceLabel`, `invoiceIntro`, `invoiceNote`, `invoiceFilename`
- analog für `offer`, `confirmation`, `creditNote`, `deliveryNote`
- `reminderFilename`, `reminderDueDays`
- `letterLabel`, `letterIntro`, `letterFilename`

### Konditionen

| Property | Typ |
|---|---|
| `dueDays` | `?int` |
| `discountRate` | `?float` |
| `discountDays` | `?int` |
| `offerValidityDays` | `?int` |

### Sonstiges

| Property | Typ | Notes |
|---|---|---|
| `priceGroups` | `array<int,string>` | Indexiert über die Preisgruppen-Nummer (z. B. `2 => "Preisgruppe 2"`) |
| `templateEngine` | `?TemplateEngine` | |
| `printVersion` | `?bool` | |
| `defaultEmailSender` | `?string` | |
| `bccAddresses` | `list<string>` | Normalisiert aus dem CSV-/Listen-Format, das Billomat liefert |
| `taxation` | `?string` | |

`Settings::fromArray()` macht die Typ-Normalisierung: Billomat liefert viele Werte als String (`"14"`, `"1"`, …), das Modell castet zu `int`/`float`/`bool`/`DateTimeImmutable`.

## Verwendete Enums

- [`NetGross`](../../src/Model/Enum/NetGross.php): `NET`, `GROSS`, `SETTINGS`.
- [`NumberRangeMode`](../../src/Model/Enum/NumberRangeMode.php): `IGNORE_PREFIX`, `CONSIDER_PREFIX`. Bestimmt, ob Nummern-Präfixe einen eigenen Nummernkreis bekommen.
- [`TemplateEngine`](../../src/Model/Enum/TemplateEngine.php): `DEFAULT`.
- [`ValueType`](../../src/Model/Enum/ValueType.php): `SETTINGS`, `ABSOLUTE`, `RELATIVE` — taucht in Settings selbst nicht direkt auf, ist aber ein verwandtes Werte-Modell für Clients/Invoices.

## Stolpersteine

- **Read-only-Felder ignorieren PUT-Versuche.** Felder wie `*NumberNext` oder `created`/`updated` lassen sich nicht setzen. Die `SettingsUpdateOptions`-Klasse blendet sie zur Sicherheit komplett aus.
- **`printVersion` als `?bool`.** Billomat erwartet `1`/`0` — das SDK macht das Mapping automatisch.
- **`priceGroups` ist zur Lesezeit aufgebaut.** Billomat schickt sie als Einzelfelder `price_group2`, `price_group3`, … `Settings::fromArray()` aggregiert sie in ein assoziatives Array, wobei der Schlüssel die Preisgruppen-Nummer ist.
- **`bccAddresses` als CSV.** Billomat liefert die Liste mal als kommagetrennten String, mal als verschachtelte `bcc_address`-Knoten. Das Modell normalisiert beides zu `list<string>`.
- **`locale`-Konsistenz.** Wenn du `locale` änderst, ändert sich nichts an `Accept-Language` im HTTP-Header des SDK — der ist fix auf `de-de`. Das ist meist OK, kann aber bei Sprachvergleichen relevant werden.

## End-to-End-Beispiel

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\SettingsUpdateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\Enum\NetGross;
use Justpilot\Billomat\Model\Enum\NumberRangeMode;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

// Aktuelle Settings lesen
$current = $billomat->settings->get();
printf("Aktuelle Standard-Fälligkeit: %d Tage\n", $current->dueDays ?? 0);
printf("Nummernkreismodus: %s\n", $current->numberRangeMode?->value ?? 'unbekannt');

// Ändern
$opts = new SettingsUpdateOptions();
$opts->dueDays = 21;
$opts->netGross = NetGross::NET;
$opts->numberRangeMode = NumberRangeMode::CONSIDER_PREFIX;
$opts->defaultEmailSender = 'rechnungen@meinefirma.de';

$updated = $billomat->settings->update($opts);
printf("Neue Fälligkeit gespeichert: %d Tage\n", $updated->dueDays ?? 0);
```
