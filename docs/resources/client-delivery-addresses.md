<!-- Source: https://www.billomat.com/api/lieferadressen/ -->

# Delivery addresses (Lieferadressen)

API wrapper for client delivery addresses under `/client-delivery-addresses`. A delivery address belongs to exactly one client (`client_id`), so the API is a sub-resource of `clients`, like contacts and tags. [Invoices](invoices.md) and [recurring invoices](recurrings.md) refer to one via `clientDeliveryAddressId`.

## Access

```php
$billomat->clients->deliveryAddresses
```

`Justpilot\Billomat\Api\ClientDeliveryAddressesApi`.

## Endpoint overview

| Method | HTTP | Path |
|---|---|---|
| `list($parentId?, $filters?)` | GET | `/client-delivery-addresses[?client_id={id}]` |
| `listPage($parentId?, $filters?)` | GET | `/client-delivery-addresses[?client_id={id}]` (page with metadata) |
| `iterateAll($parentId?, $filters?, $pageSize?)` | GET | `/client-delivery-addresses[?client_id={id}]` (lazy over all pages) |
| `get($id)` / `getOrFail($id)` | GET | `/client-delivery-addresses/{id}` |
| `create($parentId, $options)` | POST | `/client-delivery-addresses` |
| `update($id, $options)` | PUT | `/client-delivery-addresses/{id}` |
| `delete($id)` | DELETE | `/client-delivery-addresses/{id}` |

## Methods

### `list(?int $parentId = null, SubResourceFilter|array $filters = []): list<ClientDeliveryAddress>`

Lists the delivery addresses of a client, or of all clients without `$parentId`.

```php
$addresses = $billomat->clients->deliveryAddresses->list(98765);

foreach ($addresses as $address) {
    printf("#%d %s, %s %s%s\n",
        $address->id,
        $address->name ?? '',
        $address->zip ?? '',
        $address->city ?? '',
        $address->isDefault ? ' (default)' : '',
    );
}
```

`$filters` takes a `SubResourceFilter` (sorting and pagination) or an array of query parameters. Billomat documents no other filters; `client_id` comes from `$parentId` and wins over a `client_id` in `$filters`.

`listPage()` returns one page with `PageInfo`, `iterateAll()` walks through all pages lazily, see [Pagination](../advanced/pagination.md).

### `get(int $id): ?ClientDeliveryAddress`

Returns `null` on 404. `getOrFail(int $id): ClientDeliveryAddress` throws a `NotFoundException` instead.

### `create(int $parentId, ClientDeliveryAddressCreateOptions $options): ClientDeliveryAddress`

```php
use Justpilot\Billomat\Api\ClientDeliveryAddressCreateOptions;
use Justpilot\Billomat\Api\InvoiceCreateOptions;

$address = $billomat->clients->deliveryAddresses->create(98765, new ClientDeliveryAddressCreateOptions(
    name: 'Musterfirma GmbH – Lager Nord',
    street: 'Hafenstraße 1',
    zip: '20457',
    city: 'Hamburg',
    countryCode: 'DE',
));

$invoice = $billomat->invoices->create(new InvoiceCreateOptions(
    clientId: 98765,
    clientDeliveryAddressId: $address->id,
));
```

The client ID is the first argument of `create()`, as on every sub-resource; the SDK adds it to the payload as `client_id`.

### `update(int $id, ClientDeliveryAddressUpdateOptions $options): ClientDeliveryAddress`

A partial update: only non-`null` fields are sent, and `Clear::Field` empties a field. The client of a delivery address can't be changed.

### `delete(int $id): void`

Removes the delivery address.

## Write model: `ClientDeliveryAddressCreateOptions`

All fields are optional, nullable constructor parameters passed as named arguments. The object is immutable (`final readonly`); each field has a `with*()` method that returns a modified copy.

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `label` | `label` | `?string` | Undocumented |
| `name` | `name` | `?string` | Company name |
| `salutation` | `salutation` | `?string` | |
| `firstName` | `first_name` | `?string` | |
| `lastName` | `last_name` | `?string` | |
| `street` | `street` | `?string` | |
| `addressLine2`, `addressLine3` | `address_line_2`, `address_line_3` | `?string` | Undocumented |
| `zip` | `zip` | `?string` | |
| `city` | `city` | `?string` | |
| `state` | `state` | `?string` | State/region |
| `countryCode` | `country_code` | `?string` | ISO 3166-1 alpha-2 (e.g. `DE`) |
| `isDefault` | `is_default` | `?bool` | Undocumented: the client's default delivery address. There is at most one per client; setting it on one address clears it on the previous one |

Billomat returns and accepts the fields marked *undocumented* without listing them in its documentation.

## Write model: `ClientDeliveryAddressUpdateOptions`

Same fields as the create options, each `…|Clear|null`. `null` means "don't send"; `Clear::Field` (`Justpilot\Billomat\Api\Clear`) sends the field as JSON `null` so Billomat empties it.

## Read model: `ClientDeliveryAddress`

`final readonly class ClientDeliveryAddress`.

| Property | Type |
|---|---|
| `id` | `int` |
| `clientId` | `int` |
| `created`, `updated` | `?\DateTimeImmutable` |
| `label`, `name`, `salutation`, `firstName`, `lastName` | `?string` |
| `street`, `addressLine2`, `addressLine3`, `zip`, `city`, `state`, `countryCode` | `?string` |
| `isDefault` | `?bool` |
| `extra` | `array<string, mixed>` (unmapped fields, see [Models](../concepts/models.md)) |

## Enums used

None.

<!-- json-examples:start (generated by tools/generate-json-examples.php, run `composer docs:json`) -->
## JSON on the wire

Billomat's API documentation only shows XML. These are the JSON bodies the SDK sends and receives: requests as the SDK serializes them, responses recorded in the Billomat sandbox and anonymized ([contract fixtures](../../tests/Fixtures/Contract)). Billomat sends every value as a string; the read models cast them (see [Read models](../concepts/models.md)).

::: code-group
<<< @/snippets/json/client-delivery-addresses.create.json [POST /client-delivery-addresses]
<<< @/../tests/Fixtures/Contract/client-delivery-addresses/get.json [GET /client-delivery-addresses/{id} (response)]
<<< @/../tests/Fixtures/Contract/client-delivery-addresses/list.json [GET /client-delivery-addresses (response)]
:::
<!-- json-examples:end -->
