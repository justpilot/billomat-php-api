<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\DeliveryNoteComment;
use Justpilot\Billomat\Model\Enum\DeliveryNoteCommentActionKey;

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

        return $this->listResource('/delivery-note-comments', 'delivery-note-comments', 'delivery-note-comment', DeliveryNoteComment::fromArray(...), $query);
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

        return DeliveryNoteComment::fromArray($this->unwrapEnvelope($data, 'delivery-note-comment', 'creating delivery note comment'));
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/delivery-note-comments/{$id}");

        return true;
    }
}
