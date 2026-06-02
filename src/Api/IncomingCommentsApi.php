<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\IncomingCommentActionKey;
use Justpilot\Billomat\Model\IncomingComment;

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

        return $this->listResource('/incoming-comments', 'incoming-comments', 'incoming-comment', IncomingComment::fromArray(...), $query);
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

        return IncomingComment::fromArray($this->unwrapEnvelope($data, 'incoming-comment', 'creating incoming comment'));
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/incoming-comments/{$id}");

        return true;
    }
}
