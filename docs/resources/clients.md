# Clients (Kunden)

API-Wrapper für die Billomat-Ressource „Kunden“ — entspricht den Endpunkten unter `/clients`.

## Zugriff

```php
$billomat->clients
```

`Justpilot\Billomat\Api\ClientsApi`, intern angelegt in `BillomatClient::__construct()`.

## Endpunkt-Übersicht

| Methode | HTTP | Pfad |
|---|---|---|
| `list()` | GET | `/clients` |
| `get($id)` | GET | `/clients/{id}` |
| `create($options)` | POST | `/clients` |
| `update($id, $options)` | PUT | `/clients/{id}` |
| `getMyself()` | GET | `/clients/myself` |

## Methoden

### `list(array $filters = []): list<Client>`

Listet Kunden mit optionalen Filtern.

```php
public function list(array $filters = []): array
```

Beliebige Filter aus der Billomat-Doku können übergeben werden (`per_page`, `page`, `name`, `client_number`, `email`, `order_by` …). Array-Werte werden als `key[]=…` serialisiert.

```php
$clients = $billomat->clients->list([
    'per_page' => 50,
    'order_by' => 'name+ASC',
]);

foreach ($clients as $client) {
    echo $client->id . ': ' . $client->name . PHP_EOL;
}
```

**Exceptions:** `AuthenticationException`, `HttpException`.

### `get(int $id): ?Client`

Holt einen Kunden anhand seiner ID. Liefert `null`, wenn Billomat 404 zurückgibt.

```php
$client = $billomat->clients->get(12345);

if ($client === null) {
    throw new RuntimeException('Kunde 12345 existiert nicht.');
}
```

**Exceptions:** `AuthenticationException`, `HttpException` (kein `NotFoundException`).

### `create(ClientCreateOptions $options): Client`

Legt einen neuen Kunden an. Der Payload wird mit dem Wrapper `{ "client": { … } }` gesendet.

```php
use Justpilot\Billomat\Api\ClientCreateOptions;

$opts = new ClientCreateOptions();
$opts->name = 'Acme GmbH';
$opts->firstName = 'Max';
$opts->lastName = 'Mustermann';
$opts->email = 'max@acme.example';
$opts->countryCode = 'DE';

$client = $billomat->clients->create($opts);
echo $client->id;
```

**Exceptions:** `ValidationException` (Pflichtfeld fehlt, Wert ungültig), `AuthenticationException`, `HttpException`.

### `update(int $id, ClientUpdateOptions $options): Client`

Partial-Update auf einem bestehenden Kunden. Nur Felder, die in `ClientUpdateOptions` gesetzt sind, werden an Billomat geschickt; alle anderen Felder bleiben unangetastet.

Einschränkung laut Billomat: Ein archivierter Kunde lässt sich nicht aktualisieren. Setze ggf. zuerst `archived = false`.

```php
use Justpilot\Billomat\Api\ClientUpdateOptions;

$opts = new ClientUpdateOptions();
$opts->name = 'Acme GmbH & Co. KG';
$opts->email = 'rechnungen@acme.example';

$updated = $billomat->clients->update(12345, $opts);
```

**Exceptions:** `ValidationException`, `AuthenticationException`, `NotFoundException`, `HttpException`.

### `getMyself(): Client`

Liefert das eigene Account-Profil als `Client`. Praktisch für Health-Checks („sind meine Credentials gültig?“) und zum Auslesen der eigenen Stammdaten.

```php
$me = $billomat->clients->getMyself();
echo 'Eingeloggt als: ' . $me->name;
```

**Exceptions:** `AuthenticationException`, `HttpException`.

## Write-Modell: `ClientCreateOptions`

Public, nullable Properties. `toArray()` filtert `null`-Werte heraus, damit Billomat Defaults aus den Account-Einstellungen ziehen kann.

### Stammdaten

| Property | Billomat-Feld | Typ | Beschreibung |
|---|---|---|---|
| `name` | `name` | `?string` | Firmenname (laut Billomat-Doku Pflicht für Firmen). |
| `archived` | `archived` | `?bool` | `1` = archiviert, `0` = aktiv. |
| `numberPre` | `number_pre` | `?string` | Präfix der Kundennummer (Default aus Einstellungen). |
| `number` | `number` | `?int` | Laufende Kundennummer (Default nächste freie). |
| `numberLength` | `number_length` | `?int` | Mindestlänge der Kundennummer. |
| `clientNumber` | `client_number` | `?string` | Frei vergebbare Kundennummer. |

