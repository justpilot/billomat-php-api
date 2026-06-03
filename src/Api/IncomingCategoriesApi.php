<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Model\IncomingCategory;
use Justpilot\Billomat\Pagination\Page;

/**
 * Read-only API-Wrapper für Eingangsrechnungs-Kategorien.
 *
 * Quelle: https://www.billomat.com/api/eingangsrechnungen/kategorien/
 *
 * Billomat dokumentiert keine Mutations-Endpunkte für diese Ressource — die
 * Liste wird serverseitig gepflegt.
 */
final class IncomingCategoriesApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array|null> $filters
     *
     * @return list<IncomingCategory>
     */
    public function list(array $filters = []): array
    {
        return $this->listResource(
            '/incoming-categories',
            'incoming-categories',
            'incoming-category',
            IncomingCategory::fromArray(...),
            $filters,
        );
    }

    /**
     * @param array<string, scalar|array|null> $filters
     *
     * @return Page<IncomingCategory>
     */
    public function listPage(array $filters = []): Page
    {
        return $this->listResourcePage(
            '/incoming-categories',
            'incoming-categories',
            'incoming-category',
            IncomingCategory::fromArray(...),
            $filters,
        );
    }

    /**
     * @param array<string, scalar|array|null> $filters
     *
     * @return Generator<int, IncomingCategory>
     */
    public function iterateAll(array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource(
            '/incoming-categories',
            'incoming-categories',
            'incoming-category',
            IncomingCategory::fromArray(...),
            $filters,
            $pageSize,
        );
    }

    public function get(string $id): ?IncomingCategory
    {
        $data = $this->getJsonOrNull("/incoming-categories/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['incoming-category'] ?? null;
        if (!\is_array($row)) {
            return null;
        }

        return IncomingCategory::fromArray($row);
    }
}
