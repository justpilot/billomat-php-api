<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Typisierter Payload für POST /supplier-tags gemäß Billomat-API.
 *
 * Doku:
 * https://www.billomat.com/api/lieferanten/schlagworte/
 *
 * Generiert aus tools/codegen/definitions/supplier-tags.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final class SupplierTagCreateOptions implements WriteOptions
{
    public function __construct(
        /**
         * ID des Datensatzes, an dem das Schlagwort hängt.
         *
         * Billomat-Feld: supplier_id
         * Typ: INT
         */
        public int $supplierId,
        /**
         * Name des Schlagworts.
         *
         * Billomat-Feld: name
         * Typ: ALNUM
         */
        public string $name,
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
            'name' => $this->name,
        ];
    }
}
