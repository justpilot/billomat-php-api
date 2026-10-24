<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Typisierter Payload für POST /taxes und PUT /taxes/{id} gemäß Billomat-API.
 *
 * Doku:
 * https://www.billomat.com/api/einstellungen/steuersaetze/
 *
 * Generiert aus tools/codegen/definitions/taxes.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final class TaxRateCreateOptions
{
    public function __construct(
        /**
         * Name des Steuersatzes.
         *
         * Beispiel: "MwSt"
         *
         * Billomat-Feld: name
         * Typ: ALNUM
         */
        public string $name,
        /**
         * Höhe des Steuersatzes in Prozent.
         *
         * Beispiel: 19.0
         *
         * Billomat-Feld: rate
         * Typ: FLOAT
         */
        public float $rate,
        /**
         * Standard-Steuersatz?
         *
         * Billomat-Feld: is_default
         * Typ: BOOL
         * Default: nein
         */
        public bool $isDefault = false,
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
            'name' => $this->name,
            'rate' => $this->rate,
            'is_default' => ($this->isDefault ? 1 : 0),
        ];
    }
}
