<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\RecurringItem;

/**
 * API-Wrapper für die Positionen einer Abo-Rechnung.
 *
 * Endpoints:
 *  - GET    /recurring-items?recurring_id={id}
 *  - GET    /recurring-items/{id}
 *  - POST   /recurring-items
 *  - PUT    /recurring-items/{id}
 *  - DELETE /recurring-items/{id}
 *
 * Doku: https://www.billomat.com/api/abo-rechnungen/positionen/
 */
final class RecurringItemsApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array|null> $query
     *
     * @return list<RecurringItem>
     */
    public function listByRecurring(int $recurringId, array $query = []): array
    {
        $params = array_merge(['recurring_id' => $recurringId], $query);

        return $this->listResource('/recurring-items', 'recurring-items', 'recurring-item', RecurringItem::fromArray(...), $params);
    }

    public function get(int $id): ?RecurringItem
    {
        $data = $this->getJsonOrNull("/recurring-items/{$id}");

        if (null === $data) {
            return null;
        }

        return RecurringItem::fromArray($this->unwrapEnvelope($data, 'recurring-item', 'fetching recurring item'));
    }

    public function create(int $recurringId, RecurringItemCreateOptions $options): RecurringItem
    {
        $body = $options->toArray();
        $body['recurring_id'] = $recurringId;

        $payload = ['recurring-item' => $body];

        $data = $this->postJson('/recurring-items', $payload);

        return RecurringItem::fromArray($this->unwrapEnvelope($data, 'recurring-item', 'creating recurring item'));
    }

    public function update(int $id, RecurringItemCreateOptions $options): RecurringItem
    {
        $payload = ['recurring-item' => $options->toArray()];

        $data = $this->putJson("/recurring-items/{$id}", $payload);

        return RecurringItem::fromArray($this->unwrapEnvelope($data, 'recurring-item', 'updating recurring item'));
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/recurring-items/{$id}");

        return true;
    }
}
