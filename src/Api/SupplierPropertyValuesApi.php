<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Model\SupplierPropertyValue;
use Justpilot\Billomat\Pagination\Page;
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

    /**
     * Liefert eine einzelne Seite samt Pagination-Metadaten.
     *
     * Identisch zu {@see list()}, gibt aber zusätzlich `@page`/`@per_page`/
     * `@total` aus dem Response-Envelope als {@see PageInfo} zurück. Nützlich
     * für UI mit klassischer "Seite 1/12, 234 Treffer"-Anzeige.
     *
     * @param array<string, scalar|array|null> $filters
     *
     * @return Page<SupplierPropertyValue>
     */
    public function listPage(array $filters = []): Page
    {
        return $this->listResourcePage('/supplier-property-values', 'supplier-property-values', 'supplier-property-value', SupplierPropertyValue::fromArray(...), $filters);
    }

    /**
     * Iteriert lazy durch alle SupplierPropertyValue und yieldet sie einzeln.
     *
     * Holt seitenweise pro {@code $pageSize}-Items und stoppt automatisch,
     * sobald die letzte Seite erreicht ist (analog `auto_paging_iter()` im
     * Stripe-SDK). Filter werden bei jeder Page-Anfrage mitgesendet; `page`
     * und `per_page` darin werden überschrieben.
     *
     * @param array<string, scalar|array|null> $filters
     *
     * @return Generator<int, SupplierPropertyValue>
     */
    public function iterateAll(array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/supplier-property-values', 'supplier-property-values', 'supplier-property-value', SupplierPropertyValue::fromArray(...), $filters, $pageSize);
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
