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
        return $this->listResource('/dunning-levels', 'dunning-levels', 'dunning-level', DunningLevel::fromArray(...), $filters);
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
