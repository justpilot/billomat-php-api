<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Model\Activity;
use Justpilot\Billomat\Pagination\Page;

/**
 * API-Wrapper für den Aktivitätsfeed (`/activity-feed`).
 *
 * Read-only Endpunkt — Aktivitäten werden ausschließlich von Billomat
 * geschrieben und können nicht angelegt, geändert oder gelöscht werden.
 *
 * Doku: https://www.billomat.com/api/aktivitaeten/
 */
final class ActivitiesApi extends AbstractApi
{
    /**
     * Listet Aktivitäten – optional gefiltert nach Ressource oder Benutzer.
     *
     * Entspricht GET /activity-feed.
     *
     * @param array<string, scalar|array|null> $filters
     *
     * @return list<Activity>
     */
    public function list(array $filters = []): array
    {
        return $this->listResource('/activity-feed', 'activity-feed', 'activity', Activity::fromArray(...), $filters);
    }

    /**
     * Eine einzelne Seite samt Pagination-Metadaten.
     *
     * @param array<string, scalar|array|null> $filters
     *
     * @return Page<Activity>
     */
    public function listPage(array $filters = []): Page
    {
        return $this->listResourcePage('/activity-feed', 'activity-feed', 'activity', Activity::fromArray(...), $filters);
    }

    /**
     * Iteriert lazy durch den vollständigen Feed.
     *
     * @param array<string, scalar|array|null> $filters
     *
     * @return Generator<int, Activity>
     */
    public function iterateAll(array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/activity-feed', 'activity-feed', 'activity', Activity::fromArray(...), $filters, $pageSize);
    }
}
