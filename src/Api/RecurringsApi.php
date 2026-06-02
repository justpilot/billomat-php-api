<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Recurring;

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
