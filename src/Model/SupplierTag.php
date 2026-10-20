<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Internal\ScalarCaster;

/**
 * Schlagwort/Tag an einem Lieferanten.
 */
final readonly class SupplierTag
{
    public function __construct(
        public ?int $id,
        public int $supplierId,
        public string $name,
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
            supplierId: (int) ($data['supplier_id'] ?? 0),
            name: (string) ($data['name'] ?? ''),
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
            'supplier_id' => $this->supplierId,
            'name' => $this->name,
            'customfield' => $this->customfield,
        ];
    }
}
