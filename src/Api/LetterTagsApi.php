<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\LetterTag;
use Justpilot\Billomat\Model\LetterTagCloudEntry;

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
        return $this->listResource('/letter-tags', 'letter-tags', 'letter-tag', LetterTag::fromArray(...), ['letter_id' => $letterId]);
    }

    /**
     * @return list<LetterTagCloudEntry>
     */
    public function cloud(): array
    {
        return $this->listResource('/letter-tags', 'letter-tags', 'tag', LetterTagCloudEntry::fromArray(...));
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

        return LetterTag::fromArray($this->unwrapEnvelope($data, 'letter-tag', 'creating letter tag'));
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/letter-tags/{$id}");

        return true;
    }
}
