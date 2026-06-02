<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\ReminderText;

/**
 * Read-only API-Wrapper für Mahn-Textbausteine.
 *
 * Doku: https://www.billomat.com/en/api/settings/reminder-texts/
 */
final class ReminderTextsApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array|null> $filters
     *
     * @return list<ReminderText>
     */
    public function list(array $filters = []): array
    {
        $data = $this->getJson('/reminder-texts', $filters);

        $node = $data['reminder-texts']['reminder-text'] ?? [];

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

        /** @var list<ReminderText> $models */
        $models = array_map(ReminderText::fromArray(...), $rows);

        return $models;
    }

    public function get(int $id): ?ReminderText
    {
        $data = $this->getJsonOrNull("/reminder-texts/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['reminder-text'] ?? null;
        if (!\is_array($row)) {
            return null;
        }

        return ReminderText::fromArray($row);
    }
}
