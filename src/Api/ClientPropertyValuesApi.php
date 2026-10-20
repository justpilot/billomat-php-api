<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Model\ClientPropertyValue;
use Justpilot\Billomat\Pagination\Page;

/**
 * API-Wrapper für Werte von Kunden-Eigenschaften.
 *
 * Doku: https://www.billomat.com/api/kunden/attribute/
 */
final class ClientPropertyValuesApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array<mixed>|null> $filters
     *
     * @return list<ClientPropertyValue>
     */
    public function list(array $filters = []): array
    {
        return $this->listResource('/client-property-values', 'client-property-values', 'client-property-value', ClientPropertyValue::fromArray(...), $filters);
    }

    /**
     * Liefert eine einzelne Seite samt Pagination-Metadaten.
     *
     * @param array<string, scalar|array<mixed>|null> $filters
     *
     * @return Page<ClientPropertyValue>
     */
    public function listPage(array $filters = []): Page
    {
        return $this->listResourcePage('/client-property-values', 'client-property-values', 'client-property-value', ClientPropertyValue::fromArray(...), $filters);
    }

    /**
     * Iteriert lazy durch alle ClientPropertyValue und yieldet sie einzeln.
     *
     * @param array<string, scalar|array<mixed>|null> $filters
     *
     * @return Generator<int, ClientPropertyValue>
     */
    public function iterateAll(array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/client-property-values', 'client-property-values', 'client-property-value', ClientPropertyValue::fromArray(...), $filters, $pageSize);
    }

    public function get(int $id): ?ClientPropertyValue
    {
        $data = $this->getJsonOrNull("/client-property-values/{$id}");

        if (null === $data) {
            return null;
        }

        return ClientPropertyValue::fromArray($this->unwrapEnvelope($data, 'client-property-value', 'fetching client property value'));
    }

    public function create(ClientPropertyValueCreateOptions $options): ClientPropertyValue
    {
        $payload = ['client-property-value' => $options->toArray()];

        $data = $this->postJson('/client-property-values', $payload);

        return ClientPropertyValue::fromArray($this->unwrapEnvelope($data, 'client-property-value', 'creating client property value'));
    }
}
