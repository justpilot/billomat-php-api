<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\IncomingTag;
use Justpilot\Billomat\Model\IncomingTagCloudEntry;
use RuntimeException;

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
        $data = $this->getJson('/incoming-tags', ['incoming_id' => $incomingId]);

        $root = $data['incoming-tags'] ?? null;
        if (!\is_array($root)) {
            return [];
        }

        $rows = $root['incoming-tag'] ?? [];
        if ([] === $rows || null === $rows) {
            return [];
        }

        if (isset($rows['id'])) {
            $rows = [$rows];
        }

        if (!\is_array($rows)) {
            return [];
        }

        /** @var list<IncomingTag> $tags */
        $tags = array_map(IncomingTag::fromArray(...), $rows);

        return $tags;
    }

    /**
     * @return list<IncomingTagCloudEntry>
     */
    public function cloud(): array
    {
        $data = $this->getJson('/incoming-tags');

        $root = $data['incoming-tags'] ?? null;
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

        /** @var list<IncomingTagCloudEntry> $tags */
        $tags = array_map(IncomingTagCloudEntry::fromArray(...), $rows);

        return $tags;
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

        $row = $data['incoming-tag'] ?? null;
        if (!\is_array($row)) {
            throw new RuntimeException('Unexpected response from Billomat when creating incoming tag.');
        }

        return IncomingTag::fromArray($row);
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/incoming-tags/{$id}");

        return true;
    }
}
