<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\LetterCommentActionKey;
use Justpilot\Billomat\Model\LetterComment;
use RuntimeException;

/**
 * API-Wrapper für Letter-Comments.
 */
final class LetterCommentsApi extends AbstractApi
{
    /**
     * @param list<LetterCommentActionKey>|null $actionKeys
     *
     * @return list<LetterComment>
     */
    public function listByLetter(int $letterId, ?array $actionKeys = null): array
    {
        $query = ['letter_id' => $letterId];

        if (null !== $actionKeys && [] !== $actionKeys) {
            $query['actionkey'] = implode(',', array_map(
                static fn (LetterCommentActionKey $a): string => $a->value,
                $actionKeys,
            ));
        }

        $data = $this->getJson('/letter-comments', $query);

        $root = $data['letter-comments'] ?? null;
        if (!\is_array($root)) {
            return [];
        }

        $rows = $root['letter-comment'] ?? [];

        if ([] === $rows || null === $rows) {
            return [];
        }

        if (isset($rows['id'])) {
            $rows = [$rows];
        }

        if (!\is_array($rows)) {
            return [];
        }

        /** @var list<LetterComment> $comments */
        $comments = array_map(
            LetterComment::fromArray(...),
            $rows,
        );

        return $comments;
    }

    public function get(int $id): ?LetterComment
    {
        $data = $this->getJsonOrNull("/letter-comments/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['letter-comment'] ?? null;
        if (!\is_array($row)) {
            return null;
        }

        return LetterComment::fromArray($row);
    }

    public function create(LetterCommentCreateOptions $options): LetterComment
    {
        $payload = ['letter-comment' => $options->toArray()];

        $data = $this->postJson('/letter-comments', $payload);

        $row = $data['letter-comment'] ?? null;
        if (!\is_array($row)) {
            throw new RuntimeException('Unexpected response from Billomat when creating letter comment.');
        }

        return LetterComment::fromArray($row);
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/letter-comments/{$id}");

        return true;
    }
}
