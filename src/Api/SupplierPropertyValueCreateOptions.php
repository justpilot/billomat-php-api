<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Payload für POST /supplier-property-values.
 */
final class SupplierPropertyValueCreateOptions
{
    public function __construct(
        public int $supplierId,
        public int $supplierPropertyId,
        public mixed $value,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'supplier_id' => $this->supplierId,
            'supplier_property_id' => $this->supplierPropertyId,
            'value' => $this->value,
        ];
    }
}
