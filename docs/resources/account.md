<!-- Source: https://www.billomat.com/api/account/ -->

# Account

API wrapper for your own account data at `/clients/myself`.

## Access

```php
$billomat->account
```

`Justpilot\Billomat\Api\AccountApi`.

## Overview

Billomat stores your own account as a special client. There is no dedicated `/account` endpoint; instead the clients endpoint is called with the reserved ID `myself`. Unlike a regular `GET /clients/{id}`, the response also contains the current **plan** (`plan`) and the **quotas** (`quotas`).

## Endpoints

| Method | HTTP | Path |
|---|---|---|
| `get(): Account` | GET | `/clients/myself` |

There is no write path; the account is read-only.

## Method

### `get(): Account`

Returns your account information as an `Account` model.

```php
$account = $billomat->account->get();

echo $account->plan;           // e.g. "XL"
echo $account->client->name;   // company name
echo $account->client->email;  // billing e-mail

foreach ($account->quotas as $quota) {
    printf(
        "%s: %d of %s used%s\n",
        $quota->entity,
        $quota->used,
        $quota->isUnlimited() ? '∞' : (string) $quota->available,
        $quota->isUnlimited() ? ' (unlimited)' : '',
    );
}
```

You can also look up a quota by entity:

```php
$storage = $account->quota('storage');

if ($storage !== null && !$storage->isUnlimited()) {
    $percent = $storage->used / $storage->available * 100;
    // ...
}
```

## Model `Account`

`Justpilot\Billomat\Model\Account` — wraps the existing `Client` read model and adds the account-specific fields.

| Field | Type | Description |
|---|---|---|
| `client` | `Client` | Full client record (master data, address, bank details, etc.) |
| `plan` | `?string` | Plan name, e.g. `"XL"`, `"S"`, `"Free"` |
| `quotas` | `list<AccountQuota>` | Quotas, one entry per entity |
| `taxAuthorityNumber` | `?string` | E-invoicing, read-only; see Pitfalls |
| `facturxFieldsEnabled` | `?bool` | E-invoicing, read-only; see Pitfalls |

## Model `AccountQuota`

`Justpilot\Billomat\Model\AccountQuota` — one quota entry.

| Field | Type | Description |
|---|---|---|
| `entity` | `string` | Known values: `documents`, `clients`, `articles`, `storage` |
| `available` | `int` | Available volume; `-1` means "unlimited" |
| `used` | `int` | Used volume (bytes for `storage`) |

Helper `isUnlimited(): bool` checks the `available === -1` convention.

## Pitfalls

- **E-invoicing (undocumented).** Billomat returns `tax_authority_number` (`$taxAuthorityNumber`), `facturx_fields_enabled` (`$facturxFieldsEnabled`) and `seller_identifier` (via `$account->client->sellerIdentifier`), but the API docs don't describe them. The SDK only reads them (types derived from observed values); writing is not supported because there is no specification.
- **No `/account` endpoint.** The path is `/clients/myself`.
- **The quota list can be missing.** For some plans Billomat doesn't send `quotas` at all. The list is then empty and `quota($entity)` returns `null`.
- **`storage` is in bytes, everything else is a count.** `available`/`used` carry no unit in the response: for `storage` the values are bytes (example from the docs: `8_740_060` bytes), for `documents`/`clients`/`articles` they are counts.
- **`Account` is not a `Client`.** There is deliberately no inheritance. If you need the full client model for your account, use `$account->client`. Alternatively, `$billomat->clients->getMyself()` returns a `Client` directly (without plan/quotas).

## End-to-end

```php
use Justpilot\Billomat\BillomatClient;

$billomat = BillomatClient::create(billomatId: 'mycompany', apiKey: 'secret');

$account = $billomat->account->get();

if ($account->plan === 'Free') {
    $documents = $account->quota('documents');
    if ($documents !== null && !$documents->isUnlimited() && $documents->used / $documents->available > 0.9) {
        // suggest an upgrade
    }
}
```
