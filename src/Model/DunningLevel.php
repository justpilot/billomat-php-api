<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

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
            id: isset($data['id']) ? (int) $data['id'] : null,
            name: $data['name'] ?? null,
            position: isset($data['position']) ? (int) $data['position'] : null,
            dueDays: isset($data['due_days']) ? (int) $data['due_days'] : null,
            charge: isset($data['charge']) ? (float) $data['charge'] : null,
            interest: isset($data['interest']) ? (float) $data['interest'] : null,
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
