<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Unit;

/**
 * Read-only API-Wrapper für Einheiten.
 */
final class UnitsApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array|null> $filters
     *
     * @return list<Unit>
     */
    public function list(array $filters = []): array
    {
        $data = $this->getJson('/units', $filters);

        $node = $data['units']['unit'] ?? [];

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

        /** @var list<Unit> $models */
        $models = array_map(Unit::fromArray(...), $rows);

        return $models;
    }

    public function get(int $id): ?Unit
    {
        $data = $this->getJsonOrNull("/units/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['unit'] ?? null;
        if (!\is_array($row)) {
            return null;
        }

        return Unit::fromArray($row);
    }
}
