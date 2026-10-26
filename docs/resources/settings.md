<!-- Source: https://www.billomat.com/api/einstellungen/ -->

# Settings (Einstellungen / account settings)

API wrapper for the account-wide settings at `/settings`. Holds the defaults for number ranges, document texts, client portal colors and more.

## Access

```php
$billomat->settings
```

`Justpilot\Billomat\Api\SettingsApi`.

## Endpoints

| Method | HTTP | Path |
|---|---|---|
| `get()` | GET | `/settings` |
| `update($options)` | PUT | `/settings` |

There is no `list`/`create`/`delete`; settings are a singleton per account.

## Methods

### `get(): Settings`

```php
$settings = $billomat->settings->get();

echo $settings->currencyCode;           // e.g. "EUR"
echo $settings->invoiceNumberPre;       // e.g. "RE-"
echo $settings->dueDays;                // e.g. 14
```

Throws `UnexpectedResponseException` if the response doesn't contain a `settings` object (shouldn't happen in practice).

### `update(SettingsUpdateOptions $options): Settings`

Partial update. Only fields that are not `null` in `SettingsUpdateOptions` are sent. Returns the updated settings; throws `UnexpectedResponseException` like `get()`.

```php
use Justpilot\Billomat\Api\SettingsUpdateOptions;
use Justpilot\Billomat\Model\Enum\NetGross;

$opts = new SettingsUpdateOptions(
    dueDays: 21,
    netGross: NetGross::NET,
    defaultEmailSender: 'invoices@mycompany.com',
);

$updated = $billomat->settings->update($opts);
```

## Write model: `SettingsUpdateOptions`

Only the writable fields are included; read-only fields such as `*_number_next`, `created` and `updated` are deliberately missing. Like all write models the class is `final readonly`: pass fields as named constructor arguments, and use the `with*()` methods (e.g. `$opts->withDueDays(30)`) to get a modified copy.

`null` means "don't send", the value stays unchanged. Every scalar field also accepts `Clear::Field` (`Justpilot\Billomat\Api\Clear`): the field is then sent as JSON `null` and Billomat clears it, e.g. `new SettingsUpdateOptions(defaultEmailSender: Clear::Field)`. The types below omit `|Clear` for readability.

### Client portal and branding

| Property | Billomat field | Type |
|---|---|---|
| `bgcolor` | `bgcolor` | `?string` (hex) |
| `color1` | `color1` | `?string` (hex) |
| `color2` | `color2` | `?string` (hex) |
| `color3` | `color3` | `?string` (hex) |

### Language, currency, price basis

| Property | Billomat field | Type |
|---|---|---|
| `currencyCode` | `currency_code` | `?string` |
| `locale` | `locale` | `?string` (e.g. `de_DE`) |
| `netGross` | `net_gross` | `?NetGross` |

### SEPA and number ranges

| Property | Billomat field | Type |
|---|---|---|
| `sepaCreditorId` | `sepa_creditor_id` | `?string` |
| `numberRangeMode` | `number_range_mode` | `?NumberRangeMode` |

### Article numbers and price groups

