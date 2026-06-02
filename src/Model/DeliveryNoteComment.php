<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\DeliveryNoteCommentActionKey;

use const DATE_ATOM;

/**
 * Kommentar zu einem Lieferschein.
 *
 * Doku: https://www.billomat.com/en/api/delivery-notes/comments/
 */
final readonly class DeliveryNoteComment
{
    public function __construct(
        public ?int $id,
        public int $deliveryNoteId,
        public ?string $comment = null,
        public ?DateTimeImmutable $created = null,
        public ?int $userId = null,
        public ?DeliveryNoteCommentActionKey $actionkey = null,
        public ?string $actionkeyRaw = null,
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $actionkeyRaw = ScalarCaster::toStringOrNull($data['actionkey'] ?? null);

        return new self(
            id: ScalarCaster::toIntOrNull($data['id'] ?? null),
            deliveryNoteId: (int) ($data['delivery_note_id'] ?? 0),
            comment: ScalarCaster::toStringOrNull($data['comment'] ?? null),
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            userId: ScalarCaster::toIntOrNull($data['user_id'] ?? null),
            actionkey: null !== $actionkeyRaw
                ? DeliveryNoteCommentActionKey::tryFrom($actionkeyRaw)
                : null,
            actionkeyRaw: $actionkeyRaw,
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'created' => $this->created?->format(DATE_ATOM),
            'delivery_note_id' => $this->deliveryNoteId,
            'user_id' => $this->userId,
            'comment' => $this->comment,
            'actionkey' => $this->actionkeyRaw,
        ];
    }
}
