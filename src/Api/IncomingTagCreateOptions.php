<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Payload für POST /incoming-tags.
 */
final class IncomingTagCreateOptions
{
    public function __construct(
        public int $incomingId,
        public string $name,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'incoming_id' => $this->incomingId,
            'name' => $this->name,
        ];
    }
}
