<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Typisierter Payload für POST /incoming-property-values gemäß Billomat-API.
 *
 * Doku:
 * https://www.billomat.com/api/eingangsrechnungen/attribute/
 *
 * Generiert aus tools/codegen/definitions/incoming-property-values.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class IncomingPropertyValueCreateOptions implements WriteOptions
{
    public function __construct(
        /**
         * ID der Eigenschaft.
         *
         * Billomat-Feld: incoming_property_id
         * Typ: INT
         */
        public int $incomingPropertyId,
        /**
         * Wert der Eigenschaft.
         *
         * Billomat-Feld: value
         * Typ: ALNUM
         */
        public mixed $value,
    ) {
    }

    /** Kopie mit geändertem `incomingPropertyId`. */
    public function withIncomingPropertyId(int $incomingPropertyId): self
    {
        return clone($this, ['incomingPropertyId' => $incomingPropertyId]);
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
            'incoming_property_id' => $this->incomingPropertyId,
            'value' => $this->value,
        ];
    }
}
