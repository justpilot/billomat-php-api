<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Internal\ScalarCaster;

/**
 * Schlagwort/Tag an einem Lieferschein.
 */
final readonly class DeliveryNoteTag
{
    public function __construct(
        public ?int $id,
        public int $deliveryNoteId,
        public string $name,
        public ?string $customfield = null,
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ScalarCaster::toIntOrNull($data['id'] ?? null),
            deliveryNoteId: (int) ($data['delivery_note_id'] ?? 0),
            name: (string) ($data['name'] ?? ''),
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'delivery_note_id' => $this->deliveryNoteId,
            'name' => $this->name,
            'customfield' => $this->customfield,
        ];
    }
}
