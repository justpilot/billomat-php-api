<!-- Quelle: SDK-Aggregation — keine 1:1 Billomat-URL; siehe die Sub-Ressourcen-Docs (countries.md, currencies.md, settings-units.md, settings-email-templates.md, settings-free-texts.md, settings-reminder-levels.md, settings-roles.md, settings-tax-free-countries.md, incoming-categories.md, users.md). -->

# Lookups (Stammdaten & Settings)

API-Wrapper für die elf read-only Lookup-Endpoints — Stammdaten und Account-Einstellungen, die andere Ressourcen referenzieren.

## Zugriff

```php
$billomat->countries           // ISO-Länderliste
$billomat->currencies          // Währungsliste
$billomat->units               // Einheiten (Stück, Stunde, …)
$billomat->dunningLevels       // Mahnstufen (numerisch)
$billomat->users               // Mitarbeiter/Benutzer des Accounts
$billomat->roles               // Rollen samt Permission-Map
$billomat->emailTemplates      // E-Mail-Vorlagen
$billomat->freeTexts           // Freitext-Bausteine
$billomat->reminderTexts       // Mahn-Textbausteine
$billomat->countryTaxes        // Steuerfreie Länder (ISO 3166)
$billomat->incomingCategories  // Eingangsrechnungs-Kategorien (String-ID)
```

## Modell

Alle elf APIs sind read-only — sie kennen keine `create`/`update`/`delete`-Verben. `countries` und `currencies` bieten nur `list()`; die übrigen zusätzlich ein `get($id)`. `UsersApi` hat einen Bonus-Endpoint `getMyself()` für den aktuell authentifizierten Account. Für `incomingCategories` ist `$id` ein **String**-Slug (z.B. `goods`), nicht numerisch.

Jede dieser Ressourcen liefert Stammdaten, die andere Modelle nur per ID/Code referenzieren — z. B. `Recurring::$emailTemplateId`, `Invoice::$currencyCode`, `InvoiceItem::$unit`.

## Endpunkt-Übersicht

| API | HTTP | Pfad | Methoden |
|---|---|---|---|
| `countries` | GET | `/countries` | `list()` |
| `currencies` | GET | `/currencies` | `list()` |
| `units` | GET | `/units`, `/units/{id}` | `list($filters?)`, `get($id)` |
| `dunningLevels` | GET | `/dunning-levels`, `/dunning-levels/{id}` | `list($filters?)`, `get($id)` |
| `users` | GET | `/users`, `/users/{id}`, `/users/myself` | `list($filters?)`, `get($id)`, `getMyself()` |
| `emailTemplates` | GET | `/email-templates`, `/email-templates/{id}` | `list($filters?)`, `get($id)` |
| `freeTexts` | GET | `/free-texts`, `/free-texts/{id}` | `list($filters?)`, `get($id)` |
| `reminderTexts` | GET | `/reminder-texts`, `/reminder-texts/{id}` | `list($filters?)`, `get($id)` |
| `roles` | GET | `/roles`, `/roles/{id}` | `list($filters?)`, `get($id)` |
| `countryTaxes` | GET | `/country-taxes`, `/country-taxes/{id}` | `list($filters?)`, `get($id)` |
| `incomingCategories` | GET | `/incoming-categories`, `/incoming-categories/{id}` | `list($filters?)`, `get($id)` |

## Methoden

### `countries`

Endpoint: `/countries`. Nur `list()` — kein `get()`.

```php
$countries = $billomat->countries->list();

$eu = array_values(array_filter(
    $countries,
    static fn ($c) => true === $c->eu,
));
```

#### Read-Modell: `Country`

`final readonly class Country`. Wire-Felder: `code` (oder `country_code`), `name`, `name_de`, `eu` (0/1).

| Property | Typ |
|---|---|
| `code` | `string` |
| `name` | `?string` |
| `nameDe` | `?string` |
| `eu` | `?bool` |

### `currencies`

Endpoint: `/currencies`. Nur `list()` — kein `get()`.

```php
$currencies = $billomat->currencies->list();
```

#### Read-Modell: `Currency`

`final readonly class Currency`. Wire-Felder: `code` (oder `currency_code`), `name`, `symbol`.

| Property | Typ |
|---|---|
| `code` | `string` |
| `name` | `?string` |
| `symbol` | `?string` |

### `units`

Endpoint: `/units`. `list($filters?)` und `get($id)`.

