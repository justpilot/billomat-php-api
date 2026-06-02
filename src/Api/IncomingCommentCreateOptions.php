<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\IncomingCommentActionKey;

/**
 * Payload für POST /incoming-comments.
 */
final class IncomingCommentCreateOptions
{
    public ?IncomingCommentActionKey $actionkey = null;

    public function __construct(
        public int $incomingId,
        public string $comment,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [
            'incoming_id' => $this->incomingId,
            'comment' => $this->comment,
            'actionkey' => $this->actionkey?->value,
        ];

        return array_filter($data, static fn (mixed $v): bool => null !== $v);
    }
}
