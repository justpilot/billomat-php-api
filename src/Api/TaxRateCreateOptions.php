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
final readonly class TaxRateCreateOptions implements WriteOptions
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

    /** Kopie mit geändertem `name`. */
    public function withName(string $name): self
    {
        return clone($this, ['name' => $name]);
    }

    /** Kopie mit geändertem `rate`. */
    public function withRate(float $rate): self
    {
        return clone($this, ['rate' => $rate]);
    }

    /** Kopie mit geändertem `isDefault`. */
    public function withIsDefault(bool $isDefault): self
    {
        return clone($this, ['isDefault' => $isDefault]);
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
