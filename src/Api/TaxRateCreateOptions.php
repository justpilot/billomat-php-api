<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Typisierter Payload für POST /taxes gemäß Billomat-API.
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
        /**
         * Umsatzsteuer-Kategorie für E-Rechnungen (Codeliste UNCL5305, z. B. "S", "Z", "E", "AE").
         *
         * Von Billomat geliefert und angenommen, aber nicht dokumentiert.
         *
         * Billomat-Feld: vat_category_code
         */
        public ?string $vatCategoryCode = null,
        /**
         * Code des Befreiungsgrunds für E-Rechnungen (Codeliste VATEX, z. B. "VATEX-EU-132").
         *
         * Von Billomat geliefert und angenommen, aber nicht dokumentiert.
         *
         * Billomat-Feld: vat_exemption_reason_code
         */
        public ?string $vatExemptionReasonCode = null,
        /**
         * Befreiungsgrund als Text für E-Rechnungen.
         *
         * Von Billomat geliefert und angenommen, aber nicht dokumentiert.
         *
         * Billomat-Feld: vat_exemption_reason_text
         */
        public ?string $vatExemptionReasonText = null,
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

    /** Kopie mit geändertem `vatCategoryCode`. */
    public function withVatCategoryCode(?string $vatCategoryCode): self
    {
        return clone($this, ['vatCategoryCode' => $vatCategoryCode]);
    }

    /** Kopie mit geändertem `vatExemptionReasonCode`. */
    public function withVatExemptionReasonCode(?string $vatExemptionReasonCode): self
    {
        return clone($this, ['vatExemptionReasonCode' => $vatExemptionReasonCode]);
    }

    /** Kopie mit geändertem `vatExemptionReasonText`. */
    public function withVatExemptionReasonText(?string $vatExemptionReasonText): self
    {
        return clone($this, ['vatExemptionReasonText' => $vatExemptionReasonText]);
    }

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'rate' => $this->rate,
            'is_default' => ($this->isDefault ? 1 : 0),
            'vat_category_code' => $this->vatCategoryCode,
            'vat_exemption_reason_code' => $this->vatExemptionReasonCode,
            'vat_exemption_reason_text' => $this->vatExemptionReasonText,
        ], static fn (mixed $v): bool => null !== $v);
    }
}
