<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\ReminderItem;

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

        return $this->listResource('/reminder-items', 'reminder-items', 'reminder-item', ReminderItem::fromArray(...), $params);
    }

    public function get(int $id): ?ReminderItem
    {
        $data = $this->getJsonOrNull("/reminder-items/{$id}");

        if (null === $data) {
            return null;
        }

        return ReminderItem::fromArray($this->unwrapEnvelope($data, 'reminder-item', 'fetching reminder item'));
    }
}
