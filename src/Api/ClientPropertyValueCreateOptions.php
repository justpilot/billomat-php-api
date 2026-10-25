<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Typisierter Payload für POST /client-property-values gemäß Billomat-API.
 *
 * Doku:
 * https://www.billomat.com/api/kunden/attribute/
 *
 * Generiert aus tools/codegen/definitions/client-property-values.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final class ClientPropertyValueCreateOptions implements WriteOptions
{
    public function __construct(
        /**
         * ID des Datensatzes, zu dem der Wert gehört.
         *
         * Billomat-Feld: client_id
         * Typ: INT
         */
        public int $clientId,
        /**
         * ID der Eigenschaft.
         *
         * Billomat-Feld: client_property_id
         * Typ: INT
         */
        public int $clientPropertyId,
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
            'client_id' => $this->clientId,
            'client_property_id' => $this->clientPropertyId,
            'value' => $this->value,
        ];
    }
}
