<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\ClientProperty;
use RuntimeException;

/**
 * API-Wrapper für Definitionen von Kunden-Eigenschaften.
 *
 * Doku: https://www.billomat.com/en/api/settings/client-properties/
 */
final class ClientPropertiesApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array|null> $filters
     *
     * @return list<ClientProperty>
     */
    public function list(array $filters = []): array
    {
        $data = $this->getJson('/client-properties', $filters);

        $node = $data['client-properties']['client-property'] ?? [];

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

        /** @var list<ClientProperty> $models */
        $models = array_map(ClientProperty::fromArray(...), $rows);

        return $models;
    }

    public function get(int $id): ?ClientProperty
    {
        $data = $this->getJsonOrNull("/client-properties/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['client-property'] ?? null;
        if (!\is_array($row)) {
            return null;
        }

        return ClientProperty::fromArray($row);
    }

    public function create(PropertyCreateOptions $options): ClientProperty
    {
        $payload = ['client-property' => $options->toArray()];

        $data = $this->postJson('/client-properties', $payload);

        $row = $data['client-property'] ?? null;
        if (!\is_array($row)) {
            throw new RuntimeException('Unexpected response from Billomat when creating client property.');
        }

        return ClientProperty::fromArray($row);
    }

    public function update(int $id, PropertyCreateOptions $options): ClientProperty
    {
        $payload = ['client-property' => $options->toArray()];

        $data = $this->putJson("/client-properties/{$id}", $payload);

        $row = $data['client-property'] ?? null;
        if (!\is_array($row)) {
            throw new RuntimeException('Unexpected response from Billomat when updating client property.');
        }

        return ClientProperty::fromArray($row);
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/client-properties/{$id}");

        return true;
    }
}
