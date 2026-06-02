<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Model\Unit;
use Justpilot\Billomat\Pagination\Page;

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

    /**
     * Liefert eine einzelne Seite samt Pagination-Metadaten.
     *
     * Identisch zu {@see list()}, gibt aber zusätzlich `@page`/`@per_page`/
     * `@total` aus dem Response-Envelope als {@see PageInfo} zurück. Nützlich
     * für UI mit klassischer "Seite 1/12, 234 Treffer"-Anzeige.
     *
     * @param array<string, scalar|array|null> $filters
     *
     * @return Page<Unit>
     */
    public function listPage(array $filters = []): Page
    {
        return $this->listResourcePage('/units', 'units', 'unit', Unit::fromArray(...), $filters);
    }

    /**
     * Iteriert lazy durch alle Unit und yieldet sie einzeln.
     *
     * Holt seitenweise pro {@code $pageSize}-Items und stoppt automatisch,
     * sobald die letzte Seite erreicht ist (analog `auto_paging_iter()` im
     * Stripe-SDK). Filter werden bei jeder Page-Anfrage mitgesendet; `page`
     * und `per_page` darin werden überschrieben.
     *
     * @param array<string, scalar|array|null> $filters
     *
     * @return Generator<int, Unit>
     */
    public function iterateAll(array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/units', 'units', 'unit', Unit::fromArray(...), $filters, $pageSize);
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
