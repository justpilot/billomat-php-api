<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\DunningLevel;

/**
 * Read-only API-Wrapper für Mahnstufen.
 *
 * Doku: https://www.billomat.com/en/api/settings/dunning-levels/
 */
final class DunningLevelsApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array|null> $filters
     *
     * @return list<DunningLevel>
     */
    public function list(array $filters = []): array
    {
        $data = $this->getJson('/dunning-levels', $filters);

        $node = $data['dunning-levels']['dunning-level'] ?? [];

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

        /** @var list<DunningLevel> $models */
        $models = array_map(DunningLevel::fromArray(...), $rows);

        return $models;
    }

    public function get(int $id): ?DunningLevel
    {
        $data = $this->getJsonOrNull("/dunning-levels/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['dunning-level'] ?? null;
        if (!\is_array($row)) {
            return null;
        }

        return DunningLevel::fromArray($row);
    }
}
