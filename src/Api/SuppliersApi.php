<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Model\Supplier;
use RuntimeException;

/**
 * API-Wrapper für die Billomat-Suppliers-Ressource (Lieferanten).
 *
 * Doku: https://www.billomat.com/en/api/suppliers/
 *
 * Endpoints:
 *  - GET    /suppliers
 *  - GET    /suppliers/{id}
 *  - POST   /suppliers
 *  - PUT    /suppliers/{id}
 *  - DELETE /suppliers/{id}
 */
final class SuppliersApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array|null> $filters
     *
     * @return list<Supplier>
     */
    public function list(array $filters = []): array
    {
        $data = $this->getJson('/suppliers', $filters);

        $node = $data['suppliers']['supplier'] ?? [];

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

        /** @var list<Supplier> $models */
        $models = array_map(
            Supplier::fromArray(...),
            $rows,
        );

        return $models;
    }

    public function get(int $id): ?Supplier
    {
        $data = $this->getJsonOrNull("/suppliers/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['supplier'] ?? null;

        if (!\is_array($row)) {
            return null;
        }

        return Supplier::fromArray($row);
    }

    /**
     * @throws ValidationException
     * @throws AuthenticationException
     * @throws HttpException
     */
    public function create(SupplierCreateOptions $options): Supplier
    {
        $payload = ['supplier' => $options->toArray()];

        $data = $this->postJson('/suppliers', $payload);

        $created = $data['supplier'] ?? null;

        if (!\is_array($created)) {
            throw new RuntimeException('Unexpected response from Billomat when creating supplier.');
        }

        return Supplier::fromArray($created);
    }

    public function update(int $id, SupplierUpdateOptions $options): Supplier
    {
        $payload = ['supplier' => $options->toArray()];

        $data = $this->putJson("/suppliers/{$id}", $payload);

        $row = $data['supplier'] ?? null;
        if (!\is_array($row)) {
            throw new RuntimeException('Unexpected response from Billomat when updating supplier.');
        }

        return Supplier::fromArray($row);
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/suppliers/{$id}");

        return true;
    }
}
