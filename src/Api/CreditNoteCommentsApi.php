<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\CreditNoteComment;
use Justpilot\Billomat\Model\Enum\CreditNoteCommentActionKey;
use RuntimeException;

/**
 * API-Wrapper für Credit-Note-Comments.
 */
final class CreditNoteCommentsApi extends AbstractApi
{
    /**
     * @param list<CreditNoteCommentActionKey>|null $actionKeys
     *
     * @return list<CreditNoteComment>
     */
    public function listByCreditNote(int $creditNoteId, ?array $actionKeys = null): array
    {
        $query = ['credit_note_id' => $creditNoteId];

        if (null !== $actionKeys && [] !== $actionKeys) {
            $query['actionkey'] = implode(',', array_map(
                static fn (CreditNoteCommentActionKey $a): string => $a->value,
                $actionKeys,
            ));
        }

        $data = $this->getJson('/credit-note-comments', $query);

        $root = $data['credit-note-comments'] ?? null;
        if (!\is_array($root)) {
            return [];
        }

        $rows = $root['credit-note-comment'] ?? [];

        if ([] === $rows || null === $rows) {
            return [];
        }

        if (isset($rows['id'])) {
            $rows = [$rows];
        }

        if (!\is_array($rows)) {
            return [];
        }

        /** @var list<CreditNoteComment> $comments */
        $comments = array_map(
            CreditNoteComment::fromArray(...),
            $rows,
        );

        return $comments;
    }

    public function get(int $id): ?CreditNoteComment
    {
        $data = $this->getJsonOrNull("/credit-note-comments/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['credit-note-comment'] ?? null;
        if (!\is_array($row)) {
            return null;
        }

        return CreditNoteComment::fromArray($row);
    }

    public function create(CreditNoteCommentCreateOptions $options): CreditNoteComment
    {
        $payload = ['credit-note-comment' => $options->toArray()];

        $data = $this->postJson('/credit-note-comments', $payload);

        $row = $data['credit-note-comment'] ?? null;
        if (!\is_array($row)) {
            throw new RuntimeException('Unexpected response from Billomat when creating credit note comment.');
        }

        return CreditNoteComment::fromArray($row);
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/credit-note-comments/{$id}");

        return true;
    }
}
