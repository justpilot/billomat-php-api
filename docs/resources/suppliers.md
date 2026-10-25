<!-- Quelle: https://www.billomat.com/api/lieferanten/ -->

# Suppliers (Lieferanten)

API-Wrapper für Lieferanten unter `/suppliers` und ihre zwei Sub-Ressourcen (`/supplier-tags`, `/supplier-property-values`).

## Zugriff

```php
$billomat->suppliers               // Lieferanten selbst
$billomat->suppliers->tags            // Schlagworte an einem Lieferanten
$billomat->suppliers->propertyValues  // Werte benutzerdefinierter Lieferanten-Eigenschaften
```

Die Definitionen der Eigenschaften (Property-Schemata) liegen separat unter `$billomat->suppliers->properties` — siehe [Property-Definitionen](properties.md). Hier geht es ausschließlich um die Werte, die ein konkreter Lieferant für diese Definitionen trägt.

## Modell

Ein Supplier ist ein Lieferant — strukturell das Gegenstück zu [Clients](clients.md), aber für die eingehende Seite: Lieferanten erscheinen auf Eingangsbelegen (siehe [Incomings](incomings.md)) und können einem [Article](articles.md) als Standard-Lieferant zugewiesen werden (`Article::$supplierId`).

- **Adresse**: zerlegt in `street`, `zip`, `city`, `state`, `countryCode` (ISO-2). Das Read-Modell hat zusätzlich ein zusammengesetztes `address`-Feld, das Billomat selbst aus den Einzelteilen baut.
- **Kontakt**: `email`, `phone`, `fax`, `mobile`, `www` plus optionale persönliche Ansprache (`salutation`, `firstName`, `lastName`).
- **Steuer & Bank**: `taxNumber`, `vatNumber` und ein vollständiger Bankblock (`bankIban`, `bankBic`, …) für Überweisungen an den Lieferanten.

## Endpunkt-Übersicht

### `/suppliers`

| Methode | HTTP | Pfad |
|---|---|---|
| `list($filters?)` | GET | `/suppliers` |
| `get($id)` | GET | `/suppliers/{id}` |
| `create($options)` | POST | `/suppliers` |
| `update($id, $options)` | PUT | `/suppliers/{id}` |
| `delete($id)` | DELETE | `/suppliers/{id}` |

### `/supplier-tags`

| Methode | HTTP | Pfad |
|---|---|---|
| `list($supplierId)` | GET | `/supplier-tags?supplier_id={id}` |
| `cloud()` | GET | `/supplier-tags` |
| `get($id)` | GET | `/supplier-tags/{id}` |
| `create($options)` | POST | `/supplier-tags` |
| `delete($id)` | DELETE | `/supplier-tags/{id}` |

### `/supplier-property-values`

| Methode | HTTP | Pfad |
|---|---|---|
| `list($parentId?, $filters?)` | GET | `/supplier-property-values` |
| `get($id)` | GET | `/supplier-property-values/{id}` |
| `create($options)` | POST | `/supplier-property-values` |

## Suppliers

### `list(SupplierFilter|array $filters = []): list<Supplier>`

Filter laut Billomat-Doku: `name`, `client_number`, `email`, `first_name`, `last_name`, `country_code`, `creditor_identifier`, `note`, `incoming_id`, `tags`. Empfohlen ist der typisierte Filter `SupplierFilter`, dessen Felder die camelCase-Varianten dieser Namen sind (`countryCode`, `creditorIdentifier`, `incomingId` …; `incomingId` und `tags` als Liste, kommasepariert gesendet), dazu `orderBy`, `page`, `perPage` und `extra` für weitere Parameter. Ein Array funktioniert weiterhin; Array-Werte werden dort als `key[]=…` codiert.

```php
use Justpilot\Billomat\Api\OrderBy;
use Justpilot\Billomat\Api\SupplierFilter;

$german = $billomat->suppliers->list(new SupplierFilter(
    countryCode: 'DE',
    orderBy: OrderBy::asc('name'),
));
```

### `get(int $id): ?Supplier`

Liefert `null` bei 404.

### `create(SupplierCreateOptions $options): Supplier`

