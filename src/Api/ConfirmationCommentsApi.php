<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\ConfirmationComment;
use Justpilot\Billomat\Model\Enum\ConfirmationCommentActionKey;

/**
 * API-Wrapper für Confirmation-Comments.
 *
 * Doku: https://www.billomat.com/en/api/confirmations/comments/
 */
final class ConfirmationCommentsApi extends AbstractApi
{
    /**
     * @param list<ConfirmationCommentActionKey>|null $actionKeys
     *
     * @return list<ConfirmationComment>
     */
    public function listByConfirmation(int $confirmationId, ?array $actionKeys = null): array
    {
        $query = ['confirmation_id' => $confirmationId];

        if (null !== $actionKeys && [] !== $actionKeys) {
            $query['actionkey'] = implode(',', array_map(
                static fn (ConfirmationCommentActionKey $a): string => $a->value,
                $actionKeys,
            ));
        }

        return $this->listResource('/confirmation-comments', 'confirmation-comments', 'confirmation-comment', ConfirmationComment::fromArray(...), $query);
    }

    public function get(int $id): ?ConfirmationComment
    {
        $data = $this->getJsonOrNull("/confirmation-comments/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['confirmation-comment'] ?? null;
        if (!\is_array($row)) {
            return null;
        }

        return ConfirmationComment::fromArray($row);
    }

    public function create(ConfirmationCommentCreateOptions $options): ConfirmationComment
    {
        $payload = ['confirmation-comment' => $options->toArray()];

        $data = $this->postJson('/confirmation-comments', $payload);

        return ConfirmationComment::fromArray($this->unwrapEnvelope($data, 'confirmation-comment', 'creating confirmation comment'));
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/confirmation-comments/{$id}");

        return true;
    }
}
