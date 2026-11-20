<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Exception\NotFoundException;
use Justpilot\Billomat\Model\Role;
use Justpilot\Billomat\Pagination\Page;
use NoDiscard;

/**
 * API-Wrapper für Mitarbeiter-Rollen.
 *
 * Quelle: https://www.billomat.com/api/einstellungen/rollen/
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
        return $this->getResource("/roles/{$id}", 'role', Role::fromArray(...), 'fetching role');
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

    /**
     * Legt eine neue Rolle an.
     */
    public function create(RoleCreateOptions $options): Role
    {
        $data = $this->postJson('/roles', ['role' => $options->toArray()]);

        return Role::fromArray($this->unwrapEnvelope($data, 'role', 'creating role'));
    }

    /**
     * Ändert eine Rolle (PUT /roles/{id}).
     *
     * Nur gesetzte Felder werden gesendet; Bereiche ohne Eintrag in den Options behalten ihre Rechte.
     */
    public function update(int $id, RoleUpdateOptions $options): Role
    {
        $data = $this->putJson("/roles/{$id}", ['role' => $options->toArray()]);

        return Role::fromArray($this->unwrapEnvelope($data, 'role', 'updating role'));
    }

    /**
     * Löscht eine Rolle.
     */
    public function delete(int $id): void
    {
        $this->deleteVoid("/roles/{$id}");
    }
}
