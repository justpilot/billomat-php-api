<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Internal\ScalarCaster;

/**
 * Wert einer Lieferanten-Eigenschaft.
 *
 * Doku: https://www.billomat.com/en/api/suppliers/properties/
 */
final readonly class SupplierPropertyValue
{
    public function __construct(
        public ?int $id,
        public int $supplierId,
        public int $supplierPropertyId,
        public ?string $type,
        public ?string $name,
        public mixed $value,
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
            supplierPropertyId: (int) ($data['supplier_property_id'] ?? 0),
            type: ScalarCaster::toStringOrNull($data['type'] ?? null),
            name: ScalarCaster::toStringOrNull($data['name'] ?? null),
            value: $data['value'] ?? null,
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
            'supplier_property_id' => $this->supplierPropertyId,
            'type' => $this->type,
            'name' => $this->name,
            'value' => $this->value,
        ];
    }
}
