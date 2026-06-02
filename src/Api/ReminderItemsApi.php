<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\ReminderItem;
use RuntimeException;

/**
 * API-Wrapper für Reminder-Items (read-only — Mahnungspositionen werden vom System generiert).
 *
 * Endpoints:
 *  - GET    /reminder-items?reminder_id={id}
 *  - GET    /reminder-items/{id}
 *
 * Doku: https://www.billomat.com/en/api/reminders/items/
 */
final class ReminderItemsApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array|null> $query
     *
     * @return list<ReminderItem>
     */
    public function listByReminder(int $reminderId, array $query = []): array
    {
        $params = array_merge(['reminder_id' => $reminderId], $query);

        $data = $this->getJson('/reminder-items', $params);

        $itemsData = $data['reminder-items']['reminder-item'] ?? [];

        if (isset($itemsData['id'])) {
            $itemsData = [$itemsData];
        }

        if (!\is_array($itemsData) || [] === $itemsData) {
            return [];
        }

        /** @var list<ReminderItem> $items */
        $items = array_map(
            ReminderItem::fromArray(...),
            $itemsData,
        );

        return $items;
    }

    public function get(int $id): ?ReminderItem
    {
        $data = $this->getJsonOrNull("/reminder-items/{$id}");

        if (null === $data) {
            return null;
        }

        $itemData = $data['reminder-item'] ?? null;

        if (!\is_array($itemData)) {
            throw new RuntimeException('Unexpected response from Billomat when fetching reminder item.');
        }

        return ReminderItem::fromArray($itemData);
    }
}
