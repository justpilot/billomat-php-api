<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\IncomingTag;
use Justpilot\Billomat\Model\IncomingTagCloudEntry;

/**
 * API-Wrapper für Incoming-Tags.
 */
final class IncomingTagsApi extends AbstractApi
{
    /**
     * @return list<IncomingTag>
     */
    public function listByIncoming(int $incomingId): array
    {
        return $this->listResource('/incoming-tags', 'incoming-tags', 'incoming-tag', IncomingTag::fromArray(...), ['incoming_id' => $incomingId]);
    }

    /**
     * @return list<IncomingTagCloudEntry>
     */
    public function cloud(): array
    {
        return $this->listResource('/incoming-tags', 'incoming-tags', 'tag', IncomingTagCloudEntry::fromArray(...));
    }

    public function get(int $id): ?IncomingTag
    {
        $data = $this->getJsonOrNull("/incoming-tags/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['incoming-tag'] ?? null;
        if (!\is_array($row)) {
            return null;
        }

        return IncomingTag::fromArray($row);
    }

    public function create(IncomingTagCreateOptions $options): IncomingTag
    {
        $payload = ['incoming-tag' => $options->toArray()];

        $data = $this->postJson('/incoming-tags', $payload);

        return IncomingTag::fromArray($this->unwrapEnvelope($data, 'incoming-tag', 'creating incoming tag'));
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/incoming-tags/{$id}");

        return true;
    }
}
