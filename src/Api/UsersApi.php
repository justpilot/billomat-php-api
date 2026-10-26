<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Exception\NotFoundException;
use Justpilot\Billomat\Model\User;
use Justpilot\Billomat\Model\UserProperty;
use Justpilot\Billomat\Pagination\Page;
use NoDiscard;

/**
 * Read-only API-Wrapper für Mitarbeiter/Users.
 *
 * Doku: https://www.billomat.com/en/api/users/
 */
final class UsersApi extends AbstractApi
{
    /**
     * Attribut-Definitionen, siehe {@see PropertiesApi}.
     *
     * @var PropertiesApi<UserProperty>
     */
    public private(set) PropertiesApi $properties {
        get => $this->properties ??= new PropertiesApi($this->http, 'user', UserProperty::fromArray(...));
    }

    /**
     * @param UserFilter|array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return list<User>
     */
    #[NoDiscard]
    public function list(UserFilter|array $filters = []): array
    {
        return $this->listResource('/users', 'users', 'user', User::fromArray(...), $filters);
    }

    /**
     * Liefert eine einzelne Seite samt Pagination-Metadaten.
     *
     * Identisch zu {@see list()}, gibt aber zusätzlich `@page`/`@per_page`/
     * `@total` aus dem Response-Envelope als {@see PageInfo} zurück. Nützlich
     * für UI mit klassischer "Seite 1/12, 234 Treffer"-Anzeige.
     *
     * @param UserFilter|array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Page<User>
     */
    #[NoDiscard]
    public function listPage(UserFilter|array $filters = []): Page
    {
        return $this->listResourcePage('/users', 'users', 'user', User::fromArray(...), $filters);
    }

    /**
     * Iteriert lazy durch alle User und yieldet sie einzeln.
     *
     * Holt seitenweise pro {@code $pageSize}-Items und stoppt automatisch,
     * sobald die letzte Seite erreicht ist (analog `auto_paging_iter()` im
     * Stripe-SDK). Filter werden bei jeder Page-Anfrage mitgesendet; `page`
     * und `per_page` darin werden überschrieben.
     *
     * @param UserFilter|array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Generator<int, User>
     */
    #[NoDiscard]
    public function iterateAll(UserFilter|array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/users', 'users', 'user', User::fromArray(...), $filters, $pageSize);
    }

    #[NoDiscard]
    public function get(int $id): ?User
    {
        return $this->getResource("/users/{$id}", 'user', User::fromArray(...), 'fetching user');
    }

    /**
     * Wie {@see get()}, wirft aber eine {@see NotFoundException} statt null zurückzugeben.
     *
     * @throws NotFoundException wenn es den Datensatz nicht gibt (404)
     */
    #[NoDiscard]
    public function getOrFail(int $id): User
    {
        return $this->get($id) ?? throw $this->notFound("/users/{$id}");
    }

    /**
     * Gibt den aktuell authentifizierten Benutzer zurück.
     *
     * Den gibt es bei gültigem API-Key immer; anders als bei {@see get()} ist ein 404 hier ein
     * echter Fehler und kommt als {@see NotFoundException} durch.
     */
    #[NoDiscard]
    public function getMyself(): User
    {
        return User::fromArray($this->unwrapEnvelope($this->getJson('/users/myself'), 'user', 'fetching own user via /users/myself'));
    }
}
