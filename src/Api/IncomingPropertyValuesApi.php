<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\IncomingPropertyValue;
use RuntimeException;

/**
 * API-Wrapper für Werte von Eingangsrechnungs-Eigenschaften.
 */
final class IncomingPropertyValuesApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array|null> $filters
     *
     * @return list<IncomingPropertyValue>
     */
    public function list(array $filters = []): array
    {
        $data = $this->getJson('/incoming-property-values', $filters);

        $root = $data['incoming-property-values'] ?? null;
        if (!\is_array($root)) {
            return [];
        }

        $rows = $root['incoming-property-value'] ?? [];

        if ([] === $rows || null === $rows) {
            return [];
        }

        if (isset($rows['id'])) {
            $rows = [$rows];
        }

        if (!\is_array($rows)) {
            return [];
        }

        /** @var list<IncomingPropertyValue> $values */
        $values = array_map(
            IncomingPropertyValue::fromArray(...),
            $rows,
        );

        return $values;
    }

    public function get(int $id): ?IncomingPropertyValue
    {
        $data = $this->getJsonOrNull("/incoming-property-values/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['incoming-property-value'] ?? null;
        if (!\is_array($row)) {
            return null;
        }

        return IncomingPropertyValue::fromArray($row);
    }

    public function create(IncomingPropertyValueCreateOptions $options): IncomingPropertyValue
    {
        $payload = ['incoming-property-value' => $options->toArray()];

        $data = $this->postJson('/incoming-property-values', $payload);

        $row = $data['incoming-property-value'] ?? null;
        if (!\is_array($row)) {
            throw new RuntimeException('Unexpected response from Billomat when creating incoming property value.');
        }

        return IncomingPropertyValue::fromArray($row);
    }
}
