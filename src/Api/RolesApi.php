<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Exception\NotFoundException;
use Justpilot\Billomat\Model\Role;
use Justpilot\Billomat\Pagination\Page;
use NoDiscard;

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
     * @param array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return list<Role>
     */
    #[NoDiscard]
    public function list(array $filters = []): array
    {
        return $this->listResource('/roles', 'roles', 'role', Role::fromArray(...), $filters);
    }

    /**
     * @param array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Page<Role>
     */
    #[NoDiscard]
    public function listPage(array $filters = []): Page
    {
        return $this->listResourcePage('/roles', 'roles', 'role', Role::fromArray(...), $filters);
    }

    /**
     * @param array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Generator<int, Role>
     */
    #[NoDiscard]
    public function iterateAll(array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/roles', 'roles', 'role', Role::fromArray(...), $filters, $pageSize);
    }

    #[NoDiscard]
    public function get(int $id): ?Role
    {
        $data = $this->getJsonOrNull("/roles/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['role'] ?? null;
        if (!self::isRecord($row)) {
            return null;
        }

        return Role::fromArray($row);
    }

    /**
     * Wie {@see get()}, wirft aber eine {@see NotFoundException} statt null zurückzugeben.
     *
     * @throws NotFoundException wenn es den Datensatz nicht gibt (404)
     */
    #[NoDiscard]
    public function getOrFail(int $id): Role
    {
        return $this->get($id) ?? throw $this->notFound("/roles/{$id}");
    }
}
