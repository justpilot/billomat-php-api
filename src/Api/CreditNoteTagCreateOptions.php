<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Payload für POST /credit-note-tags.
 */
final class CreditNoteTagCreateOptions
{
    public function __construct(
        public int $creditNoteId,
        public string $name,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'credit_note_id' => $this->creditNoteId,
            'name' => $this->name,
        ];
    }
}
