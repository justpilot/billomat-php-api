<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\RecurringTag;
use Justpilot\Billomat\Model\RecurringTagCloudEntry;
use RuntimeException;

/**
 * API-Wrapper für Schlagworte von Abo-Rechnungen.
 *
 * Endpoints:
 *  - GET    /recurring-tags?recurring_id={id}
 *  - GET    /recurring-tags
 *  - GET    /recurring-tags/{id}
 *  - POST   /recurring-tags
 *  - DELETE /recurring-tags/{id}
 *
 * Doku: https://www.billomat.com/api/abo-rechnungen/schlagworte/
 */
final class RecurringTagsApi extends AbstractApi
{
    /**
     * @return list<RecurringTag>
     */
    public function listByRecurring(int $recurringId): array
    {
        $data = $this->getJson('/recurring-tags', ['recurring_id' => $recurringId]);

        $root = $data['recurring-tags'] ?? null;
        if (!\is_array($root)) {
            return [];
        }

        $rows = $root['recurring-tag'] ?? [];
        if ([] === $rows || null === $rows) {
            return [];
        }

        if (isset($rows['id'])) {
            $rows = [$rows];
        }

        if (!\is_array($rows)) {
            return [];
        }

        /** @var list<RecurringTag> $tags */
        $tags = array_map(RecurringTag::fromArray(...), $rows);

        return $tags;
    }

    /**
     * Aggregierte Tag-Cloud.
     *
     * @return list<RecurringTagCloudEntry>
     */
    public function cloud(): array
    {
        $data = $this->getJson('/recurring-tags');

        $root = $data['recurring-tags'] ?? null;
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

        /** @var list<RecurringTagCloudEntry> $tags */
        $tags = array_map(RecurringTagCloudEntry::fromArray(...), $rows);

        return $tags;
    }

    public function get(int $id): ?RecurringTag
    {
        $data = $this->getJsonOrNull("/recurring-tags/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['recurring-tag'] ?? null;
        if (!\is_array($row)) {
            return null;
        }

        return RecurringTag::fromArray($row);
    }

    public function create(RecurringTagCreateOptions $options): RecurringTag
    {
        $payload = ['recurring-tag' => $options->toArray()];

        $data = $this->postJson('/recurring-tags', $payload);

        $row = $data['recurring-tag'] ?? null;
        if (!\is_array($row)) {
            throw new RuntimeException('Unexpected response from Billomat when creating recurring tag.');
        }

        return RecurringTag::fromArray($row);
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/recurring-tags/{$id}");

        return true;
    }
}
