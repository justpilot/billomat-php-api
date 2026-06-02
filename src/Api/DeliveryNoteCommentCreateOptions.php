<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\DeliveryNoteCommentActionKey;

/**
 * Payload für POST /delivery-note-comments.
 */
final class DeliveryNoteCommentCreateOptions
{
    public ?DeliveryNoteCommentActionKey $actionkey = null;

    public function __construct(
        public int $deliveryNoteId,
        public string $comment,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [
            'delivery_note_id' => $this->deliveryNoteId,
            'comment' => $this->comment,
            'actionkey' => $this->actionkey?->value,
        ];

        return array_filter($data, static fn (mixed $v): bool => null !== $v);
    }
}
