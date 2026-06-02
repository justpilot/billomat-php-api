<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Internal\ScalarCaster;

/**
 * Schlagwort/Tag an einer Mahnung.
 */
final readonly class ReminderTag
{
    public function __construct(
        public ?int $id,
        public int $reminderId,
        public string $name,
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ScalarCaster::toIntOrNull($data['id'] ?? null),
            reminderId: (int) ($data['reminder_id'] ?? 0),
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
            'reminder_id' => $this->reminderId,
            'name' => $this->name,
        ];
    }
}
