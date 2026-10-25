<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
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
     * @param array<string, scalar|array|null> $filters
     *
     * @return list<User>
     */
    #[NoDiscard]
    public function list(array $filters = []): array
    {
        $data = $this->getJson('/users', $filters);

        $node = $data['users']['user'] ?? [];

        if (null === $node || [] === $node) {
            return [];
        }

        if (\is_array($node) && array_is_list($node)) {
            $rows = $node;
        } elseif (\is_array($node)) {
            $rows = [$node];
        } else {
            $rows = [];
        }

        /** @var list<User> $models */
        $models = array_map(User::fromArray(...), $rows);

        return $models;
    }

    /**
     * Liefert eine einzelne Seite samt Pagination-Metadaten.
     *
     * Identisch zu {@see list()}, gibt aber zusätzlich `@page`/`@per_page`/
     * `@total` aus dem Response-Envelope als {@see PageInfo} zurück. Nützlich
     * für UI mit klassischer "Seite 1/12, 234 Treffer"-Anzeige.
     *
     * @param array<string, scalar|array|null> $filters
     *
     * @return Page<User>
     */
    #[NoDiscard]
    public function listPage(array $filters = []): Page
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
     * @param array<string, scalar|array|null> $filters
     *
     * @return Generator<int, User>
     */
    #[NoDiscard]
    public function iterateAll(array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/users', 'users', 'user', User::fromArray(...), $filters, $pageSize);
    }

    #[NoDiscard]
    public function get(int $id): ?User
    {
        $data = $this->getJsonOrNull("/users/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['user'] ?? null;
        if (!\is_array($row)) {
            return null;
        }

        return User::fromArray($row);
    }

    /** Gibt den aktuell authentifizierten Benutzer zurück. */
    #[NoDiscard]
    public function getMyself(): ?User
    {
        $data = $this->getJsonOrNull('/users/myself');

        if (null === $data) {
            return null;
        }

        $row = $data['user'] ?? null;
        if (!\is_array($row)) {
            return null;
        }

        return User::fromArray($row);
    }
}
