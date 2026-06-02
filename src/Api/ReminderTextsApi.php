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
        return $this->listResource('/reminder-texts', 'reminder-texts', 'reminder-text', ReminderText::fromArray(...), $filters);
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
