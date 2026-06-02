<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Model\SupplierProperty;
use Justpilot\Billomat\Pagination\Page;
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

    /**
     * Liefert eine einzelne Seite samt Pagination-Metadaten.
     *
     * Identisch zu {@see list()}, gibt aber zusätzlich `@page`/`@per_page`/
     * `@total` aus dem Response-Envelope als {@see PageInfo} zurück. Nützlich
     * für UI mit klassischer "Seite 1/12, 234 Treffer"-Anzeige.
     *
     * @param array<string, scalar|array|null> $filters
     *
     * @return Page<SupplierProperty>
     */
    public function listPage(array $filters = []): Page
    {
        return $this->listResourcePage('/supplier-properties', 'supplier-properties', 'supplier-property', SupplierProperty::fromArray(...), $filters);
    }

    /**
     * Iteriert lazy durch alle SupplierProperty und yieldet sie einzeln.
     *
     * Holt seitenweise pro {@code $pageSize}-Items und stoppt automatisch,
     * sobald die letzte Seite erreicht ist (analog `auto_paging_iter()` im
     * Stripe-SDK). Filter werden bei jeder Page-Anfrage mitgesendet; `page`
     * und `per_page` darin werden überschrieben.
     *
     * @param array<string, scalar|array|null> $filters
     *
     * @return Generator<int, SupplierProperty>
     */
    public function iterateAll(array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/supplier-properties', 'supplier-properties', 'supplier-property', SupplierProperty::fromArray(...), $filters, $pageSize);
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
