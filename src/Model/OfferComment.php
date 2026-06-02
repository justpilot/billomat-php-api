<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\OfferCommentActionKey;

use const DATE_ATOM;

/**
 * Kommentar zu einem Angebot.
 *
 * Doku: https://www.billomat.com/en/api/estimates/comments/
 */
final readonly class OfferComment
{
    public function __construct(
        public ?int $id,
        public int $offerId,
        public ?string $comment = null,
        public ?DateTimeImmutable $created = null,
        public ?int $userId = null,
        public ?OfferCommentActionKey $actionkey = null,
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
            offerId: (int) ($data['offer_id'] ?? 0),
            comment: ScalarCaster::toStringOrNull($data['comment'] ?? null),
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            userId: ScalarCaster::toIntOrNull($data['user_id'] ?? null),
            actionkey: null !== $actionkeyRaw
                ? OfferCommentActionKey::tryFrom($actionkeyRaw)
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
            'offer_id' => $this->offerId,
            'user_id' => $this->userId,
            'comment' => $this->comment,
            'actionkey' => $this->actionkeyRaw,
        ];
    }
}
