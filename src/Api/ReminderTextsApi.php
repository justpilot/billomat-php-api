<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Exception\NotFoundException;
use Justpilot\Billomat\Model\ReminderText;
use NoDiscard;

/**
 * Read-only API-Wrapper für Mahn-Textbausteine.
 *
 * Doku: https://www.billomat.com/en/api/settings/reminder-texts/
 */
final class ReminderTextsApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return list<ReminderText>
     */
    #[NoDiscard]
    public function list(array $filters = []): array
    {
        return $this->listResource('/reminder-texts', 'reminder-texts', 'reminder-text', ReminderText::fromArray(...), $filters);
    }

    #[NoDiscard]
    public function get(int $id): ?ReminderText
    {
        $data = $this->getJsonOrNull("/reminder-texts/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['reminder-text'] ?? null;
        if (!self::isRecord($row)) {
            return null;
        }

        return ReminderText::fromArray($row);
    }

    /**
     * Wie {@see get()}, wirft aber eine {@see NotFoundException} statt null zurückzugeben.
     *
     * @throws NotFoundException wenn es den Datensatz nicht gibt (404)
     */
    #[NoDiscard]
    public function getOrFail(int $id): ReminderText
    {
        return $this->get($id) ?? throw $this->notFound("/reminder-texts/{$id}");
    }
}
