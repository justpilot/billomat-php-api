<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Typisierter Payload für POST /supplier-property-values gemäß Billomat-API.
 *
 * Doku:
 * https://www.billomat.com/api/lieferanten/attribute/
 *
 * Generiert aus tools/codegen/definitions/supplier-property-values.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class SupplierPropertyValueCreateOptions implements WriteOptions
{
    public function __construct(
        /**
         * ID der Eigenschaft.
         *
         * Billomat-Feld: supplier_property_id
         * Typ: INT
         */
        public int $supplierPropertyId,
        /**
         * Wert der Eigenschaft.
         *
         * Billomat-Feld: value
         * Typ: ALNUM
         */
        public mixed $value,
    ) {
    }

    /** Kopie mit geändertem `supplierPropertyId`. */
    public function withSupplierPropertyId(int $supplierPropertyId): self
    {
        return clone($this, ['supplierPropertyId' => $supplierPropertyId]);
    }

    /** Kopie mit geändertem `value`. */
    public function withValue(mixed $value): self
    {
        return clone($this, ['value' => $value]);
    }

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'supplier_property_id' => $this->supplierPropertyId,
            'value' => $this->value,
        ];
    }
}