```php
use Justpilot\Billomat\Api\SupplierCreateOptions;

$opts = new SupplierCreateOptions(
    name: 'Hetzner Online GmbH',
    street: 'Industriestr. 25',
    zip: '91710',
    city: 'Gunzenhausen',
    countryCode: 'DE',
    vatNumber: 'DE812871812',
    bankIban: 'DE89370400440532013000',
    bankBic: 'COBADEFFXXX',
    currencyCode: 'EUR',
);

$supplier = $billomat->suppliers->create($opts);
```

### `update(int $id, SupplierUpdateOptions $options): Supplier`

Partial-Update — Felder, die `null` bleiben, bleiben unverändert.

### `delete(int $id): void`

## Write-Modell: `SupplierCreateOptions`

Konstruktor: `new SupplierCreateOptions(string $name, …)` — `name` ist Pflicht und erster Parameter. Alle Felder sind Konstruktor-Parameter mit Default und werden als benannte Argumente übergeben. Das Objekt ist unveränderlich (`final readonly`); jedes Feld hat eine `with*()`-Methode, die eine geänderte Kopie liefert (z. B. `$opts->withEmail('neu@example.com')`).

### Identifikation & Ansprache

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `name` | `name` | `string` (Pflicht) | Firmenname oder Kurzbezeichnung |
| `salutation` | `salutation` | `?string` | z. B. `Herr` / `Frau` |
| `firstName` | `first_name` | `?string` | Ansprechpartner |
| `lastName` | `last_name` | `?string` | Ansprechpartner |
| `note` | `note` | `?string` | Freie Notiz |

### Adresse

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `street` | `street` | `?string` | — |
| `zip` | `zip` | `?string` | PLZ |
| `city` | `city` | `?string` | — |
| `state` | `state` | `?string` | Bundesland/Region |
| `countryCode` | `country_code` | `?string` | ISO-3166-1 alpha-2, z. B. `DE` |

### Kontakt

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `email` | `email` | `?string` | — |
| `phone` | `phone` | `?string` | — |
| `fax` | `fax` | `?string` | — |
| `mobile` | `mobile` | `?string` | — |
| `www` | `www` | `?string` | Webseite |

### Steuern & Bank

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `taxNumber` | `tax_number` | `?string` | Lokale Steuernummer |
| `vatNumber` | `vat_number` | `?string` | USt-IdNr. |
| `bankAccountNumber` | `bank_account_number` | `?string` | Altes Kontonummer-Format |
| `bankAccountOwner` | `bank_account_owner` | `?string` | Kontoinhaber |
| `bankNumber` | `bank_number` | `?string` | Altes BLZ-Format |
| `bankName` | `bank_name` | `?string` | — |
| `bankIban` | `bank_iban` | `?string` | IBAN |
| `bankBic` | `bank_bic` | `?string` | BIC/SWIFT |
| `currencyCode` | `currency_code` | `?string` | Standardwährung für Eingangsbelege |

## Write-Modell: `SupplierUpdateOptions`

Spiegelt `SupplierCreateOptions` exakt, jedoch ist `name` nicht mehr Pflicht und alle Felder sind nullable. `null` heißt „nicht senden“, der Wert bleibt unverändert. Mit `Clear::Field` (`Justpilot\Billomat\Api\Clear`) wird das Feld als JSON-`null` gesendet und von Billomat geleert, z. B. `new SupplierUpdateOptions(note: Clear::Field)`.

## Read-Modell: `Supplier`

`final readonly class Supplier`. Wichtig: `name` ist auch im Read-Modell ein nicht-nullbarer `string`.

| Property | Typ |
|---|---|
| `id` | `?int` |
| `created` | `?\DateTimeImmutable` |
| `name` | `string` |
| `clientNumber` | `?int` (nur Read — von Billomat vergebene laufende Lieferantennummer) |
| `salutation`, `firstName`, `lastName` | `?string` |
| `street`, `zip`, `city`, `state`, `countryCode` | `?string` |
| `address` | `?string` (nur Read — server-seitig zusammengesetzte Adresszeile) |
| `note` | `?string` |
| `email`, `phone`, `fax`, `mobile`, `www` | `?string` |
| `taxNumber`, `vatNumber` | `?string` |
| `bankAccountNumber`, `bankAccountOwner`, `bankNumber`, `bankName`, `bankIban`, `bankBic` | `?string` |
| `currencyCode` | `?string` |
| `updated` | `?\DateTimeImmutable` |
| `isEuCountry` | `?bool` |
| `locale` | `?string` |
| `creditorAccountNumber` | `?string` |
| `creditorIdentifier` | `?string` |
| `costsGross` | `?Money` |
| `costsNet` | `?Money` |
| `customfield` | `?string` (Freies Zusatzfeld, siehe [Custom-Meta-Attribute](../concepts/custom-meta-attributes.md)) |

