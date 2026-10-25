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
final class SupplierPropertyValueCreateOptions implements WriteOptions
{
    public function __construct(
        /**
         * ID des Datensatzes, zu dem der Wert gehört.
         *
         * Billomat-Feld: supplier_id
         * Typ: INT
         */
        public int $supplierId,
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

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
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
