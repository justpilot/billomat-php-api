<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\ConfirmationCommentActionKey;

use const DATE_ATOM;

/**
 * Kommentar zu einer Auftragsbestätigung.
 *
 * Doku: https://www.billomat.com/en/api/confirmations/comments/
 */
final readonly class ConfirmationComment
{
    public function __construct(
        public ?int $id,
        public int $confirmationId,
        public ?string $comment = null,
        public ?DateTimeImmutable $created = null,
        public ?int $userId = null,
        public ?ConfirmationCommentActionKey $actionkey = null,
        public ?string $actionkeyRaw = null,
        public ?string $customfield = null,
        public ?bool $public = null,
        public ?bool $byClient = null,
        public ?int $emailId = null,
        public ?int $clientId = null,
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
            confirmationId: (int) ($data['confirmation_id'] ?? 0),
            comment: ScalarCaster::toStringOrNull($data['comment'] ?? null),
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            userId: ScalarCaster::toIntOrNull($data['user_id'] ?? null),
            actionkey: null !== $actionkeyRaw
                ? ConfirmationCommentActionKey::tryFrom($actionkeyRaw)
                : null,
            actionkeyRaw: $actionkeyRaw,
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
            public: ScalarCaster::toBoolOrNull($data['public'] ?? null),
            byClient: ScalarCaster::toBoolOrNull($data['by_client'] ?? null),
            emailId: ScalarCaster::toIntOrNull($data['email_id'] ?? null),
            clientId: ScalarCaster::toIntOrNull($data['client_id'] ?? null),
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
            'confirmation_id' => $this->confirmationId,
            'user_id' => $this->userId,
            'comment' => $this->comment,
            'actionkey' => $this->actionkeyRaw,
            'customfield' => $this->customfield,
            'public' => $this->public,
            'by_client' => $this->byClient,
            'email_id' => $this->emailId,
            'client_id' => $this->clientId,
        ];
    }
}
