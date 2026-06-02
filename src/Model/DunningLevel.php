<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Internal\ScalarCaster;

/**
 * Mahnstufe (Dunning Level).
 *
 * Doku: https://www.billomat.com/en/api/settings/dunning-levels/
 */
final readonly class DunningLevel
{
    public function __construct(
        public ?int $id,
        public ?string $name,
        public ?int $position = null,
        public ?int $dueDays = null,
        public ?float $charge = null,
        public ?float $interest = null,
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ScalarCaster::toIntOrNull($data['id'] ?? null),
            name: ScalarCaster::toStringOrNull($data['name'] ?? null),
            position: ScalarCaster::toIntOrNull($data['position'] ?? null),
            dueDays: ScalarCaster::toIntOrNull($data['due_days'] ?? null),
            charge: ScalarCaster::toFloatOrNull($data['charge'] ?? null),
            interest: ScalarCaster::toFloatOrNull($data['interest'] ?? null),
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
            'position' => $this->position,
            'due_days' => $this->dueDays,
            'charge' => $this->charge,
            'interest' => $this->interest,
        ];
    }
}
