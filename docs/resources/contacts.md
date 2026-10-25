<!-- Source: https://www.billomat.com/api/kunden/kontakte/ -->

# Contacts (Ansprechpartner)

API wrapper for client contacts under `/contacts`. A contact always belongs to exactly one client (`client_id` as foreign key). Typical use: store several contact persons per company (accounting, purchasing, management).

## Access

```php
$billomat->contacts
```

`Justpilot\Billomat\Api\ContactsApi`.

## Endpoint overview

| Method | HTTP | Path |
|---|---|---|
| `list($clientId, $query?)` | GET | `/contacts?client_id={id}` |
| `get($id)` / `getOrFail($id)` | GET | `/contacts/{id}` |
| `create($options)` | POST | `/contacts` |
| `update($id, $options)` | PUT | `/contacts/{id}` |
| `delete($id)` | DELETE | `/contacts/{id}` |

There is **no** unfiltered `list()`: Billomat's `GET /contacts` requires a `client_id` filter. The SDK reflects this in the method signature.

## Methods

### `list(int $clientId, array $query = []): list<Contact>`

Lists all contacts of a client.

```php
$contacts = $billomat->contacts->list(98765);

foreach ($contacts as $contact) {
    printf("#%d %s %s <%s>\n",
        $contact->id,
        $contact->firstName ?? '',
        $contact->lastName ?? '',
        $contact->email ?? '—',
    );
}
```

`$query` takes additional parameters such as `page` and `per_page`.

### `get(int $id): ?Contact`

Returns `null` on 404. `getOrFail(int $id): Contact` throws a `NotFoundException` instead.

### `create(ContactCreateOptions $options): Contact`

```php
use Justpilot\Billomat\Api\ContactCreateOptions;

$opts = new ContactCreateOptions(
    clientId: 98765,
    firstName: 'Erika',
    lastName: 'Mustermann',
    email: 'erika@example.com',
);

$created = $billomat->contacts->create($opts);
```

`clientId` is required and the first constructor argument; all other fields are optional and passed as named arguments.

### `update(int $id, ContactUpdateOptions $options): Contact`

```php
use Justpilot\Billomat\Api\ContactUpdateOptions;

$patch = new ContactUpdateOptions(email: 'new.address@example.com');

$billomat->contacts->update($contactId, $patch);
```

`ContactUpdateOptions` is a true partial update: only non-`null` fields are sent, and `Clear::Field` empties a field. The `client_id` deliberately can't be changed on update.

### `delete(int $id): void`

Removes the contact. Errors are thrown as exceptions.

## Write model: `ContactCreateOptions`

Constructor: `new ContactCreateOptions(int $clientId, …)`. All other fields are optional, nullable constructor parameters passed as named arguments. The object is immutable (`final readonly`); each field has a `with*()` method that returns a modified copy (e.g. `$opts->withEmail('new@example.com')`).

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `clientId` | `client_id` | `int` | Required, constructor argument |
| `label` | `label` | `?string` | Free-form label (e.g. `Accounting`) |
| `name` | `name` | `?string` | Company name |
| `salutation` | `salutation` | `?string` | Salutation (`Herr`, `Frau`, …) |
| `firstName` | `first_name` | `?string` | |
| `lastName` | `last_name` | `?string` | |
| `street` | `street` | `?string` | |
| `zip` | `zip` | `?string` | |
| `city` | `city` | `?string` | |
| `state` | `state` | `?string` | State/region |
| `countryCode` | `country_code` | `?string` | ISO 3166-1 alpha-2 (e.g. `DE`) |
| `email` | `email` | `?string` | |
| `phone` | `phone` | `?string` | |
| `fax` | `fax` | `?string` | |
| `mobile` | `mobile` | `?string` | |
| `www` | `www` | `?string` | Website |
| `note` | `note` | `?string` | Free-text note |

`toArray()` strips `null` values with `array_filter`, so fields you don't set don't appear in the payload.

## Write model: `ContactUpdateOptions`

