<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\LetterTag;
use Justpilot\Billomat\Model\LetterTagCloudEntry;
use RuntimeException;

/**
 * API-Wrapper für Letter-Tags.
 */
final class LetterTagsApi extends AbstractApi
{
    /**
     * @return list<LetterTag>
     */
    public function listByLetter(int $letterId): array
    {
        $data = $this->getJson('/letter-tags', ['letter_id' => $letterId]);

        $root = $data['letter-tags'] ?? null;
        if (!\is_array($root)) {
            return [];
        }

        $rows = $root['letter-tag'] ?? [];
        if ([] === $rows || null === $rows) {
            return [];
        }

        if (isset($rows['id'])) {
            $rows = [$rows];
        }

        if (!\is_array($rows)) {
            return [];
        }

        /** @var list<LetterTag> $tags */
        $tags = array_map(LetterTag::fromArray(...), $rows);

        return $tags;
    }

    /**
     * @return list<LetterTagCloudEntry>
     */
    public function cloud(): array
    {
        $data = $this->getJson('/letter-tags');

        $root = $data['letter-tags'] ?? null;
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

        /** @var list<LetterTagCloudEntry> $tags */
        $tags = array_map(LetterTagCloudEntry::fromArray(...), $rows);

        return $tags;
    }

    public function get(int $id): ?LetterTag
    {
        $data = $this->getJsonOrNull("/letter-tags/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['letter-tag'] ?? null;
        if (!\is_array($row)) {
            return null;
        }

        return LetterTag::fromArray($row);
    }

    public function create(LetterTagCreateOptions $options): LetterTag
    {
        $payload = ['letter-tag' => $options->toArray()];

        $data = $this->postJson('/letter-tags', $payload);

        $row = $data['letter-tag'] ?? null;
        if (!\is_array($row)) {
            throw new RuntimeException('Unexpected response from Billomat when creating letter tag.');
        }

        return LetterTag::fromArray($row);
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/letter-tags/{$id}");

        return true;
    }
}
