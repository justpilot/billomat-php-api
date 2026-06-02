<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\ReminderTag;
use Justpilot\Billomat\Model\ReminderTagCloudEntry;
use RuntimeException;

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
        $data = $this->getJson('/reminder-tags', ['reminder_id' => $reminderId]);

        $root = $data['reminder-tags'] ?? null;
        if (!\is_array($root)) {
            return [];
        }

        $rows = $root['reminder-tag'] ?? [];
        if ([] === $rows || null === $rows) {
            return [];
        }

        if (isset($rows['id'])) {
            $rows = [$rows];
        }

        if (!\is_array($rows)) {
            return [];
        }

        /** @var list<ReminderTag> $tags */
        $tags = array_map(ReminderTag::fromArray(...), $rows);

        return $tags;
    }

    /**
     * @return list<ReminderTagCloudEntry>
     */
    public function cloud(): array
    {
        $data = $this->getJson('/reminder-tags');

        $root = $data['reminder-tags'] ?? null;
        if (!\is_array($root)) {
            return [];
        }

        $rows = $root['tag'] ?? [];
        if ([] === $rows || null === $rows) {
            return [];
        }

        if (isset($rows['name'])) {
            $rows = [$rows];
        }

        if (!\is_array($rows)) {
            return [];
        }

        /** @var list<ReminderTagCloudEntry> $tags */
        $tags = array_map(ReminderTagCloudEntry::fromArray(...), $rows);

        return $tags;
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

        $row = $data['reminder-tag'] ?? null;
        if (!\is_array($row)) {
            throw new RuntimeException('Unexpected response from Billomat when creating reminder tag.');
        }

        return ReminderTag::fromArray($row);
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/reminder-tags/{$id}");

        return true;
    }
}
