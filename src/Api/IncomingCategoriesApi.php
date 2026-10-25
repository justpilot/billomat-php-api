<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Exception\NotFoundException;
use Justpilot\Billomat\Model\IncomingCategory;
use Justpilot\Billomat\Pagination\Page;
use NoDiscard;

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
     * @param array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return list<IncomingCategory>
     */
    #[NoDiscard]
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
     * @param array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Page<IncomingCategory>
     */
    #[NoDiscard]
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
     * @param array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Generator<int, IncomingCategory>
     */
    #[NoDiscard]
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

    #[NoDiscard]
    public function get(string $id): ?IncomingCategory
    {
        $data = $this->getJsonOrNull("/incoming-categories/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['incoming-category'] ?? null;
        if (!self::isRecord($row)) {
            return null;
        }

        return IncomingCategory::fromArray($row);
    }

    /**
     * Wie {@see get()}, wirft aber eine {@see NotFoundException} statt null zurückzugeben.
     *
     * @throws NotFoundException wenn es den Datensatz nicht gibt (404)
     */
    #[NoDiscard]
    public function getOrFail(string $id): IncomingCategory
    {
        return $this->get($id) ?? throw $this->notFound("/incoming-categories/{$id}");
    }
}
