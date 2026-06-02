<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Internal\ScalarCaster;

/**
 * Eintrag aus der aggregierten Tag-Cloud GET /supplier-tags.
 */
final readonly class SupplierTagCloudEntry
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
            id: ScalarCaster::toIntOrNull($data['id'] ?? null),
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
