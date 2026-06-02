<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\IncomingCommentActionKey;
use Justpilot\Billomat\Model\IncomingComment;
use RuntimeException;

/**
 * API-Wrapper für Incoming-Comments.
 */
final class IncomingCommentsApi extends AbstractApi
{
    /**
     * @param list<IncomingCommentActionKey>|null $actionKeys
     *
     * @return list<IncomingComment>
     */
    public function listByIncoming(int $incomingId, ?array $actionKeys = null): array
    {
        $query = ['incoming_id' => $incomingId];

        if (null !== $actionKeys && [] !== $actionKeys) {
            $query['actionkey'] = implode(',', array_map(
                static fn (IncomingCommentActionKey $a): string => $a->value,
                $actionKeys,
            ));
        }

        $data = $this->getJson('/incoming-comments', $query);

        $root = $data['incoming-comments'] ?? null;
        if (!\is_array($root)) {
            return [];
        }

        $rows = $root['incoming-comment'] ?? [];

        if ([] === $rows || null === $rows) {
            return [];
        }

        if (isset($rows['id'])) {
            $rows = [$rows];
        }

        if (!\is_array($rows)) {
            return [];
        }

        /** @var list<IncomingComment> $comments */
        $comments = array_map(
            IncomingComment::fromArray(...),
            $rows,
        );

        return $comments;
    }

    public function get(int $id): ?IncomingComment
    {
        $data = $this->getJsonOrNull("/incoming-comments/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['incoming-comment'] ?? null;
        if (!\is_array($row)) {
            return null;
        }

        return IncomingComment::fromArray($row);
    }

    public function create(IncomingCommentCreateOptions $options): IncomingComment
    {
        $payload = ['incoming-comment' => $options->toArray()];

        $data = $this->postJson('/incoming-comments', $payload);

        $row = $data['incoming-comment'] ?? null;
        if (!\is_array($row)) {
            throw new RuntimeException('Unexpected response from Billomat when creating incoming comment.');
        }

        return IncomingComment::fromArray($row);
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/incoming-comments/{$id}");

        return true;
    }
}
