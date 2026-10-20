<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\PropertyType;

/**
 * Definition einer Lieferanten-Eigenschaft.
 */
final readonly class SupplierProperty
{
    public function __construct(
        public ?int $id,
        public string $name,
        public ?PropertyType $type,
        public ?string $defaultValue = null,
        /**
         * @deprecated since 3.1 – Billomat liefert `position` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.
         */
        public ?int $position = null,
        public ?bool $isNvl = null,
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
            name: (string) ($data['name'] ?? ''),
            type: PropertyType::fromApi($data['type'] ?? null),
            defaultValue: ScalarCaster::toStringOrNull($data['default_value'] ?? null),
            position: ScalarCaster::toIntOrNull($data['position'] ?? null),
            isNvl: ScalarCaster::toBoolOrNull($data['is_nvl'] ?? null),
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
            'name' => $this->name,
            'type' => $this->type?->value,
            'default_value' => $this->defaultValue,
            'position' => $this->position,
            'is_nvl' => $this->isNvl,
            'customfield' => $this->customfield,
        ];
    }
}
