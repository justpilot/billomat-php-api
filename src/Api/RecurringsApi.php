<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Recurring;
use RuntimeException;

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
        $data = $this->getJson('/recurrings', $filters);

        $node = $data['recurrings']['recurring'] ?? [];

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

        /** @var list<Recurring> $models */
        $models = array_map(Recurring::fromArray(...), $rows);

        return $models;
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

        $row = $data['recurring'] ?? null;
        if (!\is_array($row)) {
            throw new RuntimeException('Unexpected response from Billomat when creating recurring.');
        }

        return Recurring::fromArray($row);
    }

    public function update(int $id, RecurringUpdateOptions $options): Recurring
    {
        $payload = ['recurring' => $options->toArray()];

        $data = $this->putJson("/recurrings/{$id}", $payload);

        $row = $data['recurring'] ?? null;
        if (!\is_array($row)) {
            throw new RuntimeException('Unexpected response from Billomat when updating recurring.');
        }

        return Recurring::fromArray($row);
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/recurrings/{$id}");

        return true;
    }
}
