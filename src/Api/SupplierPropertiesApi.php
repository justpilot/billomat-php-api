<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\SupplierProperty;
use RuntimeException;

/**
 * API-Wrapper für Definitionen von Lieferanten-Eigenschaften.
 *
 * Doku: https://www.billomat.com/en/api/settings/supplier-properties/
 */
final class SupplierPropertiesApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array|null> $filters
     *
     * @return list<SupplierProperty>
     */
    public function list(array $filters = []): array
    {
        $data = $this->getJson('/supplier-properties', $filters);

        $node = $data['supplier-properties']['supplier-property'] ?? [];

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

        /** @var list<SupplierProperty> $models */
        $models = array_map(SupplierProperty::fromArray(...), $rows);

        return $models;
    }

    public function get(int $id): ?SupplierProperty
    {
        $data = $this->getJsonOrNull("/supplier-properties/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['supplier-property'] ?? null;
        if (!\is_array($row)) {
            return null;
        }

        return SupplierProperty::fromArray($row);
    }

    public function create(PropertyCreateOptions $options): SupplierProperty
    {
        $payload = ['supplier-property' => $options->toArray()];

        $data = $this->postJson('/supplier-properties', $payload);

        $row = $data['supplier-property'] ?? null;
        if (!\is_array($row)) {
            throw new RuntimeException('Unexpected response from Billomat when creating supplier property.');
        }

        return SupplierProperty::fromArray($row);
    }

    public function update(int $id, PropertyCreateOptions $options): SupplierProperty
    {
        $payload = ['supplier-property' => $options->toArray()];

        $data = $this->putJson("/supplier-properties/{$id}", $payload);

        $row = $data['supplier-property'] ?? null;
        if (!\is_array($row)) {
            throw new RuntimeException('Unexpected response from Billomat when updating supplier property.');
        }

        return SupplierProperty::fromArray($row);
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/supplier-properties/{$id}");

        return true;
    }
}
