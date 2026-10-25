# Upgrading from 3.x to 4.0

This guide lists every backward-incompatible change in 4.0 with a before/after example.
It grows with each 4.0 pre-release; the [CHANGELOG](CHANGELOG.md) has the full list of changes.

Recommended order:

1. Upgrade to the latest 3.x release and fix all deprecation notices first.
   Everything that was deprecated in 3.x is removed in 4.0.
2. Switch to PHP 8.5.
3. Work through the sections below.

## PHP 8.5 is required

`composer.json` now requires `"php": "^8.5"`. Composer will not install 4.0 on older PHP versions.

## Removed deprecated API

### `DunningLevelsApi`

Billomat removed `/dunning-levels` (it answers with 404). Reminder levels are reminder texts:

```php
// 3.x
$levels = $billomat->dunningLevels->list();

// 4.0
$levels = $billomat->reminderTexts->list();
```

The `DunningLevel` model is gone as well; use `ReminderText`.

### `AbstractApi::putEmptyResponse()`

Only relevant if you extend `AbstractApi`. Use `putVoid()`:

```php
// 3.x
$this->putEmptyResponse('/invoices/1/complete', $body);

// 4.0
$this->putVoid('/invoices/1/complete', $body);
```

### Read-model properties Billomat no longer returns

These properties were always `null` with the current Billomat API and are removed from the models,
their constructors and `toArray()`. Delete every read of them:

| Model | Removed properties |
|---|---|
| `Article` | `$unit`, `$purchasePriceCurrencyCode`, `$categoryId` |
| `Client` | `$customerportalUrl` |
| `Contact` | `$note` |
| `*Property` | `$position` |
| `TaxRate` | `$accountId` |
| `ReminderText` | `$dueDays` |
| `Country` | `$nameDe`, `$eu` |
| `User` | `$phone`, `$mobile`, `$fax` |
| `Invoice`, `CreditNote` | `$customerportalUrl` |
| `Offer`, `Confirmation` | `$discountRate`, `$discountDate`, `$discountDays`, `$discountAmount`, `$customerportalUrl` |
| `Letter` | `$letterNumber`, `$number`, `$numberPre`, `$numberLength`, `$note`, `$customerportalUrl` |
| `Reminder` | `$reminderNumber`, `$number`, `$numberPre`, `$numberLength`, `$totalNet`, `$quote` |
| `Recurring` | `$numberPre` |
| `Incoming` | `$supplyDate`, `$dueDays`, `$paidAt`, `$intro` |
| `DeliveryNote` | `$totalGross`, `$totalNet`, `$netGross`, `$reduction`, `$totalGrossUnreduced`, `$totalNetUnreduced`, `$currencyCode`, `$quote`, `$customerportalUrl` |
| `InvoiceItem`, `OfferItem`, `CreditNoteItem`, `ConfirmationItem` | `$created` |
| `ReminderItem` | `$totalGross`, `$totalNet`, `$created` |
| `DeliveryNoteItem` | `$unitPrice`, `$taxName`, `$taxRate`, `$reduction`, `$totalGross`, `$totalNet`, `$totalGrossUnreduced`, `$totalNetUnreduced`, `$created` |

Fields that Billomat still accepts on write (for example `Recurring::$numberPre`) stay available
in the matching `*CreateOptions`/`*UpdateOptions`.

If you construct models yourself (for example in tests), switch to named arguments;
positional constructor calls break wherever a removed parameter was in between.

## `BillomatConfig` validates its input

The constructor (and therefore `BillomatClient::create()`) throws an `InvalidArgumentException` when

- the Billomat ID is not a valid subdomain (letters, digits and inner hyphens, at most 63 characters), or
- the base URI does not result in an absolute URI with scheme and host.

```php
// 3.x: built the host mycompany.billomat.net.billomat.net and failed only on the first request
BillomatClient::create(billomatId: 'mycompany.billomat.net', apiKey: $key);

// 4.0: throws InvalidArgumentException right away – pass only the subdomain
BillomatClient::create(billomatId: 'mycompany', apiKey: $key);
```

## Read-only methods are `#[\NoDiscard]`

`get()`, `getMyself()`, `list()`, `listPage()`, `listBy*()`, `listGrouped()`, `iterateAll()`, `cloud()`,
`pdf()`, `thumb()`, `avatar()` and `SearchApi::query()` carry `#[\NoDiscard]`. PHP 8.5 emits a warning
when their result is ignored. This is not a hard break, but test suites that turn warnings into failures
will notice it. If you call such a method only for its exception (for example to check that a resource
exists), cast the result to `(void)`:

```php
(void) $billomat->clients->get($id);
```
