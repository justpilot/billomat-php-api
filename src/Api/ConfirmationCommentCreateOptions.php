<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\ConfirmationCommentActionKey;

/**
 * Payload für POST /confirmation-comments.
 *
 * Doku: https://www.billomat.com/en/api/confirmations/comments/
 */
final class ConfirmationCommentCreateOptions
{
    public ?ConfirmationCommentActionKey $actionkey = null;

    public function __construct(
        public int $confirmationId,
        public string $comment,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [
            'confirmation_id' => $this->confirmationId,
            'comment' => $this->comment,
            'actionkey' => $this->actionkey?->value,
        ];

        return array_filter($data, static fn (mixed $v): bool => null !== $v);
    }
}
