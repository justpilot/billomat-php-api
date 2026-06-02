<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\DeliveryNoteComment;
use Justpilot\Billomat\Model\Enum\DeliveryNoteCommentActionKey;
use RuntimeException;

/**
 * API-Wrapper für Delivery-Note-Comments.
 */
final class DeliveryNoteCommentsApi extends AbstractApi
{
    /**
     * @param list<DeliveryNoteCommentActionKey>|null $actionKeys
     *
     * @return list<DeliveryNoteComment>
     */
    public function listByDeliveryNote(int $deliveryNoteId, ?array $actionKeys = null): array
    {
        $query = ['delivery_note_id' => $deliveryNoteId];

        if (null !== $actionKeys && [] !== $actionKeys) {
            $query['actionkey'] = implode(',', array_map(
                static fn (DeliveryNoteCommentActionKey $a): string => $a->value,
                $actionKeys,
            ));
        }

        $data = $this->getJson('/delivery-note-comments', $query);

        $root = $data['delivery-note-comments'] ?? null;
        if (!\is_array($root)) {
            return [];
        }

        $rows = $root['delivery-note-comment'] ?? [];

        if ([] === $rows || null === $rows) {
            return [];
        }

        if (isset($rows['id'])) {
            $rows = [$rows];
        }

        if (!\is_array($rows)) {
            return [];
        }

        /** @var list<DeliveryNoteComment> $comments */
        $comments = array_map(
            DeliveryNoteComment::fromArray(...),
            $rows,
        );

        return $comments;
    }

    public function get(int $id): ?DeliveryNoteComment
    {
        $data = $this->getJsonOrNull("/delivery-note-comments/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['delivery-note-comment'] ?? null;
        if (!\is_array($row)) {
            return null;
        }

        return DeliveryNoteComment::fromArray($row);
    }

    public function create(DeliveryNoteCommentCreateOptions $options): DeliveryNoteComment
    {
        $payload = ['delivery-note-comment' => $options->toArray()];

        $data = $this->postJson('/delivery-note-comments', $payload);

        $row = $data['delivery-note-comment'] ?? null;
        if (!\is_array($row)) {
            throw new RuntimeException('Unexpected response from Billomat when creating delivery note comment.');
        }

        return DeliveryNoteComment::fromArray($row);
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/delivery-note-comments/{$id}");

        return true;
    }
}
