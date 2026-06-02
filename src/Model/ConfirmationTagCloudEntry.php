<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

/**
 * Eintrag aus der aggregierten Tag-Cloud GET /confirmation-tags (ohne confirmation_id).
 */
final readonly class ConfirmationTagCloudEntry
{
    public function __construct(
        public ?int $id,
        public string $name,
        public int $count,
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
            count: isset($data['count']) ? (int) $data['count'] : 0,
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
            'count' => $this->count,
        ];
    }
}
