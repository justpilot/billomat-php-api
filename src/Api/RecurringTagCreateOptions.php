<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Payload für POST /recurring-tags.
 *
 * Doku: https://www.billomat.com/api/abo-rechnungen/schlagworte/
 */
final class RecurringTagCreateOptions
{
    public function __construct(
        public int $recurringId,
        public string $name,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'recurring_id' => $this->recurringId,
            'name' => $this->name,
        ];
    }
}
