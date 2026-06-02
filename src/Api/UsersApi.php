<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\User;

/**
 * Read-only API-Wrapper für Mitarbeiter/Users.
 *
 * Doku: https://www.billomat.com/en/api/users/
 */
final class UsersApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array|null> $filters
     *
     * @return list<User>
     */
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
