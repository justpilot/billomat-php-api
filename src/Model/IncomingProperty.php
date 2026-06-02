<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Model\Enum\PropertyType;

/**
 * Definition einer Eigenschaft für Eingangsrechnungen.
 */
final readonly class IncomingProperty
{
    public function __construct(
        public ?int $id,
        public string $name,
        public ?PropertyType $type,
        public ?string $defaultValue = null,
        public ?int $position = null,
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            name: (string) ($data['name'] ?? ''),
            type: PropertyType::fromApi($data['type'] ?? null),
            defaultValue: $data['default_value'] ?? null,
            position: isset($data['position']) ? (int) $data['position'] : null,
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type?->value,
            'default_value' => $this->defaultValue,
            'position' => $this->position,
        ];
    }
}
