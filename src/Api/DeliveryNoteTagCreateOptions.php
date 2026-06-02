<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Payload für POST /delivery-note-tags.
 */
final class DeliveryNoteTagCreateOptions
{
    public function __construct(
        public int $deliveryNoteId,
        public string $name,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'delivery_note_id' => $this->deliveryNoteId,
            'name' => $this->name,
        ];
    }
}
