<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Typisierter Payload für PUT /taxes/{id} gemäß Billomat-API.
 *
 * Nur gesetzte (nicht-null) Felder werden serialisiert (Partial Update):
 * ein Umbenennen setzt `is_default` nicht zurück.
 *
 * Doku:
 * https://www.billomat.com/api/einstellungen/steuersaetze/
 *
 * Generiert aus tools/codegen/definitions/taxes.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class TaxRateUpdateOptions implements WriteOptions
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
        public string|Clear|null $name = null,
        /**
         * Höhe des Steuersatzes in Prozent.
         *
         * Beispiel: 19.0
         *
         * Billomat-Feld: rate
         * Typ: FLOAT
         */
        public float|Clear|null $rate = null,
        /**
         * Standard-Steuersatz?
         *
         * Billomat-Feld: is_default
         * Typ: BOOL
         */
        public bool|Clear|null $isDefault = null,
        /**
         * Umsatzsteuer-Kategorie für E-Rechnungen (Codeliste UNCL5305, z. B. "S", "Z", "E", "AE").
         *
         * Von Billomat geliefert und angenommen, aber nicht dokumentiert.
         *
         * Billomat-Feld: vat_category_code
         */
        public string|Clear|null $vatCategoryCode = null,
        /**
         * Code des Befreiungsgrunds für E-Rechnungen (Codeliste VATEX, z. B. "VATEX-EU-132").
         *
         * Von Billomat geliefert und angenommen, aber nicht dokumentiert.
         *
         * Billomat-Feld: vat_exemption_reason_code
         */
        public string|Clear|null $vatExemptionReasonCode = null,
        /**
         * Befreiungsgrund als Text für E-Rechnungen.
         *
         * Von Billomat geliefert und angenommen, aber nicht dokumentiert.
         *
         * Billomat-Feld: vat_exemption_reason_text
         */
        public string|Clear|null $vatExemptionReasonText = null,
    ) {
    }

    /** Kopie mit geändertem `name`. */
    public function withName(string|Clear|null $name): self
    {
        return clone($this, ['name' => $name]);
    }

    /** Kopie mit geändertem `rate`. */
    public function withRate(float|Clear|null $rate): self
    {
        return clone($this, ['rate' => $rate]);
    }

    /** Kopie mit geändertem `isDefault`. */
    public function withIsDefault(bool|Clear|null $isDefault): self
    {
        return clone($this, ['isDefault' => $isDefault]);
    }

    /** Kopie mit geändertem `vatCategoryCode`. */
    public function withVatCategoryCode(string|Clear|null $vatCategoryCode): self
    {
        return clone($this, ['vatCategoryCode' => $vatCategoryCode]);
    }

    /** Kopie mit geändertem `vatExemptionReasonCode`. */
    public function withVatExemptionReasonCode(string|Clear|null $vatExemptionReasonCode): self
    {
        return clone($this, ['vatExemptionReasonCode' => $vatExemptionReasonCode]);
    }

    /** Kopie mit geändertem `vatExemptionReasonText`. */
    public function withVatExemptionReasonText(string|Clear|null $vatExemptionReasonText): self
    {
        return clone($this, ['vatExemptionReasonText' => $vatExemptionReasonText]);
    }

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
     * Nicht gesetzte Felder (null) fehlen im Payload, {@see Clear::Field} wird als null gesendet und leert das Feld.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return array_map(
            static fn (mixed $v): mixed => $v instanceof Clear ? null : $v,
            array_filter([
                'name' => $this->name,
                'rate' => $this->rate,
                'is_default' => \is_bool($this->isDefault) ? ($this->isDefault ? 1 : 0) : $this->isDefault,
                'vat_category_code' => $this->vatCategoryCode,
                'vat_exemption_reason_code' => $this->vatExemptionReasonCode,
                'vat_exemption_reason_text' => $this->vatExemptionReasonText,
            ], static fn (mixed $v): bool => null !== $v),
        );
    }
}
