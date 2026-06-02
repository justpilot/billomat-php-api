<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\ClientProperty;

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
        return $this->listResource('/client-properties', 'client-properties', 'client-property', ClientProperty::fromArray(...), $filters);
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

        return ClientProperty::fromArray($this->unwrapEnvelope($data, 'client-property', 'creating client property'));
    }

    public function update(int $id, PropertyCreateOptions $options): ClientProperty
    {
        $payload = ['client-property' => $options->toArray()];

        $data = $this->putJson("/client-properties/{$id}", $payload);

        return ClientProperty::fromArray($this->unwrapEnvelope($data, 'client-property', 'updating client property'));
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/client-properties/{$id}");

        return true;
    }
}
