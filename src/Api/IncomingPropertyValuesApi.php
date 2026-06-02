<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\IncomingPropertyValue;

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
        return $this->listResource('/incoming-property-values', 'incoming-property-values', 'incoming-property-value', IncomingPropertyValue::fromArray(...), $filters);
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

        return IncomingPropertyValue::fromArray($this->unwrapEnvelope($data, 'incoming-property-value', 'creating incoming property value'));
    }
}
