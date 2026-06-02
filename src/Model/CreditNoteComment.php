<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\CreditNoteCommentActionKey;

use const DATE_ATOM;

/**
 * Kommentar zu einer Gutschrift.
 */
final readonly class CreditNoteComment
{
    public function __construct(
        public ?int $id,
        public int $creditNoteId,
        public ?string $comment = null,
        public ?DateTimeImmutable $created = null,
        public ?int $userId = null,
        public ?CreditNoteCommentActionKey $actionkey = null,
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
            creditNoteId: (int) ($data['credit_note_id'] ?? 0),
            comment: ScalarCaster::toStringOrNull($data['comment'] ?? null),
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            userId: ScalarCaster::toIntOrNull($data['user_id'] ?? null),
            actionkey: null !== $actionkeyRaw
                ? CreditNoteCommentActionKey::tryFrom($actionkeyRaw)
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
            'credit_note_id' => $this->creditNoteId,
            'user_id' => $this->userId,
            'comment' => $this->comment,
            'actionkey' => $this->actionkeyRaw,
        ];
    }
}
