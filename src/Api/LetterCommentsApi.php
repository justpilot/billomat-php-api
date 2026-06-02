<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\LetterCommentActionKey;
use Justpilot\Billomat\Model\LetterComment;

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

        return $this->listResource('/letter-comments', 'letter-comments', 'letter-comment', LetterComment::fromArray(...), $query);
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

        return LetterComment::fromArray($this->unwrapEnvelope($data, 'letter-comment', 'creating letter comment'));
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/letter-comments/{$id}");

        return true;
    }
}