```php
$units = $billomat->units->list();
$piece = $billomat->units->get(42);
```

#### Read-Modell: `Unit`

`final readonly class Unit`.

| Property | Typ |
|---|---|
| `id` | `?int` |
| `name` | `string` |

### `dunningLevels`

Endpoint: `/dunning-levels`. `list($filters?)` und `get($id)`.

```php
$levels = $billomat->dunningLevels->list(['order_by' => 'position+ASC']);
```

#### Read-Modell: `DunningLevel`

`final readonly class DunningLevel`.

| Property | Typ |
|---|---|
| `id` | `?int` |
| `name` | `?string` |
| `position` | `?int` |
| `dueDays` | `?int` |
| `charge` | `?float` |
| `interest` | `?float` |

### `users`

Endpoint: `/users`. Bietet zusätzlich `getMyself()` für `/users/myself`.

```php
$me = $billomat->users->getMyself();
$colleague = $billomat->users->get(12345);
$all = $billomat->users->list();
```

#### Read-Modell: `User`

`final readonly class User`.

| Property | Typ |
|---|---|
| `id` | `?int` |
| `email` | `?string` |
| `firstName` | `?string` |
| `lastName` | `?string` |
| `salutation` | `?string` |
| `phone` | `?string` |
| `mobile` | `?string` |
| `fax` | `?string` |
| `roleId` | `?int` |

### `emailTemplates`

Endpoint: `/email-templates`. Vorlagen für die automatische Belegversendung.

```php
$templates = $billomat->emailTemplates->list();
$default = array_values(array_filter(
    $templates,
    static fn ($t) => true === $t->isDefault,
))[0] ?? null;
```

#### Read-Modell: `EmailTemplate`

`final readonly class EmailTemplate`. Das Wire-Feld `from` wird auf `fromAddress` gemappt (`from` ist in PHP ein Keyword).

| Property | Typ |
|---|---|
| `id` | `?int` |
| `name` | `?string` |
| `subject` | `?string` |
| `body` | `?string` |
| `fromAddress` | `?string` |
| `isDefault` | `?bool` |

### `freeTexts`

Endpoint: `/free-texts`. Freitext-Bausteine, die als `title`/`label`/`intro`/`note` auf Dokumenten wiederverwendet werden können (siehe `freeTextId` an `Invoice`, `Offer`, `Recurring`, …).

```php
$blocks = $billomat->freeTexts->list();
```

#### Read-Modell: `FreeText`

`final readonly class FreeText`.

| Property | Typ |
|---|---|
| `id` | `?int` |
| `title` | `?string` |
| `label` | `?string` |
| `intro` | `?string` |
| `note` | `?string` |

### `reminderTexts`

Endpoint: `/reminder-texts`. Textbausteine für Mahnungen — werden über `Reminder::$reminderTextId` referenziert (siehe [Reminders](reminders.md)).

```php
$texts = $billomat->reminderTexts->list(['order_by' => 'sort+ASC']);
```

#### Read-Modell: `ReminderText`

`final readonly class ReminderText`.

| Property | Typ |
|---|---|
| `id` | `?int` |
| `name` | `?string` |
| `subject` | `?string` |
| `header` | `?string` |
| `footer` | `?string` |
| `dueDays` | `?int` |
| `sort` | `?int` |

### `roles`

Endpoint: `/roles`. Mitarbeiter-Rollen samt zugehöriger Zugriffsrechte. Details: [settings-roles.md](settings-roles.md).

```php
$master = $billomat->roles->get(1);
$canDeleteInvoices = ($master?->permissions['invoices'] ?? null) === 'DELETE';
```

#### Read-Modell: `Role`

`final readonly class Role`.

| Property | Typ |
|---|---|
| `id` | `?int` |
| `name` | `?string` |
| `permissions` | `array<string,?string>` |

### `countryTaxes`

Endpoint: `/country-taxes`. Liste der Ländercodes, für die Billomat keine MwSt. berechnet. Details: [settings-tax-free-countries.md](settings-tax-free-countries.md).

```php
$taxFree = array_map(static fn ($c) => $c->countryCode, $billomat->countryTaxes->list());
```

#### Read-Modell: `CountryTax`

`final readonly class CountryTax`.

| Property | Typ |
|---|---|
| `id` | `?int` |
| `countryCode` | `?string` |

### `incomingCategories`

