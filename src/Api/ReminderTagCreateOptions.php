<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Payload für POST /reminder-tags.
 */
final class ReminderTagCreateOptions
{
    public function __construct(
        public int $reminderId,
        public string $name,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'reminder_id' => $this->reminderId,
            'name' => $this->name,
        ];
    }
}
