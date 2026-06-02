<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\IncomingProperty;

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
        return $this->listResource('/incoming-properties', 'incoming-properties', 'incoming-property', IncomingProperty::fromArray(...), $filters);
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

        return IncomingProperty::fromArray($this->unwrapEnvelope($data, 'incoming-property', 'creating incoming property'));
    }

    public function update(int $id, PropertyCreateOptions $options): IncomingProperty
    {
        $payload = ['incoming-property' => $options->toArray()];

        $data = $this->putJson("/incoming-properties/{$id}", $payload);

        return IncomingProperty::fromArray($this->unwrapEnvelope($data, 'incoming-property', 'updating incoming property'));
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/incoming-properties/{$id}");

        return true;
    }
}