| Property | Billomat field | Type |
|---|---|---|
| `articleNumberPre` | `article_number_pre` | `?string` |
| `articleNumberLength` | `article_number_length` | `?int` |
| `priceGroups` | `price_group2`, `price_group3`, … | `array<int,string>` (index → name; empty array = don't send) |

### Client numbers

| Property | Billomat field | Type |
|---|---|---|
| `clientNumberPre` | `client_number_pre` | `?string` |
| `clientNumberLength` | `client_number_length` | `?int` |

### Invoice defaults

| Property | Billomat field | Type |
|---|---|---|
| `invoiceNumberPre` | `invoice_number_pre` | `?string` |
| `invoiceNumberLength` | `invoice_number_length` | `?int` |
| `invoiceLabel` | `invoice_label` | `?string` |
| `invoiceIntro` | `invoice_intro` | `?string` |
| `invoiceNote` | `invoice_note` | `?string` |
| `invoiceFilename` | `invoice_filename` | `?string` |
| `dueDays` | `due_days` | `?int` |
| `discountRate` | `discount_rate` | `?float` (percent) |
| `discountDays` | `discount_days` | `?int` |

### Offer defaults

| Property | Billomat field | Type |
|---|---|---|
| `offerNumberPre` | `offer_number_pre` | `?string` |
| `offerNumberLength` | `offer_number_length` | `?int` |
| `offerLabel`, `offerIntro`, `offerNote`, `offerFilename` | `offer_label`, `offer_intro`, `offer_note`, `offer_filename` | `?string` |
| `offerValidityDays` | `offer_validity_days` | `?int` |

### Confirmations, credit notes, delivery notes

Each of the three document types has the same block (`x` = `confirmation`, `creditNote`, `deliveryNote`; Billomat fields `confirmation_*`, `credit_note_*`, `delivery_note_*`):

| Property | Billomat field | Type |
|---|---|---|
| `xNumberPre` | `x_number_pre` | `?string` |
| `xNumberLength` | `x_number_length` | `?int` |
| `xLabel`, `xIntro`, `xNote`, `xFilename` | `x_label`, `x_intro`, `x_note`, `x_filename` | `?string` |

### Reminders and letters

| Property | Billomat field | Type |
|---|---|---|
| `reminderFilename` | `reminder_filename` | `?string` |
| `reminderDueDays` | `reminder_due_days` | `?int` |
| `letterLabel`, `letterIntro`, `letterFilename` | `letter_label`, `letter_intro`, `letter_filename` | `?string` |

### Print, e-mail, taxation

| Property | Billomat field | Type |
|---|---|---|
| `printVersion` | `print_version` | `?bool` (serialized as `1`/`0`) |
| `defaultEmailSender` | `default_email_sender` | `?string` |
| `bccAddresses` | `bcc_addresses` | `list<string>` (sent as a comma-separated string; empty list = don't send) |
| `taxation` | `taxation` | `?string` |

## Read model: `Settings`

`final readonly class Settings`. About 60 properties; the main groups:

### Metadata

| Property | Type |
|---|---|
| `created`, `updated` | `?\DateTimeImmutable` |

### Account basics

| Property | Type |
|---|---|
| `bgcolor`, `color1`, `color2`, `color3` | `?string` |
| `currencyCode`, `locale` | `?string` |
| `netGross` | `?NetGross` |
| `sepaCreditorId` | `?string` |
| `numberRangeMode` | `?NumberRangeMode` |

### Number ranges (prefix, length, read-only "next")

Each document type has a block `xNumberPre` (`?string`), `xNumberLength` (`?int`) and `xNumberNext` (`?int`, read-only):

- `articleNumberPre/Length/Next`
- `clientNumberPre/Length/Next`
- `invoiceNumberPre/Length/Next`
- `offerNumberPre/Length/Next`
- `confirmationNumberPre/Length/Next`
- `creditNoteNumberPre/Length/Next`
- `deliveryNoteNumberPre/Length/Next`

### Document texts

Each document type has `xLabel`, `xIntro`, `xNote`, `xFilename`:

- `invoiceLabel`, `invoiceIntro`, `invoiceNote`, `invoiceFilename`
- the same for `offer`, `confirmation`, `creditNote`, `deliveryNote`
- `reminderFilename`, `reminderDueDays`
- `letterLabel`, `letterIntro`, `letterFilename`

### Terms

| Property | Type |
|---|---|
| `dueDays` | `?int` |
| `discountRate` | `?float` |
| `discountDays` | `?int` |
| `offerValidityDays` | `?int` |

### Other

| Property | Type | Notes |
|---|---|---|
| `priceGroups` | `array<int,string>` | Keyed by price group number (e.g. `2 => "Preisgruppe 2"`) |
| `templateEngine` | `?TemplateEngine` | |
| `printVersion` | `?bool` | |
| `defaultEmailSender` | `?string` | |
| `bccAddresses` | `list<string>` | Normalized from the CSV/list format Billomat returns |
| `taxation` | `?string` | |

`Settings::fromArray()` normalizes the types: Billomat sends many values as strings (`"14"`, `"1"`, …), and the model casts them to `int`/`float`/`bool`/`DateTimeImmutable`.

## Enums

- [`NetGross`](../../src/Model/Enum/NetGross.php): `NET`, `GROSS`, `SETTINGS`.
- [`NumberRangeMode`](../../src/Model/Enum/NumberRangeMode.php): `IGNORE_PREFIX`, `CONSIDER_PREFIX`. Controls whether number prefixes get their own number range.
- [`TemplateEngine`](../../src/Model/Enum/TemplateEngine.php): `DEFAULT`.
- [`ValueType`](../../src/Model/Enum/ValueType.php): `SETTINGS`, `ABSOLUTE`, `RELATIVE` — not used in settings directly, but a related value type for clients/invoices.

<!-- json-examples:start (generated by tools/generate-json-examples.php, run `composer docs:json`) -->
## JSON on the wire

Billomat's API documentation only shows XML. These are the JSON bodies the SDK sends and receives: requests as the SDK serializes them, responses recorded in the Billomat sandbox and anonymized ([contract fixtures](../../tests/Fixtures/Contract)). Billomat sends every value as a string; the read models cast them (see [Read models](../concepts/models.md)).

::: code-group
<<< @/snippets/json/settings.update.json [PUT /settings]
<<< @/../tests/Fixtures/Contract/settings/get.json [GET /settings (response)]
:::
<!-- json-examples:end -->

## Pitfalls

- **Read-only fields can't be set.** Fields like `*NumberNext` or `created`/`updated` can't be changed via PUT, so `SettingsUpdateOptions` doesn't offer them at all.
- **`printVersion` is a `?bool`.** Billomat expects `1`/`0`; the SDK converts automatically.
- **`priceGroups` is assembled on read.** Billomat sends separate fields `price_group2`, `price_group3`, … `Settings::fromArray()` collects them into an array keyed by the price group number; `SettingsUpdateOptions` splits them up again.
- **`bccAddresses` as CSV.** Billomat returns the list either as a comma-separated string or as nested `bcc_address` nodes. The model normalizes both to `list<string>`; on write the SDK sends a comma-separated string.
- **`locale` doesn't affect the SDK.** Changing `locale` doesn't change the SDK's `Accept-Language` header; that comes from `BillomatConfig::$language` (default `de-de`).

## End-to-end example

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

// Read the current settings
$current = $billomat->settings->get();
printf("Current default due days: %d\n", $current->dueDays ?? 0);
printf("Number range mode: %s\n", $current->numberRangeMode?->value ?? 'unknown');

// Change them
$opts = new SettingsUpdateOptions(
    dueDays: 21,
    netGross: NetGross::NET,
    numberRangeMode: NumberRangeMode::CONSIDER_PREFIX,
    defaultEmailSender: 'invoices@mycompany.com',
);

$updated = $billomat->settings->update($opts);
printf("New due days saved: %d\n", $updated->dueDays ?? 0);
```
