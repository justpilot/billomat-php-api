<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\ConfirmationComment;
use Justpilot\Billomat\Model\Enum\ConfirmationCommentActionKey;
use RuntimeException;

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

        $data = $this->getJson('/confirmation-comments', $query);

        $root = $data['confirmation-comments'] ?? null;
        if (!\is_array($root)) {
            return [];
        }

        $rows = $root['confirmation-comment'] ?? [];

        if ([] === $rows || null === $rows) {
            return [];
        }

        if (isset($rows['id'])) {
            $rows = [$rows];
        }

        if (!\is_array($rows)) {
            return [];
        }

        /** @var list<ConfirmationComment> $comments */
        $comments = array_map(
            ConfirmationComment::fromArray(...),
            $rows,
        );

        return $comments;
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

        $row = $data['confirmation-comment'] ?? null;
        if (!\is_array($row)) {
            throw new RuntimeException('Unexpected response from Billomat when creating confirmation comment.');
        }

        return ConfirmationComment::fromArray($row);
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/confirmation-comments/{$id}");

        return true;
    }
}
