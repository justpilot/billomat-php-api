<!-- Source: https://www.billomat.com/api/einstellungen/rollen/ -->

# Roles (Mitarbeiter-Rollen / user roles)

API wrapper for user roles at `/roles`. A role bundles access rights per resource (articles, clients, invoices, …) and is assigned to users; see [users.md](users.md) for `roleId`.

## Access

```php
$billomat->roles
```

`Justpilot\Billomat\Api\RolesApi`.

## Endpoints

| Method | HTTP | Path |
|---|---|---|
| `list($filters?)` | GET | `/roles` |
| `listPage($filters?)` | GET | `/roles` |
| `iterateAll($filters?, $pageSize?)` | GET | `/roles` (all pages) |
| `get($id)` | GET | `/roles/{id}` |
| `getOrFail($id)` | GET | `/roles/{id}` |
| `create($options)` | POST | `/roles` |
| `update($id, $options)` | PUT | `/roles/{id}` |
| `delete($id)` | DELETE | `/roles/{id}` |

Roles have no `customfield`: Billomat answers `/roles/{id}/customfield` with 405, so there is no `getCustomField()`/`setCustomField()` here.

## Methods

### `list(array $filters = []): list<Role>`

```php
foreach ($billomat->roles->list() as $role) {
    printf("#%d %s\n", $role->id, $role->name ?? '');
}
```

Filters:

| Parameter | Description |
|---|---|
| `name` | Substring match on the role name; case-insensitive |
| `page`, `per_page` | Pagination — see [Pagination and filtering](../concepts/pagination-and-filtering.md) |

### `get(int $id): ?Role`

Returns `null` on 404.

### `getOrFail(int $id): Role`

Like `get()`, but throws `NotFoundException` instead of returning `null`.

### `create(RoleCreateOptions $options): Role`

The permissions are a map area → `AccessLevel`, like `Role::$permissions`. Areas without an entry get no access.

```php
use Justpilot\Billomat\Api\RoleCreateOptions;
use Justpilot\Billomat\Model\Enum\AccessLevel;

$role = $billomat->roles->create(new RoleCreateOptions(
    name: 'Accounting',
    permissions: [
        'invoices' => AccessLevel::DELETE,
        'clients' => AccessLevel::READ,
        'settings_documents' => AccessLevel::UPDATE,
    ],
));

// or step by step
$options = new RoleCreateOptions(name: 'Sales')
    ->withPermission('offers', AccessLevel::UPDATE)
    ->withPermission('clients', AccessLevel::UPDATE);
```

### `update(int $id, RoleUpdateOptions $options): Role`

Only what you set is sent: a `null` name stays unchanged, and areas without an entry keep their access. An area set to `null` loses its access (sent as an empty string).

```php
use Justpilot\Billomat\Api\RoleUpdateOptions;

$billomat->roles->update($role->id, new RoleUpdateOptions(permissions: [
    'clients' => AccessLevel::UPDATE, // raise
    'invoices' => null,               // revoke
]));
```

### `delete(int $id): void`

```php
$billomat->roles->delete($role->id);
```

## Write models

| Class | Fields |
|---|---|
| `RoleCreateOptions` | `name` (`string`, required), `permissions` (`array<string, AccessLevel>`) |
| `RoleUpdateOptions` | `name` (`?string`), `permissions` (`array<string, AccessLevel\|null>`, `null` revokes) |

Both are immutable: `withName()`, `withPermissions()` (replaces the map) and `withPermission($area, $level)` (sets one area). Every way of setting permissions checks the area name and throws an `InvalidArgumentException` for an unknown one, because Billomat silently ignores unknown fields and a typo (`invoice` instead of `invoices`) would otherwise go unnoticed. `Role::isArea($key)` applies the same rule: one of `Role::AREAS` or any key starting with `settings_`.

## Read model: `Role`

`final readonly class Role`.

| Property | Billomat field | Type |
|---|---|---|
| `id` | `id` | `int` |
| `name` | `name` | `?string` |
| `permissions` | (several fields, see below) | `array<string, AccessLevel>` |
| `created`, `updated` | `created`, `updated` | `?\DateTimeImmutable` |

`toArray()` exports the permissions as top-level fields, like Billomat sends them (`'articles' => 'DELETE'`).

### Permission values

Each permission is a `Justpilot\Billomat\Model\Enum\AccessLevel`:

| Value | Meaning |
|---|---|
| `AccessLevel::READ` | Read |
| `AccessLevel::UPDATE` | Edit (implies `READ`) |
| `AccessLevel::DELETE` | Full access (implies `UPDATE` + `READ`) |
| missing | No access (Billomat sends an empty string) |

`allows()` checks the hierarchy: `AccessLevel::DELETE->allows(AccessLevel::UPDATE)` is `true`. `label()` gives the German UI label (`Lesen`, `Bearbeiten`, `Löschen`). Users carry the same map in `User::$rights` (see [users.md](users.md)).

### Known permission keys

`articles`, `clients`, `suppliers`, `offers`, `confirmations`, `invoices`, `credit_notes`, `delivery_notes`, `reminders`, `incomings`, `letters`, `banking`, `net_income_statement`, `accounting_export`, `settings_my_account`, `settings_documents`, `settings_configuration`, `settings_administration`, `settings_addons`, `settings_my_addons`.

They are in `Role::AREAS`. `fromArray()` also accepts any key starting with `settings_`, so new settings permissions end up in `permissions` automatically if Billomat adds them.

<!-- json-examples:start (generated by tools/generate-json-examples.php, run `composer docs:json`) -->
## JSON on the wire

Billomat's API documentation only shows XML. These are the JSON bodies the SDK sends and receives: requests as the SDK serializes them, responses recorded in the Billomat sandbox and anonymized ([contract fixtures](../../tests/Fixtures/Contract)). Billomat sends every value as a string; the read models cast them (see [Read models](../concepts/models.md)).

::: code-group
<<< @/snippets/json/roles.create.json [POST /roles]
<<< @/../tests/Fixtures/Contract/roles/get.json [GET /roles/{id} (response)]
<<< @/../tests/Fixtures/Contract/roles/list.json [GET /roles (response)]
:::
<!-- json-examples:end -->

## Pitfalls

- **The master role can't be deleted.** The system role `Master` (usually `id=1`) can't be removed, not even in the UI.
- **No access → no entry.** Areas without access, and values the SDK doesn't know, are not in `permissions`; check with `$role->permissions['articles'] ?? null`, or `($role->permissions['articles'] ?? null)?->allows(AccessLevel::UPDATE) ?? false` for a minimum level.
- **Allowed levels depend on the area.** The `settings_*` areas only accept `UPDATE`; `READ` or `DELETE` there makes Billomat answer HTTP 400 (`invalid value "READ" for field "settings_documents"`), and so does `READ` for `banking`. The SDK doesn't check levels per area, the `ValidationException` carries Billomat's message.
- **Single-item list quirk.** With only one role Billomat returns an object instead of a list; `listResource()` normalizes this.

## End-to-end example

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\Enum\AccessLevel;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

foreach ($billomat->roles->list() as $role) {
    printf("Role %s (#%d):\n", $role->name ?? '?', $role->id);
    foreach ($role->permissions as $resource => $access) {
        printf("  %-25s %s%s\n", $resource, $access->label(), $access->allows(AccessLevel::DELETE) ? ' (full access)' : '');
    }
}
```
