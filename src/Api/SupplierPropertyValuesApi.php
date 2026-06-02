<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\SupplierPropertyValue;
use RuntimeException;

/**
 * API-Wrapper für Werte von Lieferanten-Eigenschaften.
 *
 * Doku: https://www.billomat.com/en/api/suppliers/properties/
 */
final class SupplierPropertyValuesApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array|null> $filters
     *
     * @return list<SupplierPropertyValue>
     */
    public function list(array $filters = []): array
    {
        $data = $this->getJson('/supplier-property-values', $filters);

        $root = $data['supplier-property-values'] ?? null;
        if (!\is_array($root)) {
            return [];
        }

        $rows = $root['supplier-property-value'] ?? [];

        if ([] === $rows || null === $rows) {
            return [];
        }

        if (isset($rows['id'])) {
            $rows = [$rows];
        }

        if (!\is_array($rows)) {
            return [];
        }

        /** @var list<SupplierPropertyValue> $values */
        $values = array_map(
            SupplierPropertyValue::fromArray(...),
            $rows,
        );

        return $values;
    }

    public function get(int $id): ?SupplierPropertyValue
    {
        $data = $this->getJsonOrNull("/supplier-property-values/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['supplier-property-value'] ?? null;
        if (!\is_array($row)) {
            return null;
        }

        return SupplierPropertyValue::fromArray($row);
    }

    public function create(SupplierPropertyValueCreateOptions $options): SupplierPropertyValue
    {
        $payload = ['supplier-property-value' => $options->toArray()];

        $data = $this->postJson('/supplier-property-values', $payload);

        $row = $data['supplier-property-value'] ?? null;
        if (!\is_array($row)) {
            throw new RuntimeException('Unexpected response from Billomat when creating supplier property value.');
        }

        return SupplierPropertyValue::fromArray($row);
    }
}
