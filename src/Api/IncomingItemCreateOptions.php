<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\ItemType;
use Justpilot\Billomat\Model\Money;

/**
 * Typisierter Payload für POST /incoming-items gemäß Billomat-API.
 *
 * Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren ignoriert.
 *
 * Doku:
 * https://www.billomat.com/api/eingangsrechnungen/posten/
 *
 * Generiert aus tools/codegen/definitions/incoming-items.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class IncomingItemCreateOptions implements WriteOptions
{
    public function __construct(
        /**
         * Betrag des Postens.
         *
         * Billomat-Feld: total
         * Typ: INT
         */
        public Money $total,
        /**
         * Beschreibung des Postens.
         *
         * Billomat-Feld: description
         * Typ: ALNUM
         */
        public ?string $description = null,
        /**
         * Laufende Nummer des Postens innerhalb der Eingangsrechnung.
         *
         * Wird beim Anlegen normalerweise automatisch vergeben (anhängen ans Ende).
         *
         * Billomat-Feld: position
         * Typ: INT
         */
        public ?int $position = null,
        /**
         * Steuerbezeichnung (z. B. "USt").
         *
         * Billomat-Feld: tax_name
         * Typ: ALNUM
         */
        public ?string $taxName = null,
        /**
         * Steuerrate in Prozent.
         *
         * Billomat-Feld: tax_rate
         * Typ: FLOAT
         */
        public ?float $taxRate = null,
        /**
         * Steuerrate für das Reverse-Charge-Verfahren.
         *
         * Billomat-Feld: tax_rate_reverse_charge
         * Typ: FLOAT
         */
        public ?float $taxRateReverseCharge = null,
        /**
         * Ist die Steuer im Betrag enthalten (Brutto)?
         *
         * Billomat-Feld: tax_included
         * Typ: BOOL
         */
        public ?bool $taxIncluded = null,
        /**
         * Titel der Kategorie.
         *
         * Billomat-Feld: category
         * Typ: ALNUM
         */
        public ?string $category = null,
        /**
         * Produkt oder Dienstleistung.
         *
         * Nur bei Rechnungen ins Ausland, abhängig vom Firmenstandort.
         *
         * Billomat-Feld: type
         * Typ: ENUM[PRODUCT,SERVICE]
         */
        public ?ItemType $type = null,
        /**
         * Kostenstelle.
         *
         * Billomat-Feld: cost_center
         * Typ: INT
         */
        public ?int $costCenter = null,
        /**
         * Nummer des Aufwandskontos.
         *
         * Billomat-Feld: expense_account_number
         * Typ: INT
         */
        public ?int $expenseAccountNumber = null,
    ) {
    }

    /** Kopie mit geändertem `total`. */
    public function withTotal(Money $total): self
    {
        return clone($this, ['total' => $total]);
    }

    /** Kopie mit geändertem `description`. */
    public function withDescription(?string $description): self
    {
        return clone($this, ['description' => $description]);
    }

    /** Kopie mit geändertem `position`. */
    public function withPosition(?int $position): self
    {
        return clone($this, ['position' => $position]);
    }

    /** Kopie mit geändertem `taxName`. */
    public function withTaxName(?string $taxName): self
    {
        return clone($this, ['taxName' => $taxName]);
    }

    /** Kopie mit geändertem `taxRate`. */
    public function withTaxRate(?float $taxRate): self
    {
        return clone($this, ['taxRate' => $taxRate]);
    }

    /** Kopie mit geändertem `taxRateReverseCharge`. */
    public function withTaxRateReverseCharge(?float $taxRateReverseCharge): self
    {
        return clone($this, ['taxRateReverseCharge' => $taxRateReverseCharge]);
    }

    /** Kopie mit geändertem `taxIncluded`. */
    public function withTaxIncluded(?bool $taxIncluded): self
    {
        return clone($this, ['taxIncluded' => $taxIncluded]);
    }

    /** Kopie mit geändertem `category`. */
    public function withCategory(?string $category): self
    {
        return clone($this, ['category' => $category]);
    }

    /** Kopie mit geändertem `type`. */
    public function withType(?ItemType $type): self
    {
        return clone($this, ['type' => $type]);
    }

    /** Kopie mit geändertem `costCenter`. */
    public function withCostCenter(?int $costCenter): self
    {
        return clone($this, ['costCenter' => $costCenter]);
    }

    /** Kopie mit geändertem `expenseAccountNumber`. */
    public function withExpenseAccountNumber(?int $expenseAccountNumber): self
    {
        return clone($this, ['expenseAccountNumber' => $expenseAccountNumber]);
    }

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'total' => $this->total->toString(),
            'description' => $this->description,
            'position' => $this->position,
            'tax_name' => $this->taxName,
            'tax_rate' => $this->taxRate,
            'tax_rate_reverse_charge' => $this->taxRateReverseCharge,
            'tax_included' => null === $this->taxIncluded ? null : ($this->taxIncluded ? 1 : 0),
            'category' => $this->category,
            'type' => $this->type?->value,
            'cost_center' => $this->costCenter,
            'expense_account_number' => $this->expenseAccountNumber,
        ], static fn (mixed $v): bool => null !== $v);
    }
}
