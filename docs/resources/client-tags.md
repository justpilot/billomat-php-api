<!-- Source: https://www.billomat.com/api/kunden/schlagworte/ -->

# Client Tags (Kunden-Schlagworte)

API wrapper for tags on clients under `/client-tags`. Conceptually identical to [Invoice Tags](invoice-tags.md); if you know the model there, read this page as a cheat sheet.

## Access

```php
$billomat->clients->tags
```

`Justpilot\Billomat\Api\TagsApi`, the generic tags class shared by all tag resources.

## Overview

As with invoice tags, the same resource offers two views:

- **Tags of one client**, via `?client_id={id}`. Response element: `client-tag`.
- **Tag cloud**: all tags of the account with their frequency, without a filter. Response element: `tag` (a different key).

The two views map to two methods with two separate read models.

## Endpoint overview

| Method | HTTP | Path |
|---|---|---|
| `list($clientId, $filters?)` | GET | `/client-tags?client_id={id}` |
| `listPage($clientId, $filters?)` / `iterateAll($clientId, $filters?, $pageSize?)` | GET | `/client-tags?client_id={id}` (paginated) |
| `cloud()` | GET | `/client-tags` |
| `get($id)` / `getOrFail($id)` | GET | `/client-tags/{id}` |
| `create($clientId, $options)` | POST | `/client-tags` |
| `delete($id)` | DELETE | `/client-tags/{id}` |

There is no `update()` method: delete the tag and create a new one.

## Methods

### `list(int $clientId, SubResourceFilter|array $filters = []): list<ClientTag>`

Lists the tags of a single client.

```php
$tags = $billomat->clients->tags->list(98765);

foreach ($tags as $tag) {
    echo $tag->name . "\n";
}
```

`SubResourceFilter` sorts and paginates (`orderBy`, `page`, `perPage`, `extra`); `listPage()` and `iterateAll()` work the same way. See [Pagination](../advanced/pagination.md).

```php
use Justpilot\Billomat\Api\OrderBy;
use Justpilot\Billomat\Api\SubResourceFilter;

$tags = $billomat->clients->tags->list(98765, new SubResourceFilter(orderBy: OrderBy::asc('name')));
```

### `cloud(): list<ClientTagCloudEntry>`

Aggregated list of all client tags in the account with their `count`. Useful for autocompletion in a UI or for a tag cloud.

```php
foreach ($billomat->clients->tags->cloud() as $entry) {
    printf("%s (%d clients)\n", $entry->name, $entry->count);
}
```

### `get(int $id): ?ClientTag`

Returns `null` on 404. `getOrFail(int $id): ClientTag` throws a `NotFoundException` instead.

### `create(int $clientId, ClientTagCreateOptions $options): ClientTag`

```php
use Justpilot\Billomat\Api\ClientTagCreateOptions;

$tag = $billomat->clients->tags->create(
    98765,
    new ClientTagCreateOptions(name: 'A-client'),
);
```

Several tags for the same client mean several `create()` calls.

### `delete(int $id): void`

Removes the link between client and tag. Once a tag is no longer used on any client, it disappears from the tag cloud automatically.

## Write model: `ClientTagCreateOptions`

Constructor: `new ClientTagCreateOptions(string $name)`. The object is `final readonly`; `withName()` returns a modified copy. The client ID is the first argument of `create()`, which adds it to the payload as `client_id`.

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `name` | `name` | `string` | Required, free text |

## Read model: `ClientTag`

`final readonly class ClientTag`: one tag link on one client.

| Property | Type |
|---|---|
| `id` | `int` |
| `clientId` | `int` |
| `name` | `string` |
| `customfield` | `?string` (free-form extra field, see [Custom meta attributes](../concepts/custom-meta-attributes.md)) |
| `extra` | `array<string, mixed>` (unmapped fields, see [Models](../concepts/models.md)) |

## Read model: `ClientTagCloudEntry`

`final readonly class ClientTagCloudEntry`: one aggregated entry from `cloud()`. It deliberately has **no** `clientId` field, because the entry aggregates several clients.

| Property | Type |
|---|---|
| `id` | `?int` |
| `name` | `string` |
| `count` | `int` |
| `extra` | `array<string, mixed>` |

## Enums used

None. `name` is free text.

## Pitfalls

- **Two views, two models.** `list()` returns `ClientTag`, `cloud()` returns `ClientTagCloudEntry`. Mixing them up leads to "why doesn't this thing have a `clientId`?" moments.
- **Inconsistent response keys.** `list()` parses `client-tags.client-tag`, `cloud()` parses `client-tags.tag`: same endpoint, two different inner keys depending on whether the `client_id` filter is present. The SDK hides this, but it's worth knowing when you debug the raw response.
- **Single-item list quirk.** If a client has only one tag (or the cloud has only one entry), Billomat returns a single object instead of a list. Both methods normalize this to a list.
- **No PUT.** Renaming isn't possible: delete the old link and create a new one.
- **`name` is case-sensitive.** `a-client` and `A-client` are two different tags for Billomat and appear separately in the tag cloud.

## End-to-end example

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\ClientTagCreateOptions;
use Justpilot\Billomat\BillomatClient;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

$clientId = 98765;

// 1) Attach tags to a client
foreach (['A-client', 'regular', '2026-Q2'] as $name) {
    $billomat->clients->tags->create(
        $clientId,
        new ClientTagCreateOptions(name: $name),
    );
}

// 2) List the client's tags
$tags = $billomat->clients->tags->list($clientId);
printf("Tags on client #%d: %s\n", $clientId, implode(', ', array_map(
    static fn ($t) => $t->name,
    $tags,
)));

// 3) Tag cloud: the 5 most used tags
$cloud = $billomat->clients->tags->cloud();
usort($cloud, static fn ($a, $b) => $b->count <=> $a->count);
foreach (array_slice($cloud, 0, 5) as $entry) {
    printf("%-20s %d\n", $entry->name, $entry->count);
}
```