## Supplier Tags

Funktional identisch zu [Article Tags](articles.md#article-tags), bezogen auf einen Lieferanten statt einen Artikel.

### Methoden

```php
use Justpilot\Billomat\Api\SupplierTagCreateOptions;

$tag = $billomat->suppliers->tags->create(
    new SupplierTagCreateOptions(supplierId: $supplier->id, name: 'hosting'),
);

$tags  = $billomat->suppliers->tags->list($supplier->id);
$cloud = $billomat->suppliers->tags->cloud(); // aggregiert, mit count
$billomat->suppliers->tags->delete($tag->id);
```

Ein `update()` gibt es nicht — Tags werden gelöscht und neu angelegt.

### Write-Modell: `SupplierTagCreateOptions`

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `supplierId` | `supplier_id` | `int` (Pflicht) | — |
| `name` | `name` | `string` (Pflicht) | — |

### Read-Modell: `SupplierTag`

| Property | Typ |
|---|---|
| `id` | `?int` |
| `supplierId` | `int` |
| `name` | `string` |
| `customfield` | `?string` (Freies Zusatzfeld, siehe [Custom-Meta-Attribute](../concepts/custom-meta-attributes.md)) |

### Read-Modell: `SupplierTagCloudEntry`

| Property | Typ |
|---|---|
| `id` | `?int` |
| `name` | `string` |
| `count` | `int` |

## Supplier Property Values

Speichert die Werte für die in [`properties.md`](properties.md) definierten Lieferanten-Eigenschaften (`supplierProperties`). Eine Property-Definition (`SupplierProperty`) ist das Schema (Name + `PropertyType`), ein `SupplierPropertyValue` ist die konkrete Belegung an einem Lieferanten.

```php
use Justpilot\Billomat\Api\SupplierPropertyValueCreateOptions;

// Werte für einen Lieferanten auflisten
$values = $billomat->suppliers->propertyValues->list($supplier->id);

// Wert für die Property "Kreditorennummer Buchhaltung" (id = 17) setzen
$value = $billomat->suppliers->propertyValues->create(
    new SupplierPropertyValueCreateOptions(
        supplierId: $supplier->id,
        supplierPropertyId: 17,
        value: '70001',
    ),
);
```

Es gibt weder `update()` noch `delete()` — ein erneuter `create()`-Call mit derselben `supplierPropertyId` überschreibt den bestehenden Wert.

### Write-Modell: `SupplierPropertyValueCreateOptions`

Alle drei Werte sind Pflicht und werden über den Konstruktor gesetzt:

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `supplierId` | `supplier_id` | `int` (Pflicht) | — |
| `supplierPropertyId` | `supplier_property_id` | `int` (Pflicht) | ID der Property-Definition |
| `value` | `value` | `mixed` (Pflicht) | Bei `CHECKBOX` werden `0`/`1` erwartet |

### Read-Modell: `SupplierPropertyValue`

| Property | Typ |
|---|---|
| `id` | `?int` |
| `supplierId` | `int` |
| `supplierPropertyId` | `int` |
| `type` | `?string` (rohes API-Feld, z. B. `TEXTFIELD`) |
| `name` | `?string` (Name aus der Property-Definition) |
| `value` | `mixed` |
| `created`, `updated` | `?\DateTimeImmutable` |
| `customfield` | `?string` (Freies Zusatzfeld, siehe [Custom-Meta-Attribute](../concepts/custom-meta-attributes.md)) |

## Verwendete Enums

- [`PropertyType`](../../src/Model/Enum/PropertyType.php): `TEXTFIELD`, `TEXTAREA`, `CHECKBOX` — wird vom zugehörigen [`SupplierProperty`](../../src/Model/SupplierProperty.php) der Definition getragen, nicht vom Wert selbst.

Lieferanten haben sonst keine ressourcen-spezifischen Enums; alle Felder am `Supplier` selbst sind plain Strings (`countryCode`, `currencyCode`, …).

## Stolpersteine

- **Kein `update()` für Tags und Property-Values.** Tags löschen und neu anlegen; bei Property-Values überschreibt ein erneuter POST mit derselben `supplier_property_id` den bestehenden Eintrag.
- **`address` und `clientNumber` sind read-only.** Beide Felder erscheinen im `Supplier`-Read-Modell, fehlen aber in `SupplierCreateOptions`/`SupplierUpdateOptions` — Billomat baut sie selbst aus `street`/`zip`/`city`/`countryCode` bzw. einer laufenden Nummer.
- **`Supplier::$name` ist nicht-null.** Im Gegensatz zu fast allen anderen Feldern ist `name` im Read-Modell ein `string` (kein `?string`) — ein leerer Name liefert `''`, nicht `null`.
- **`bankBic` kommt als `bank_swift`.** Billomat liefert den BIC im Feld `bank_swift`; das Read-Modell mappt ihn auf `bankBic` (Fallback `bank_bic`).
- **Alte vs. moderne Bankdaten.** `bankAccountNumber`/`bankNumber` sind die deutschen Pre-SEPA-Felder. Für SEPA-Überweisungen sind `bankIban`/`bankBic` relevant; beide Paare lassen sich parallel setzen, aber Billomat nutzt für Überweisungen primär IBAN/BIC.
- **`currencyCode` als Default.** Der Wert wird auf neu angelegte Eingangsbelege ([Incomings](incomings.md)) übernommen, kann dort aber pro Beleg überschrieben werden.
- **Verknüpfung zu Artikeln läuft über `Article::$supplierId`.** Beim Löschen eines Suppliers verlieren betroffene Artikel nur den Verweis — sie werden nicht mitgelöscht.
- **`PropertyType::CHECKBOX` erwartet `0`/`1` als `value`.** Boolean-Strings (`"true"`/`"false"`) werden nicht erkannt.

## End-to-End-Beispiel

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\SupplierCreateOptions;
use Justpilot\Billomat\Api\SupplierPropertyValueCreateOptions;
use Justpilot\Billomat\Api\SupplierTagCreateOptions;
use Justpilot\Billomat\Api\SupplierUpdateOptions;
use Justpilot\Billomat\BillomatClient;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

// 1) Lieferant anlegen
$opts = new SupplierCreateOptions(
    name: 'Hetzner Online GmbH',
    street: 'Industriestr. 25',
    zip: '91710',
    city: 'Gunzenhausen',
    countryCode: 'DE',
    email: 'billing@hetzner.com',
    www: 'https://www.hetzner.com',
    vatNumber: 'DE812871812',
    bankIban: 'DE89370400440532013000',
    bankBic: 'COBADEFFXXX',
    currencyCode: 'EUR',
);

$supplier = $billomat->suppliers->create($opts);
printf("Lieferant #%d (%s) angelegt — Kreditor-Nr. %s\n",
    $supplier->id,
    $supplier->name,
    $supplier->clientNumber ?? 'tbd',
);

// 2) Tags vergeben
$billomat->suppliers->tags->create(
    new SupplierTagCreateOptions(supplierId: $supplier->id, name: 'hosting'),
);
$billomat->suppliers->tags->create(
    new SupplierTagCreateOptions(supplierId: $supplier->id, name: 'monthly-invoice'),
);

// 3) Custom-Property setzen (SupplierProperty #17 = "Kreditorennummer Buchhaltung")
$billomat->suppliers->propertyValues->create(
    new SupplierPropertyValueCreateOptions(
        supplierId: $supplier->id,
        supplierPropertyId: 17,
        value: '70001',
    ),
);

// 4) Kontaktdaten später nachpflegen — Partial-Update
$update = new SupplierUpdateOptions(phone: '+49 9831 505-0', note: 'Standard-Hostingpartner seit 2018.');
$billomat->suppliers->update($supplier->id, $update);

// 5) Cloud-Übersicht aller Supplier-Tags
foreach ($billomat->suppliers->tags->cloud() as $entry) {
    printf("%s (%d)\n", $entry->name, $entry->count);
}
```
