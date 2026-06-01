<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\RecurringItem;
use RuntimeException;

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

        $data = $this->getJson('/recurring-items', $params);

        $rows = $data['recurring-items']['recurring-item'] ?? [];

        if (isset($rows['id'])) {
            $rows = [$rows];
        }

        if (!\is_array($rows) || [] === $rows) {
            return [];
        }

        /** @var list<RecurringItem> $items */
        $items = array_map(RecurringItem::fromArray(...), $rows);

        return $items;
    }

    public function get(int $id): ?RecurringItem
    {
        $data = $this->getJsonOrNull("/recurring-items/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['recurring-item'] ?? null;
        if (!\is_array($row)) {
            throw new RuntimeException('Unexpected response from Billomat when fetching recurring item.');
        }

        return RecurringItem::fromArray($row);
    }

    public function create(int $recurringId, RecurringItemCreateOptions $options): RecurringItem
    {
        $body = $options->toArray();
        $body['recurring_id'] = $recurringId;

        $payload = ['recurring-item' => $body];

        $data = $this->postJson('/recurring-items', $payload);

        $row = $data['recurring-item'] ?? null;
        if (!\is_array($row)) {
            throw new RuntimeException('Unexpected response from Billomat when creating recurring item.');
        }

        return RecurringItem::fromArray($row);
    }

    public function update(int $id, RecurringItemCreateOptions $options): RecurringItem
    {
        $payload = ['recurring-item' => $options->toArray()];

        $data = $this->putJson("/recurring-items/{$id}", $payload);

        $row = $data['recurring-item'] ?? null;
        if (!\is_array($row)) {
            throw new RuntimeException('Unexpected response from Billomat when updating recurring item.');
        }

        return RecurringItem::fromArray($row);
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/recurring-items/{$id}");

        return true;
    }
}
