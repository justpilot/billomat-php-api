<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\SupplierTag;
use Justpilot\Billomat\Model\SupplierTagCloudEntry;
use RuntimeException;

/**
 * API-Wrapper für Supplier-Tags.
 */
final class SupplierTagsApi extends AbstractApi
{
    /**
     * @return list<SupplierTag>
     */
    public function listBySupplier(int $supplierId): array
    {
        $data = $this->getJson('/supplier-tags', ['supplier_id' => $supplierId]);

        $root = $data['supplier-tags'] ?? null;
        if (!\is_array($root)) {
            return [];
        }

        $rows = $root['supplier-tag'] ?? [];
        if ([] === $rows || null === $rows) {
            return [];
        }

        if (isset($rows['id'])) {
            $rows = [$rows];
        }

        if (!\is_array($rows)) {
            return [];
        }

        /** @var list<SupplierTag> $tags */
        $tags = array_map(SupplierTag::fromArray(...), $rows);

        return $tags;
    }

    /**
     * @return list<SupplierTagCloudEntry>
     */
    public function cloud(): array
    {
        $data = $this->getJson('/supplier-tags');

        $root = $data['supplier-tags'] ?? null;
        if (!\is_array($root)) {
            return [];
        }

        $rows = $root['tag'] ?? [];
        if ([] === $rows || null === $rows) {
            return [];
        }

        if (isset($rows['name'])) {
            $rows = [$rows];
        }

        if (!\is_array($rows)) {
            return [];
        }

        /** @var list<SupplierTagCloudEntry> $tags */
        $tags = array_map(SupplierTagCloudEntry::fromArray(...), $rows);

        return $tags;
    }

    public function get(int $id): ?SupplierTag
    {
        $data = $this->getJsonOrNull("/supplier-tags/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['supplier-tag'] ?? null;
        if (!\is_array($row)) {
            return null;
        }

        return SupplierTag::fromArray($row);
    }

    public function create(SupplierTagCreateOptions $options): SupplierTag
    {
        $payload = ['supplier-tag' => $options->toArray()];

        $data = $this->postJson('/supplier-tags', $payload);

        $row = $data['supplier-tag'] ?? null;
        if (!\is_array($row)) {
            throw new RuntimeException('Unexpected response from Billomat when creating supplier tag.');
        }

        return SupplierTag::fromArray($row);
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/supplier-tags/{$id}");

        return true;
    }
}
