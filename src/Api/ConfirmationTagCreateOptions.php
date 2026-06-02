<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Payload für POST /confirmation-tags.
 *
 * Doku: https://www.billomat.com/en/api/confirmations/tags/
 */
final class ConfirmationTagCreateOptions
{
    public function __construct(
        public int $confirmationId,
        public string $name,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'confirmation_id' => $this->confirmationId,
            'name' => $this->name,
        ];
    }
}
