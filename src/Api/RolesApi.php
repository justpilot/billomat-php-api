<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Model\Role;
use Justpilot\Billomat\Pagination\Page;

/**
 * Read-only API-Wrapper für Mitarbeiter-Rollen.
 *
 * Quelle: https://www.billomat.com/api/einstellungen/rollen/
 *
 * Billomat unterstützt zusätzlich POST/PUT/DELETE auf `/roles`. Das SDK
 * exponiert diese Verben aktuell nicht — wer Rollen verwaltet, kann
 * `BillomatHttpClient` direkt nutzen.
 *
 * Filter:
 *  - `name` — case-insensitive Teilstring-Suche auf dem Rollennamen.
 */
final class RolesApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array|null> $filters
     *
     * @return list<Role>
     */
    public function list(array $filters = []): array
    {
        return $this->listResource('/roles', 'roles', 'role', Role::fromArray(...), $filters);
    }

    /**
     * @param array<string, scalar|array|null> $filters
     *
     * @return Page<Role>
     */
    public function listPage(array $filters = []): Page
    {
        return $this->listResourcePage('/roles', 'roles', 'role', Role::fromArray(...), $filters);
    }

    /**
     * @param array<string, scalar|array|null> $filters
     *
     * @return Generator<int, Role>
     */
    public function iterateAll(array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/roles', 'roles', 'role', Role::fromArray(...), $filters, $pageSize);
    }

    public function get(int $id): ?Role
    {
        $data = $this->getJsonOrNull("/roles/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['role'] ?? null;
        if (!\is_array($row)) {
            return null;
        }

        return Role::fromArray($row);
    }
}
