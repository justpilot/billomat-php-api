<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\ReminderTag;
use Justpilot\Billomat\Model\ReminderTagCloudEntry;

/**
 * API-Wrapper für Reminder-Tags.
 */
final class ReminderTagsApi extends AbstractApi
{
    /**
     * @return list<ReminderTag>
     */
    public function listByReminder(int $reminderId): array
    {
        return $this->listResource('/reminder-tags', 'reminder-tags', 'reminder-tag', ReminderTag::fromArray(...), ['reminder_id' => $reminderId]);
    }

    /**
     * @return list<ReminderTagCloudEntry>
     */
    public function cloud(): array
    {
        return $this->listResource('/reminder-tags', 'reminder-tags', 'tag', ReminderTagCloudEntry::fromArray(...));
    }

    public function get(int $id): ?ReminderTag
    {
        $data = $this->getJsonOrNull("/reminder-tags/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['reminder-tag'] ?? null;
        if (!\is_array($row)) {
            return null;
        }

        return ReminderTag::fromArray($row);
    }

    public function create(ReminderTagCreateOptions $options): ReminderTag
    {
        $payload = ['reminder-tag' => $options->toArray()];

        $data = $this->postJson('/reminder-tags', $payload);

        return ReminderTag::fromArray($this->unwrapEnvelope($data, 'reminder-tag', 'creating reminder tag'));
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/reminder-tags/{$id}");

        return true;
    }
}
