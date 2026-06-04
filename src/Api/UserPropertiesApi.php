<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Model\UserProperty;
use Justpilot\Billomat\Pagination\Page;

/**
 * API-Wrapper für Definitionen von Benutzer-Eigenschaften.
 *
 * Quelle: https://www.billomat.com/api/einstellungen/benutzer-attribute/
 *
 * Endpoints:
 *  - GET    /user-properties
 *  - GET    /user-properties/{id}
 *  - POST   /user-properties
 *  - PUT    /user-properties/{id}
 *  - DELETE /user-properties/{id}
 *
 * Damit ergänzt diese API die vier bestehenden Property-Definition-Endpoints
 * (article-, client-, supplier-, incoming-properties) um den fünften Parent
 * "User". Anders als bei Kunden/Artikeln/Lieferanten/Eingangsrechnungen gibt
 * es bei Billomat **keinen** Property-Values-Endpoint für User — Werte werden
 * direkt am Benutzer-Datensatz gepflegt.
 */
final class UserPropertiesApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array|null> $filters
     *
     * @return list<UserProperty>
     */
    public function list(array $filters = []): array
    {
        return $this->listResource('/user-properties', 'user-properties', 'user-property', UserProperty::fromArray(...), $filters);
    }

    /**
     * @param array<string, scalar|array|null> $filters
     *
     * @return Page<UserProperty>
     */
    public function listPage(array $filters = []): Page
    {
        return $this->listResourcePage('/user-properties', 'user-properties', 'user-property', UserProperty::fromArray(...), $filters);
    }

    /**
     * @param array<string, scalar|array|null> $filters
     *
     * @return Generator<int, UserProperty>
     */
    public function iterateAll(array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/user-properties', 'user-properties', 'user-property', UserProperty::fromArray(...), $filters, $pageSize);
    }

    public function get(int $id): ?UserProperty
    {
        $data = $this->getJsonOrNull("/user-properties/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['user-property'] ?? null;
        if (!\is_array($row)) {
            return null;
        }

        return UserProperty::fromArray($row);
    }

    public function create(PropertyCreateOptions $options): UserProperty
    {
        $payload = ['user-property' => $options->toArray()];

        $data = $this->postJson('/user-properties', $payload);

        return UserProperty::fromArray($this->unwrapEnvelope($data, 'user-property', 'creating user property'));
    }

    public function update(int $id, PropertyCreateOptions $options): UserProperty
    {
        $payload = ['user-property' => $options->toArray()];

        $data = $this->putJson("/user-properties/{$id}", $payload);

        return UserProperty::fromArray($this->unwrapEnvelope($data, 'user-property', 'updating user property'));
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/user-properties/{$id}");

        return true;
    }
}