Endpoint: `/incoming-categories`. Vordefinierte Kategorie-Buckets für Eingangsrechnungen — die ID ist ein **String**-Slug. Details: [incoming-categories.md](incoming-categories.md).

```php
$goods = $billomat->incomingCategories->get('goods');
```

#### Read-Modell: `IncomingCategory`

`final readonly class IncomingCategory`.

| Property | Typ |
|---|---|
| `id` | `string` |
| `title` | `?string` |
| `description` | `?string` |

## Verwendete Enums

Keine.

## Stolpersteine

- **Alles read-only.** Keine dieser acht APIs hat `create`/`update`/`delete`. Wer einen neuen Mitarbeiter oder eine neue E-Mail-Vorlage anlegen will, muss das in der Billomat-Web-UI tun — die JSON-API gibt nichts her.
- **`countries` und `currencies` bieten kein `get()`.** Wer ein einzelnes Land oder eine einzelne Währung braucht, muss die volle Liste laden und clientseitig filtern. Die Listen sind klein (~250 Länder, ~170 Währungen).
- **`code` ist der Schlüssel, nicht `id`.** Für `Country` und `Currency` gibt es keine numerische ID — alle Querverweise (`Client::$countryCode`, `Invoice::$currencyCode`) zielen auf den ISO-Code.
- **Billomat antwortet uneinheitlich.** Manche Endpoints liefern `code`, andere `country_code`/`currency_code` — das Read-Modell akzeptiert beides. Keine Logik daran hängen, welches Feld zurückkommt.
- **`from` heisst im PHP-Modell `fromAddress`.** `from` ist in PHP reserviert, deshalb das Rename. `toArray()` rendert wieder als `from`.
- **`User::roleId`** ist ein Verweis auf eine im Billomat-Account hinterlegte Rolle — auflösbar via [`$billomat->roles->get($roleId)`](settings-roles.md).
- **`getMyself()`** ist der einzige Weg, programmatisch den aktuellen Account-Inhaber/Mitarbeiter zu identifizieren. Sehr nützlich für „Erstellt von"-Felder in Audit-Logs.
- **Filter-Strings nicht selbst URL-encoden.** `order_by=sort+ASC` wird vom HTTP-Layer korrekt durchgereicht; ein `%2B` statt `+` lehnt Billomat ab (siehe `BillomatHttpClient::buildBillomatQuery`).

## End-to-End-Beispiel

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\BillomatClient;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

// 1) Wer bin ich?
$me = $billomat->users->getMyself();
printf("Eingeloggt als %s %s <%s>\n", $me?->firstName, $me?->lastName, $me?->email);

// 2) Stammdaten cachen
$countriesByCode = [];
foreach ($billomat->countries->list() as $country) {
    $countriesByCode[$country->code] = $country;
}

$currenciesByCode = [];
foreach ($billomat->currencies->list() as $currency) {
    $currenciesByCode[$currency->code] = $currency;
}

$unitsById = [];
foreach ($billomat->units->list() as $unit) {
    if (null !== $unit->id) {
        $unitsById[$unit->id] = $unit;
    }
}

printf(
    "Stammdaten: %d Länder, %d Währungen, %d Einheiten\n",
    \count($countriesByCode),
    \count($currenciesByCode),
    \count($unitsById),
);

// 3) Default-E-Mail-Vorlage bestimmen
$default = null;
foreach ($billomat->emailTemplates->list() as $template) {
    if (true === $template->isDefault) {
        $default = $template;
        break;
    }
}
if (null !== $default) {
    printf("Default-Vorlage: #%d %s\n", $default->id, $default->name);
}

// 4) Mahnstufen für Reporting laden
foreach ($billomat->dunningLevels->list(['order_by' => 'position+ASC']) as $level) {
    printf(
        "Stufe %d: %s — fällig nach %d Tagen, %.2f EUR Mahngebühr\n",
        $level->position ?? 0,
        $level->name ?? '-',
        $level->dueDays ?? 0,
        $level->charge ?? 0.0,
    );
}

// 5) Freitext-Bausteine für eine Rechnungs-Vorlage durchsuchen
$blocks = $billomat->freeTexts->list();
$agbBlock = null;
foreach ($blocks as $block) {
    if (str_contains((string) $block->title, 'AGB')) {
        $agbBlock = $block;
        break;
    }
}
// $agbBlock->id kann jetzt in InvoiceCreateOptions::$freeTextId verwendet werden.
```
