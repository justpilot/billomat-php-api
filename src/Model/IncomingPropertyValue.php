<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Internal\ScalarCaster;

/**
 * Wert einer Eigenschaft einer Eingangsrechnung.
 */
final readonly class IncomingPropertyValue
{
    public function __construct(
        public ?int $id,
        public int $incomingId,
        public int $incomingPropertyId,
        public ?string $type,
        public ?string $name,
        public mixed $value,
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ScalarCaster::toIntOrNull($data['id'] ?? null),
            incomingId: (int) ($data['incoming_id'] ?? 0),
            incomingPropertyId: (int) ($data['incoming_property_id'] ?? 0),
            type: ScalarCaster::toStringOrNull($data['type'] ?? null),
            name: ScalarCaster::toStringOrNull($data['name'] ?? null),
            value: $data['value'] ?? null,
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'incoming_id' => $this->incomingId,
            'incoming_property_id' => $this->incomingPropertyId,
            'type' => $this->type,
            'name' => $this->name,
            'value' => $this->value,
        ];
    }
}