### Adresse

| Property | Billomat-Feld | Typ |
|---|---|---|
| `street` | `street` | `?string` |
| `zip` | `zip` | `?string` |
| `city` | `city` | `?string` |
| `state` | `state` | `?string` |
| `countryCode` | `country_code` | `?string` (ISO 3166-1 Alpha-2, z. B. `DE`, `AT`, `CH`) |

### Ansprechpartner & Kontakt

| Property | Billomat-Feld | Typ |
|---|---|---|
| `firstName` | `first_name` | `?string` |
| `lastName` | `last_name` | `?string` |
| `salutation` | `salutation` | `?string` |
| `email` | `email` | `?string` |
| `phone` | `phone` | `?string` |
| `fax` | `fax` | `?string` |
| `mobile` | `mobile` | `?string` |
| `www` | `www` | `?string` (URL ohne Schema) |
| `note` | `note` | `?string` |
| `locale` | `locale` | `?string` (z. B. `de_DE`) |

### Steuer & Preisbasis

| Property | Billomat-Feld | Typ |
|---|---|---|
| `taxNumber` | `tax_number` | `?string` |
| `vatNumber` | `vat_number` | `?string` |
| `taxRule` | `tax_rule` | `?string` (`TAX`, `NO_TAX`, `COUNTRY` — siehe Enum [TaxRule](#verwendete-enums)) |
| `netGross` | `net_gross` | `?string` (`NET`, `GROSS`, `SETTINGS`) |
| `currencyCode` | `currency_code` | `?string` (ISO-Währungscode) |

### Debitor & Preisgruppe

| Property | Billomat-Feld | Typ |
|---|---|---|
| `debitorAccountNumber` | `debitor_account_number` | `?int` |
| `priceGroup` | `price_group` | `?int` |

### Bankdaten

| Property | Billomat-Feld | Typ |
|---|---|---|
| `bankAccountNumber` | `bank_account_number` | `?string` |
| `bankAccountOwner` | `bank_account_owner` | `?string` |
| `bankNumber` | `bank_number` | `?string` |
| `bankName` | `bank_name` | `?string` |
| `bankSwift` | `bank_swift` | `?string` |
| `bankIban` | `bank_iban` | `?string` |

### SEPA

| Property | Billomat-Feld | Typ |
|---|---|---|
| `sepaMandate` | `sepa_mandate` | `?string` |
| `sepaMandateDate` | `sepa_mandate_date` | `?string` (`YYYY-MM-DD`) |

### Zahlungsbedingungen

| Property | Billomat-Feld | Typ |
|---|---|---|
| `defaultPaymentTypes` | `default_payment_types` | `?string` (CSV, z. B. `CASH,BANK_TRANSFER`) |
| `reduction` | `reduction` | `?float` (Rabatt in %) |
| `discountRateType` | `discount_rate_type` | `?string` (`SETTINGS`, `ABSOLUTE`, `RELATIVE`) |
| `discountRate` | `discount_rate` | `?float` |
| `discountDaysType` | `discount_days_type` | `?string` |
| `discountDays` | `discount_days` | `?float` |
| `dueDaysType` | `due_days_type` | `?string` |
| `dueDays` | `due_days` | `?int` |
| `reminderDueDaysType` | `reminder_due_days_type` | `?string` |
| `reminderDueDays` | `reminder_due_days` | `?int` |
| `offerValidityDaysType` | `offer_validity_days_type` | `?string` |
| `offerValidityDays` | `offer_validity_days` | `?int` |
| `dunningRun` | `dunning_run` | `bool` (Default `false`) |

## Write-Modell: `ClientUpdateOptions`

Schmaler Subset für Partial-Updates. Nur Felder, die hier vorhanden und gesetzt sind, fließen in den PUT-Request. Boolean `archived` wird zu `1`/`0` serialisiert.

| Property | Billomat-Feld | Typ |
|---|---|---|
| `archived` | `archived` | `?bool` |
| `name` | `name` | `?string` |
| `street`, `zip`, `city`, `state`, `countryCode` | analog | `?string` |
| `firstName`, `lastName`, `salutation` | analog | `?string` |
| `phone`, `fax`, `mobile`, `email`, `www` | analog | `?string` |
| `taxNumber`, `vatNumber` | `tax_number`, `vat_number` | `?string` |
| `note` | `note` | `?string` |
| `reduction` | `reduction` | `?float` |
| `debitorAccountNumber` | `debitor_account_number` | `?int` |

## Read-Modell: `Client`

`final readonly class Client`. Properties (Auswahl der wichtigsten):

| Property | Typ | Notes |
|---|---|---|
| `id` | `?int` | Billomat-interne ID. Nullable, weil ein frisch konstruiertes Objekt theoretisch keine haben könnte. |
| `name` | `string` | Pflicht — nicht nullable. |
| `clientNumber` | `?string` | |
| `street`, `zip`, `city`, `state`, `countryCode` | `?string` | |
| `firstName`, `lastName`, `salutation` | `?string` | |
| `email`, `phone`, `fax`, `mobile`, `www` | `?string` | |
| `note`, `locale` | `?string` | |
| `taxNumber`, `vatNumber` | `?string` | |
| `taxRule`, `netGross`, `currencyCode` | `?string` | Werte spiegeln die Enum-Cases als String. |
| `debitorAccountNumber`, `priceGroup` | `?int` | |
| `archived`, `dunningRun` | `?bool` | |
| `reduction`, `discountRate`, `discountDays` | `?float` | |
| `dueDays`, `reminderDueDays`, `offerValidityDays` | `?int` | |

`Client::fromArray(array $data): self` hydriert ein Modell aus dem `client`-Knoten der API-Response. `toArray()` rendert das Modell zurück in Billomat-Feldnamen (snake_case) — gedacht für Debugging und Logging, **nicht** für Update-Calls.

## Verwendete Enums

Diese Enums tauchen als String-Werte in den Properties auf (Billomat liefert sie roh als String). Eine echte Enum-Typisierung gibt es bei diesem Modell aktuell nur indirekt.

- [`TaxRule`](../../src/Model/Enum/TaxRule.php): `TAX`, `NO_TAX`, `COUNTRY`
- [`NetGross`](../../src/Model/Enum/NetGross.php): `NET`, `GROSS`, `SETTINGS`
- [`ValueType`](../../src/Model/Enum/ValueType.php) (für `*Type`-Felder): `SETTINGS`, `ABSOLUTE`, `RELATIVE`

## Stolpersteine

- **Single-Item-List-Quirk:** Wenn Billomat in der Liste nur einen Kunden zurückliefert, schickt es ihn manchmal als Objekt statt in einer Liste. Das normalisiert `ClientsApi::list()` automatisch — du bekommst immer eine `list<Client>`.
- **Archivierte Kunden lassen sich nicht updaten.** Setze in `ClientUpdateOptions` zuerst `archived = false`, dann den Rest in einem zweiten Update-Request.
- **`number_length` < tatsächliche Länge.** Wenn `number` schon vorhandene Stellen hat, schlägt das Validierungs-Check auf Billomat-Seite fehl — `ValidationException` ist die Folge.

## End-to-End-Beispiel

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\ClientCreateOptions;
use Justpilot\Billomat\Api\ClientUpdateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Exception\ValidationException;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

// Anlegen
$create = new ClientCreateOptions();
$create->name = 'Beispiel GmbH';
$create->countryCode = 'DE';
$create->email = 'rechnungen@beispiel.de';

try {
    $client = $billomat->clients->create($create);
} catch (ValidationException $e) {
    fwrite(STDERR, "Anlage abgelehnt: " . $e->getResponseBody() . PHP_EOL);
    exit(1);
}

printf("Angelegt: #%d %s\n", $client->id, $client->name);

// Lesen
$reloaded = $billomat->clients->get($client->id);
var_dump($reloaded?->email);

// Aktualisieren
$update = new ClientUpdateOptions();
$update->note = 'Erstkontakt: ' . date('Y-m-d');
$billomat->clients->update($client->id, $update);

// Listen (z. B. nach Anlage)
$recent = $billomat->clients->list([
    'per_page' => 5,
    'order_by' => 'id+DESC',
]);
foreach ($recent as $c) {
    printf("- #%d %s\n", $c->id, $c->name);
}
```
