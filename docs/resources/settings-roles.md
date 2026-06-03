<!-- Quelle: https://www.billomat.com/api/einstellungen/rollen/ -->

# Roles (Mitarbeiter-Rollen)

API-Wrapper für Mitarbeiter-Rollen unter `/roles`. Rollen bündeln Zugriffsrechte pro Ressource (Artikel, Kunden, Rechnungen …) und werden Benutzern zugewiesen — siehe [users.md](users.md) für den `roleId`-Bezug.

## Zugriff

```php
$billomat->roles
```

`Justpilot\Billomat\Api\RolesApi`.

## Endpunkt-Übersicht

| Methode | HTTP | Pfad |
|---|---|---|
| `list($filters?)` | GET | `/roles` |
| `listPage($filters?)` | GET | `/roles` |
| `iterateAll($filters?, $pageSize?)` | GET | `/roles` (mehrseitig) |
| `get($id)` | GET | `/roles/{id}` |

> Read-only im SDK. Anlegen/Bearbeiten/Löschen sind bei Billomat verfügbar (`POST`/`PUT`/`DELETE /roles`), aber nicht als SDK-Methoden exponiert.

## Methoden

### `list(array $filters = []): list<Role>`

```php
foreach ($billomat->roles->list() as $role) {
    printf("#%d %s\n", $role->id ?? 0, $role->name ?? '');
}
```

Filter:

| Parameter | Beschreibung |
|---|---|
| `name` | Teilstring-Suche auf dem Rollennamen; case-insensitive |
| `page`, `per_page` | Pagination — siehe [Konzept](../concepts/pagination-and-filtering.md) |

### `get(int $id): ?Role`

Liefert `null` bei 404.

## Read-Modell: `Role`

`final readonly class Role`.

| Property | Billomat-Feld | Typ |
|---|---|---|
| `id` | `id` | `?int` |
| `name` | `name` | `?string` |
| `permissions` | (mehrere Felder, s.u.) | `array<string,?string>` |

### Rechte-Werte

Jedes Permission-Feld enthält einen von vier Werten:

| Wert | Bedeutung |
|---|---|
| `READ` | Lesen |
| `UPDATE` | Bearbeiten (impliziert `READ`) |
| `DELETE` | Vollzugriff (impliziert `UPDATE`+`READ`) |
| `null` | Kein Zugriff (Billomat sendet einen leeren String; `ScalarCaster` normalisiert ihn auf `null`) |

### Bekannte Permission-Keys

`articles`, `clients`, `offers`, `confirmations`, `invoices`, `credit_notes`, `delivery_notes`, `reminders`, `settings_my_account`, `settings_documents`, `settings_configuration`, `settings_administration`, `settings_addons`, `settings_my_addons`.

`fromArray()` nimmt zusätzlich jeden Key entgegen, der mit `settings_` beginnt — falls Billomat das Set erweitert, landen neue Settings-Rechte automatisch in `permissions`.

## Stolpersteine

- **Master-Rolle nicht löschbar.** Die System-Rolle `Master` (üblicherweise `id=1`) kann auch via UI nicht entfernt werden.
- **Empty-String → null.** Wer prüft, ob eine Rolle ein Recht hat, vergleicht `$role->permissions['articles'] === null` (kein Recht) statt `=== ''`.
- **Single-Item-List-Quirk.** Bei nur einer Rolle liefert Billomat ein Objekt statt einer Liste — `listResource()` normalisiert.

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

foreach ($billomat->roles->list() as $role) {
    printf("Rolle %s (#%d):\n", $role->name ?? '?', $role->id ?? 0);
    foreach ($role->permissions as $resource => $access) {
        if (null === $access) {
            continue;
        }
        printf("  %-25s %s\n", $resource, $access);
    }
}
```
