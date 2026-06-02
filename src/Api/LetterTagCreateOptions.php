<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Payload für POST /letter-tags.
 */
final class LetterTagCreateOptions
{
    public function __construct(
        public int $letterId,
        public string $name,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'letter_id' => $this->letterId,
            'name' => $this->name,
        ];
    }
}
