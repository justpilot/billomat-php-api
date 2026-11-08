<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\ItemType;
use Justpilot\Billomat\Model\Money;

/**
 * Typisierter Payload für PUT /incoming-items/{id} (Posten bearbeiten).
 *
 * Nur gesetzte (nicht-null) Felder werden serialisiert (Partial Update);
 * Billomat lässt nicht gesendete Felder unverändert.
 *
 * Doku:
 * https://www.billomat.com/api/eingangsrechnungen/posten/
 *
 * Generiert aus tools/codegen/definitions/incoming-items.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class IncomingItemUpdateOptions implements WriteOptions
{
    public function __construct(
        /**
         * Betrag des Postens.
         *
         * Billomat-Feld: total
         * Typ: INT
         */
        public Money|Clear|null $total = null,
        /**
         * Beschreibung des Postens.
         *
         * Billomat-Feld: description
         * Typ: ALNUM
         */
        public string|Clear|null $description = null,
        /**
         * Laufende Nummer des Postens innerhalb der Eingangsrechnung.
         *
         * Wird beim Anlegen normalerweise automatisch vergeben (anhängen ans Ende).
         *
         * Billomat-Feld: position
         * Typ: INT
         */
        public int|Clear|null $position = null,
        /**
         * Steuerbezeichnung (z. B. "USt").
         *
         * Billomat-Feld: tax_name
         * Typ: ALNUM
         */
        public string|Clear|null $taxName = null,
        /**
         * Steuerrate in Prozent.
         *
         * Billomat-Feld: tax_rate
         * Typ: FLOAT
         */
        public float|Clear|null $taxRate = null,
        /**
         * Steuerrate für das Reverse-Charge-Verfahren.
         *
         * Billomat-Feld: tax_rate_reverse_charge
         * Typ: FLOAT
         */
        public float|Clear|null $taxRateReverseCharge = null,
        /**
         * Ist die Steuer im Betrag enthalten (Brutto)?
         *
         * Billomat-Feld: tax_included
         * Typ: BOOL
         */
        public bool|Clear|null $taxIncluded = null,
        /**
         * Titel der Kategorie.
         *
         * Billomat-Feld: category
         * Typ: ALNUM
         */
        public string|Clear|null $category = null,
        /**
         * Produkt oder Dienstleistung.
         *
         * Nur bei Rechnungen ins Ausland, abhängig vom Firmenstandort.
         *
         * Billomat-Feld: type
         * Typ: ENUM[PRODUCT,SERVICE]
         */
        public ItemType|Clear|null $type = null,
        /**
         * Kostenstelle.
         *
         * Billomat-Feld: cost_center
         * Typ: INT
         */
        public int|Clear|null $costCenter = null,
        /**
         * Nummer des Aufwandskontos.
         *
         * Billomat-Feld: expense_account_number
         * Typ: INT
         */
        public int|Clear|null $expenseAccountNumber = null,
    ) {
    }

    /** Kopie mit geändertem `total`. */
    public function withTotal(Money|Clear|null $total): self
    {
        return clone($this, ['total' => $total]);
    }

    /** Kopie mit geändertem `description`. */
    public function withDescription(string|Clear|null $description): self
    {
        return clone($this, ['description' => $description]);
    }

    /** Kopie mit geändertem `position`. */
    public function withPosition(int|Clear|null $position): self
    {
        return clone($this, ['position' => $position]);
    }

    /** Kopie mit geändertem `taxName`. */
    public function withTaxName(string|Clear|null $taxName): self
    {
        return clone($this, ['taxName' => $taxName]);
    }

    /** Kopie mit geändertem `taxRate`. */
    public function withTaxRate(float|Clear|null $taxRate): self
    {
        return clone($this, ['taxRate' => $taxRate]);
    }

    /** Kopie mit geändertem `taxRateReverseCharge`. */
    public function withTaxRateReverseCharge(float|Clear|null $taxRateReverseCharge): self
    {
        return clone($this, ['taxRateReverseCharge' => $taxRateReverseCharge]);
    }

    /** Kopie mit geändertem `taxIncluded`. */
    public function withTaxIncluded(bool|Clear|null $taxIncluded): self
    {
        return clone($this, ['taxIncluded' => $taxIncluded]);
    }

    /** Kopie mit geändertem `category`. */
    public function withCategory(string|Clear|null $category): self
    {
        return clone($this, ['category' => $category]);
    }

    /** Kopie mit geändertem `type`. */
    public function withType(ItemType|Clear|null $type): self
    {
        return clone($this, ['type' => $type]);
    }

    /** Kopie mit geändertem `costCenter`. */
    public function withCostCenter(int|Clear|null $costCenter): self
    {
        return clone($this, ['costCenter' => $costCenter]);
    }

    /** Kopie mit geändertem `expenseAccountNumber`. */
    public function withExpenseAccountNumber(int|Clear|null $expenseAccountNumber): self
    {
        return clone($this, ['expenseAccountNumber' => $expenseAccountNumber]);
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
                'total' => $this->total instanceof Money ? $this->total->toString() : $this->total,
                'description' => $this->description,
                'position' => $this->position,
                'tax_name' => $this->taxName,
                'tax_rate' => $this->taxRate,
                'tax_rate_reverse_charge' => $this->taxRateReverseCharge,
                'tax_included' => \is_bool($this->taxIncluded) ? ($this->taxIncluded ? 1 : 0) : $this->taxIncluded,
                'category' => $this->category,
                'type' => $this->type instanceof ItemType ? $this->type->value : $this->type,
                'cost_center' => $this->costCenter,
                'expense_account_number' => $this->expenseAccountNumber,
            ], static fn (mixed $v): bool => null !== $v),
        );
    }
}
