<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

/**
 * Einheit (z.B. Stück, Stunde) aus den Billomat-Einstellungen.
 */
final readonly class Unit
{
    public function __construct(
        public ?int $id,
        public string $name,
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
        ];
    }
}
