<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\RecurringTag;
use Justpilot\Billomat\Model\RecurringTagCloudEntry;

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
        return $this->listResource('/recurring-tags', 'recurring-tags', 'recurring-tag', RecurringTag::fromArray(...), ['recurring_id' => $recurringId]);
    }

    /**
     * Aggregierte Tag-Cloud.
     *
     * @return list<RecurringTagCloudEntry>
     */
    public function cloud(): array
    {
        return $this->listResource('/recurring-tags', 'recurring-tags', 'tag', RecurringTagCloudEntry::fromArray(...));
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

        return RecurringTag::fromArray($this->unwrapEnvelope($data, 'recurring-tag', 'creating recurring tag'));
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/recurring-tags/{$id}");

        return true;
    }
}