Same fields as `ContactCreateOptions`, **without** `clientId`: Billomat doesn't allow changing the client on update. All fields are optional constructor parameters, and `toArray()` strips `null` values as well. `null` means "don't send", the value stays unchanged. `Clear::Field` (`Justpilot\Billomat\Api\Clear`) sends the field as JSON `null` so Billomat empties it, e.g. `new ContactUpdateOptions(note: Clear::Field)`.

| Property | Billomat field | Type |
|---|---|---|
| `label`, `name`, `salutation`, `firstName`, `lastName` | `label`, `name`, `salutation`, `first_name`, `last_name` | `string\|Clear\|null` |
| `street`, `zip`, `city`, `state`, `countryCode` | `street`, `zip`, `city`, `state`, `country_code` | `string\|Clear\|null` |
| `email`, `phone`, `fax`, `mobile`, `www` | same | `string\|Clear\|null` |
| `note` | `note` | `string\|Clear\|null` |

## Read model: `Contact`

`final readonly class Contact`.

| Property | Type |
|---|---|
| `id` | `int` |
| `clientId` | `int` |
| `created` | `?\DateTimeImmutable` |
| `label` | `?string` |
| `salutation` | `?string` |
| `firstName` | `?string` |
| `lastName` | `?string` |
| `street` | `?string` |
| `zip` | `?string` |
| `city` | `?string` |
| `state` | `?string` |
| `countryCode` | `?string` |
| `email` | `?string` |
| `phone` | `?string` |
| `fax` | `?string` |
| `mobile` | `?string` |
| `updated` | `?\DateTimeImmutable` |
| `name` | `?string` |
| `address` | `?string` |
| `www` | `?string` |
| `locale` | `?string` |
| `customfield` | `?string` (free-form extra field, see [Custom meta attributes](../concepts/custom-meta-attributes.md)) |
| `extra` | `array<string, mixed>` (unmapped fields, see [Models](../concepts/models.md)) |

`created` and `updated` are parsed from Billomat's date string; an invalid value leaves the field `null`.

## Enums used

None. All fields are strings, including `salutation` and `countryCode`. Billomat expects the latter as an ISO 3166-1 alpha-2 code (validated server-side).

## Pitfalls

- **`list()` requires a filter.** Billomat's `GET /contacts` without `client_id` returns an error, not a global list. The SDK makes the client ID a required argument.
- **Single-item list quirk.** If a client has exactly one contact, Billomat returns a single object under `contacts.contact` instead of a list. `list()` normalizes this to a `list<Contact>` via an `array_is_list()` check.
- **`client_id` can't be changed on update.** `ContactUpdateOptions` deliberately doesn't have the field. To "move" a contact to another client, delete it and create it again.
- **`null` doesn't empty a field.** In `ContactUpdateOptions`, `null` means "leave unchanged". To empty a field, pass `Clear::Field`.

## End-to-end example

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\ContactCreateOptions;
use Justpilot\Billomat\Api\ContactUpdateOptions;
use Justpilot\Billomat\BillomatClient;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

$clientId = 98765;

// 1) List existing contacts
foreach ($billomat->contacts->list($clientId) as $contact) {
    printf("[#%d] %s %s — %s\n",
        $contact->id,
        $contact->firstName ?? '',
        $contact->lastName ?? '',
        $contact->email ?? '—',
    );
}

// 2) Create a new contact
$opts = new ContactCreateOptions(
    clientId: $clientId,
    label: 'Accounting',
    salutation: 'Frau',
    firstName: 'Erika',
    lastName: 'Mustermann',
    email: 'accounting@example.com',
    countryCode: 'DE',
);

$created = $billomat->contacts->create($opts);
printf("Created: #%d %s %s\n", $created->id, $created->firstName, $created->lastName);

// 3) Update the email address
$patch = new ContactUpdateOptions(email: 'finance@example.com');

$updated = $billomat->contacts->update($created->id, $patch);
var_dump($updated->email); // 'finance@example.com'

// 4) Delete it again
$billomat->contacts->delete($created->id);
```
