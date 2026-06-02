<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Model\Recurring;
use Justpilot\Billomat\Pagination\Page;

/**
 * API-Wrapper für Abo-Rechnungen (Recurrings).
 *
 * Endpoints:
 *  - GET    /recurrings
 *  - GET    /recurrings/{id}
 *  - POST   /recurrings
 *  - PUT    /recurrings/{id}
 *  - DELETE /recurrings/{id}
 *
 * Doku: https://www.billomat.com/api/abo-rechnungen/
 */
final class RecurringsApi extends AbstractApi
{
    /**
     * Listet Abo-Rechnungen mit optionalen Filtern.
     *
     * Unterstützte Filter laut Doku: client_id, contact_id, name, payment_type,
     * cycle_number, cycle, label, intro, note, tags, article_id.
     *
     * @param array<string, scalar|array|null> $filters
     *
     * @return list<Recurring>
     */
    public function list(array $filters = []): array
    {
        return $this->listResource('/recurrings', 'recurrings', 'recurring', Recurring::fromArray(...), $filters);
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
     * @return Page<Recurring>
     */
    public function listPage(array $filters = []): Page
    {
        return $this->listResourcePage('/recurrings', 'recurrings', 'recurring', Recurring::fromArray(...), $filters);
    }

    /**
     * Iteriert lazy durch alle Abo-Rechnungen und yieldet sie einzeln.
     *
     * Holt seitenweise pro {@code $pageSize}-Items und stoppt automatisch,
     * sobald die letzte Seite erreicht ist (analog `auto_paging_iter()` im
     * Stripe-SDK). Filter werden bei jeder Page-Anfrage mitgesendet; `page`
     * und `per_page` darin werden überschrieben.
     *
     * @param array<string, scalar|array|null> $filters
     *
     * @return Generator<int, Recurring>
     */
    public function iterateAll(array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/recurrings', 'recurrings', 'recurring', Recurring::fromArray(...), $filters, $pageSize);
    }

    public function get(int $id): ?Recurring
    {
        $data = $this->getJsonOrNull("/recurrings/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['recurring'] ?? null;
        if (!\is_array($row)) {
            return null;
        }

        return Recurring::fromArray($row);
    }

    public function create(RecurringCreateOptions $options): Recurring
    {
        $payload = ['recurring' => $options->toArray()];

        $data = $this->postJson('/recurrings', $payload);

        return Recurring::fromArray($this->unwrapEnvelope($data, 'recurring', 'creating recurring'));
    }

    public function update(int $id, RecurringUpdateOptions $options): Recurring
    {
        $payload = ['recurring' => $options->toArray()];

        $data = $this->putJson("/recurrings/{$id}", $payload);

        return Recurring::fromArray($this->unwrapEnvelope($data, 'recurring', 'updating recurring'));
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/recurrings/{$id}");

        return true;
    }
}
