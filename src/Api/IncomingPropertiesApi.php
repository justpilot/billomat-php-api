<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\IncomingProperty;
use RuntimeException;

/**
 * API-Wrapper für Definitionen von Eigenschaften für Eingangsrechnungen.
 */
final class IncomingPropertiesApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array|null> $filters
     *
     * @return list<IncomingProperty>
     */
    public function list(array $filters = []): array
    {
        $data = $this->getJson('/incoming-properties', $filters);

        $node = $data['incoming-properties']['incoming-property'] ?? [];

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

        /** @var list<IncomingProperty> $models */
        $models = array_map(IncomingProperty::fromArray(...), $rows);

        return $models;
    }

    public function get(int $id): ?IncomingProperty
    {
        $data = $this->getJsonOrNull("/incoming-properties/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['incoming-property'] ?? null;
        if (!\is_array($row)) {
            return null;
        }

        return IncomingProperty::fromArray($row);
    }

    public function create(PropertyCreateOptions $options): IncomingProperty
    {
        $payload = ['incoming-property' => $options->toArray()];

        $data = $this->postJson('/incoming-properties', $payload);

        $row = $data['incoming-property'] ?? null;
        if (!\is_array($row)) {
            throw new RuntimeException('Unexpected response from Billomat when creating incoming property.');
        }

        return IncomingProperty::fromArray($row);
    }

    public function update(int $id, PropertyCreateOptions $options): IncomingProperty
    {
        $payload = ['incoming-property' => $options->toArray()];

        $data = $this->putJson("/incoming-properties/{$id}", $payload);

        $row = $data['incoming-property'] ?? null;
        if (!\is_array($row)) {
            throw new RuntimeException('Unexpected response from Billomat when updating incoming property.');
        }

        return IncomingProperty::fromArray($row);
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/incoming-properties/{$id}");

        return true;
    }
}
