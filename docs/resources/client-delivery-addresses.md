<!-- Source: https://www.billomat.com/api/lieferadressen/ -->

# Client delivery addresses (Lieferadressen)

API wrapper for client delivery addresses under `/client-delivery-addresses`. A delivery address belongs to exactly one client (`client_id`), so the API is a sub-resource of `clients`, like contacts. Invoices and recurring invoices reference one through `clientDeliveryAddressId`.

::: warning Experimental
Billomat is still finishing this resource: its documentation says the endpoints only become binding on 2027-01-01. Until then `ClientDeliveryAddressesApi`, `ClientDeliveryAddress` and the two options classes are marked `@experimental` and may change in a minor release, see [Backward compatibility](../concepts/backward-compatibility.md#experimental-api). The endpoints already work in the sandbox (checked on 2026-11-22).
:::

## Access

```php
$billomat->clients->deliveryAddresses
```

`Justpilot\Billomat\Api\ClientDeliveryAddressesApi`.

## Endpoint overview

| Method | HTTP | Path |
|---|---|---|
| `list($parentId, $filters?)` | GET | `/client-delivery-addresses?client_id={id}` |
| `listPage($parentId, $filters?)` | GET | `/client-delivery-addresses?client_id={id}` (page with metadata) |
| `iterateAll($parentId, $filters?, $pageSize?)` | GET | `/client-delivery-addresses?client_id={id}` (lazy over all pages) |
| `get($id)` / `getOrFail($id)` | GET | `/client-delivery-addresses/{id}` |
| `create($parentId, $options)` | POST | `/client-delivery-addresses` |
| `update($id, $options)` | PUT | `/client-delivery-addresses/{id}` |
| `delete($id)` | DELETE | `/client-delivery-addresses/{id}` |

## Methods

### `list(int $parentId, SubResourceFilter|array $filters = []): list<ClientDeliveryAddress>`

Lists the delivery addresses of a client.

```php
foreach ($billomat->clients->deliveryAddresses->list(98765) as $address) {
    printf("#%d %s: %s, %s %s%s\n",
        $address->id,
        $address->label ?? '—',
        $address->street ?? '',
        $address->zip ?? '',
        $address->city ?? '',
        $address->isDefault ? ' (default)' : '',
    );
}
```

`$filters` takes a `SubResourceFilter` (sorting and pagination) or an array of query parameters; `client_id` always comes from `$parentId`. `listPage()` and `iterateAll()` work as on every list API, see [Pagination](../advanced/pagination.md).

### `get(int $id): ?ClientDeliveryAddress`

Returns `null` on 404. `getOrFail(int $id): ClientDeliveryAddress` throws a `NotFoundException` instead.

### `create(int $parentId, ClientDeliveryAddressCreateOptions $options): ClientDeliveryAddress`

```php
use Justpilot\Billomat\Api\ClientDeliveryAddressCreateOptions;

$address = $billomat->clients->deliveryAddresses->create(98765, new ClientDeliveryAddressCreateOptions(
    label: 'Warehouse Berlin',
    name: 'Musterfirma GmbH',
    street: 'Lagerweg 5',
    addressLine2: 'Gate 3',
    zip: '10115',
    city: 'Berlin',
    countryCode: 'DE',
    isDefault: true,
));
```

The client ID is the first argument of `create()`; the SDK adds it to the payload as `client_id`. All fields of the options are optional.

### `update(int $id, ClientDeliveryAddressUpdateOptions $options): ClientDeliveryAddress`

```php
use Justpilot\Billomat\Api\ClientDeliveryAddressUpdateOptions;

$billomat->clients->deliveryAddresses->update($address->id, new ClientDeliveryAddressUpdateOptions(city: 'Bremen'));
```

A partial update: only non-`null` fields are sent, `Clear::Field` empties a field. The client can't be changed.

### `delete(int $id): void`

Removes the delivery address.

## Using a delivery address on an invoice

```php
use Justpilot\Billomat\Api\InvoiceCreateOptions;

$invoice = $billomat->invoices->create(new InvoiceCreateOptions(
    clientId: 98765,
    clientDeliveryAddressId: $address->id,
));

$invoice->clientDeliveryAddressId; // same ID, also on get()
```

`RecurringCreateOptions`/`RecurringUpdateOptions`, `InvoiceUpdateOptions` and the read models `Invoice` and `Recurring` have the same field. Other document types (offers, confirmations, credit notes, delivery notes) don't have it, neither in Billomat's docs nor in the sandbox responses.

## Write model: `ClientDeliveryAddressCreateOptions`

All fields are optional, nullable constructor parameters passed as named arguments. The object is immutable; each field has a `with*()` method.

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `label` | `label` | `?string` | Name of the address (e.g. `Warehouse Berlin`) |
| `name` | `name` | `?string` | Company name |
| `salutation` | `salutation` | `?string` | |
| `firstName` | `first_name` | `?string` | |
| `lastName` | `last_name` | `?string` | |
| `street` | `street` | `?string` | |
| `addressLine2` | `address_line_2` | `?string` | Additional address line |
| `addressLine3` | `address_line_3` | `?string` | Additional address line |
| `zip` | `zip` | `?string` | |
| `city` | `city` | `?string` | |
| `state` | `state` | `?string` | State/region |
| `countryCode` | `country_code` | `?string` | ISO 3166-1 alpha-2; Billomat rejects unknown codes with a `ValidationException` |
| `isDefault` | `is_default` | `?bool` | Default delivery address of the client; sent as `1`/`0` |

Billomat's field table only lists part of these; `label`, `address_line_2`, `address_line_3` and `is_default` appear in its example responses. The sandbox accepts and returns all of them.

## Write model: `ClientDeliveryAddressUpdateOptions`

Same fields as the create options, typed `string|Clear|null` (`isDefault`: `bool|Clear|null`). `null` means "leave unchanged", `Clear::Field` sends JSON `null` to empty the field.

## Read model: `ClientDeliveryAddress`

| Property | Type |
|---|---|
| `id` | `int` |
| `clientId` | `int` |
| `created` | `?\DateTimeImmutable` |
| `updated` | `?\DateTimeImmutable` |
| `label`, `name`, `salutation`, `firstName`, `lastName` | `?string` |
| `street`, `addressLine2`, `addressLine3`, `zip`, `city`, `state`, `countryCode` | `?string` |
| `isDefault` | `?bool` |
| `extra` | `array<string, mixed>` (unmapped fields, see [Models](../concepts/models.md)) |

<!-- json-examples:start (generated by tools/generate-json-examples.php, run `composer docs:json`) -->
## JSON on the wire

Billomat's API documentation only shows XML. These are the JSON bodies the SDK sends and receives: requests as the SDK serializes them, responses recorded in the Billomat sandbox and anonymized ([contract fixtures](../../tests/Fixtures/Contract)). Billomat sends every value as a string; the read models cast them (see [Read models](../concepts/models.md)).

::: code-group
<<< @/snippets/json/client-delivery-addresses.create.json [POST /client-delivery-addresses]
<<< @/../tests/Fixtures/Contract/client-delivery-addresses/get.json [GET /client-delivery-addresses/{id} (response)]
<<< @/../tests/Fixtures/Contract/client-delivery-addresses/list.json [GET /client-delivery-addresses (response)]
:::
<!-- json-examples:end -->

## Pitfalls

- **No `customfield`.** Unlike contacts, Billomat answers `GET /client-delivery-addresses/{id}/customfield` with 405, so there are no `getCustomField()`/`setCustomField()` methods.
- **Single-item list quirk.** With exactly one address Billomat returns an object instead of a list; `list()` normalizes it.
- **One default per client.** Creating or updating an address with `isDefault: true` resets the flag on the client's previous default address (checked in the sandbox on 2026-11-22). Reload other addresses if you cache them.
- **No duplicates.** Billomat rejects an address whose fields match an existing address of the same client with a `ValidationException` ("an equal delivery address already exists for this client"). `label` doesn't count: two addresses that only differ in the label are duplicates.
