<!-- Quelle: https://www.billomat.com/api/benutzer/ -->

# Users (Benutzer)

Read-only API-Wrapper für die Billomat-Ressource „Benutzer" — entspricht den Endpunkten unter `/users`.

## Zugriff

```php
$billomat->users
```

`Justpilot\Billomat\Api\UsersApi`, intern angelegt in `BillomatClient::__construct()`.

## Endpunkt-Übersicht

| Methode | HTTP | Pfad |
|---|---|---|
| `list()` | GET | `/users` |
| `listPage()` | GET | `/users` (Seite mit Metadaten) |
| `iterateAll()` | GET | `/users` (lazy über alle Seiten) |
| `get($id)` | GET | `/users/{id}` |
| `getMyself()` | GET | `/users/myself` |

Avatar-Endpunkt (`GET /users/{id}/avatar`) ist im SDK nicht implementiert; bei Bedarf direkt über `BillomatClient::getHttpClient()` aufrufen.

## Methoden

### `list(array $filters = []): list<User>`

Listet Benutzer mit optionalen Filtern.

```php
public function list(array $filters = []): array
```

Unterstützte Filter laut Billomat-Doku:

| Filter | Bedeutung |
|---|---|
| `email` | E-Mail-Adresse (case-insensitiv) |
| `first_name` | Vorname |
| `last_name` | Nachname |

```php
$users = $billomat->users->list([
    'email' => '@example.com',
]);

foreach ($users as $user) {
    echo $user->id . ': ' . $user->email . PHP_EOL;
}
```

**Exceptions:** `AuthenticationException`, `HttpException`.

### `listPage(array $filters = []): Page<User>`

Wie `list()`, liefert aber zusätzlich `page`/`per_page`/`total` aus dem Response-Envelope.

```php
$page = $billomat->users->listPage(['per_page' => 25]);

echo $page->total, ' Benutzer (Seite ', $page->page, '/', $page->totalPages, ')';
```

### `iterateAll(array $filters = [], int $pageSize = 100): Generator<int, User>`

Lazy-Iteration über alle Seiten, analog `auto_paging_iter()` im Stripe-SDK.

```php
foreach ($billomat->users->iterateAll() as $user) {
    // ...
}
```

### `get(int $id): ?User`

Holt einen Benutzer per ID. Liefert `null` bei 404.

```php
$user = $billomat->users->get(123);
```

**Exceptions:** `AuthenticationException`, `HttpException`.

### `getMyself(): ?User`

Gibt den aktuell authentifizierten Benutzer zurück (`GET /users/myself`).

```php
$me = $billomat->users->getMyself();
echo $me?->email;
```

## Stolpersteine

- **Kein Schreibzugriff per API.** Billomat dokumentiert für `/users` ausschliesslich Lese-Endpunkte (list, single, avatar, myself). Es existiert kein offizielles `POST/PUT/DELETE /users`. Benutzerverwaltung — Anlegen, Rolle ändern, Passwort, Löschen — geschieht ausschliesslich über die Billomat-Web-UI. Das SDK exponiert daher absichtlich keine `create`/`update`/`delete`-Verben für diese Ressource.
- **`role_id` ist nur Read-Lookup.** Die Rolle eines Benutzers liest sich aus `User::$roleId`; die Roll-Definitionen selbst kommen aus [`settings-roles.md`](settings-roles.md). Die Zuordnung „User → Rolle" verändert man in der UI, nicht hier.

## Read-Modell: `User`

`final readonly class Justpilot\Billomat\Model\User`

| Property | Typ | Mapping |
|---|---|---|
| `id` | `?int` | `id` |
| `email` | `?string` | `email` |
| `firstName` | `?string` | `first_name` |
| `lastName` | `?string` | `last_name` |
| `salutation` | `?string` | `salutation` |
| `phone` | `?string` | `phone` |
| `mobile` | `?string` | `mobile` |
| `fax` | `?string` | `fax` |
| `roleId` | `?int` | `role_id` |

`User::fromArray($data)` hydratisiert aus dem Billomat-Wire-Format; `User::toArray()` schreibt zurück (snake_case).
