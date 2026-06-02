<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\LetterCommentActionKey;

/**
 * Payload für POST /letter-comments.
 */
final class LetterCommentCreateOptions
{
    public ?LetterCommentActionKey $actionkey = null;

    public function __construct(
        public int $letterId,
        public string $comment,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [
            'letter_id' => $this->letterId,
            'comment' => $this->comment,
            'actionkey' => $this->actionkey?->value,
        ];

        return array_filter($data, static fn (mixed $v): bool => null !== $v);
    }
}
