<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

/**
 * Schlagwort/Tag an einer Abo-Rechnung.
 *
 * Doku: https://www.billomat.com/api/abo-rechnungen/schlagworte/
 */
final readonly class RecurringTag
{
    public function __construct(
        public ?int $id,
        public int $recurringId,
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
            recurringId: (int) ($data['recurring_id'] ?? 0),
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
            'recurring_id' => $this->recurringId,
            'name' => $this->name,
        ];
    }
}
