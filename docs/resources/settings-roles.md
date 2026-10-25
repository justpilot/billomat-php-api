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

> Read-only in the SDK. Billomat supports create/update/delete (`POST`/`PUT`/`DELETE /roles`), but the SDK doesn't expose them as methods.

## Methods

### `list(array $filters = []): list<Role>`

```php
foreach ($billomat->roles->list() as $role) {
    printf("#%d %s\n", $role->id ?? 0, $role->name ?? '');
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

## Read model: `Role`

`final readonly class Role`.

| Property | Billomat field | Type |
|---|---|---|
| `id` | `id` | `?int` |
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

## Pitfalls

- **The master role can't be deleted.** The system role `Master` (usually `id=1`) can't be removed, not even in the UI.
- **No access → no entry.** Areas without access, and values the SDK doesn't know, are not in `permissions`; check with `$role->permissions['articles'] ?? null`, or `($role->permissions['articles'] ?? null)?->allows(AccessLevel::UPDATE) ?? false` for a minimum level.
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
    printf("Role %s (#%d):\n", $role->name ?? '?', $role->id ?? 0);
    foreach ($role->permissions as $resource => $access) {
        printf("  %-25s %s%s\n", $resource, $access->label(), $access->allows(AccessLevel::DELETE) ? ' (full access)' : '');
    }
}
```
