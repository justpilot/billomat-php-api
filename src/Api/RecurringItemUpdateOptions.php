<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\ItemType;
use Justpilot\Billomat\Model\Money;

/**
 * Typisierter Payload für PUT /recurring-items/{id} (Position bearbeiten).
 *
 * Nur gesetzte (nicht-null) Felder werden serialisiert (Partial Update);
 * Billomat lässt nicht gesendete Felder unverändert.
 *
 * Doku:
 * https://www.billomat.com/api/abo-rechnungen/positionen/
 *
 * Generiert aus tools/codegen/definitions/recurring-items.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class RecurringItemUpdateOptions implements WriteOptions
{
    public function __construct(
        /**
         * ID des Artikels (falls verknüpft).
         *
         * Billomat-Feld: article_id
         * Typ: INT
         */
        public int|Clear|null $articleId = null,
        /**
         * Laufende Positionsnummer innerhalb des Dokuments.
         *
         * Wird beim Anlegen normalerweise automatisch vergeben (anhängen ans Ende).
         *
         * Billomat-Feld: position
         */
        public int|Clear|null $position = null,
        /**
         * Einheit (z. B. "Stück", "Stunde").
         *
         * Billomat-Feld: unit
         * Typ: ALNUM
         */
        public string|Clear|null $unit = null,
        /**
         * Menge.
         *
         * Billomat-Feld: quantity
         * Typ: FLOAT
         */
        public float|Clear|null $quantity = null,
        /**
         * Einzelpreis.
         *
         * Billomat-Feld: unit_price
         * Typ: FLOAT
         */
        public Money|Clear|null $unitPrice = null,
        /**
         * Steuerbezeichnung (z. B. "MwSt").
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
         * Flag, ob die Steuerrate manuell geändert wurde.
         *
         * Laut Doku Pflicht, *wenn* nicht der Standard-Steuersatz angewendet werden soll.
         *
         * Billomat-Feld: tax_changed_manually
         */
        public bool|Clear|null $taxChangedManually = null,
        /**
         * Titel der Position.
         *
         * Billomat-Feld: title
         * Typ: ALNUM
         */
        public string|Clear|null $title = null,
        /**
         * Beschreibung der Position.
         *
         * Billomat-Feld: description
         * Typ: ALNUM
         */
        public string|Clear|null $description = null,
        /**
         * Positionsrabatt (absolut oder als Prozentwert, z. B. "10" oder "10%").
         *
         * Billomat-Feld: reduction
         * Typ: ALNUM
         */
        public string|Clear|null $reduction = null,
        /**
         * Typ der Position (Produkt/Dienstleistung).
         *
         * Billomat-Feld: type
         */
        public ItemType|Clear|null $type = null,
    ) {
    }

    /** Kopie mit geändertem `articleId`. */
    public function withArticleId(int|Clear|null $articleId): self
    {
        return clone($this, ['articleId' => $articleId]);
    }

    /** Kopie mit geändertem `position`. */
    public function withPosition(int|Clear|null $position): self
    {
        return clone($this, ['position' => $position]);
    }

    /** Kopie mit geändertem `unit`. */
    public function withUnit(string|Clear|null $unit): self
    {
        return clone($this, ['unit' => $unit]);
    }

    /** Kopie mit geändertem `quantity`. */
    public function withQuantity(float|Clear|null $quantity): self
    {
        return clone($this, ['quantity' => $quantity]);
    }

    /** Kopie mit geändertem `unitPrice`. */
    public function withUnitPrice(Money|Clear|null $unitPrice): self
    {
        return clone($this, ['unitPrice' => $unitPrice]);
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

    /** Kopie mit geändertem `taxChangedManually`. */
    public function withTaxChangedManually(bool|Clear|null $taxChangedManually): self
    {
        return clone($this, ['taxChangedManually' => $taxChangedManually]);
    }

    /** Kopie mit geändertem `title`. */
    public function withTitle(string|Clear|null $title): self
    {
        return clone($this, ['title' => $title]);
    }

    /** Kopie mit geändertem `description`. */
    public function withDescription(string|Clear|null $description): self
    {
        return clone($this, ['description' => $description]);
    }

    /** Kopie mit geändertem `reduction`. */
    public function withReduction(string|Clear|null $reduction): self
    {
        return clone($this, ['reduction' => $reduction]);
    }

    /** Kopie mit geändertem `type`. */
    public function withType(ItemType|Clear|null $type): self
    {
        return clone($this, ['type' => $type]);
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
                'type' => $this->type instanceof ItemType ? $this->type->value : $this->type,
                'article_id' => $this->articleId,
                'title' => $this->title,
                'description' => $this->description,
                'quantity' => $this->quantity,
                'unit_price' => $this->unitPrice instanceof Money ? $this->unitPrice->toString() : $this->unitPrice,
                'unit' => $this->unit,
                'tax_name' => $this->taxName,
                'tax_rate' => $this->taxRate,
                'tax_changed_manually' => $this->taxChangedManually,
                'reduction' => $this->reduction,
                'position' => $this->position,
            ], static fn (mixed $v): bool => null !== $v),
        );
    }
}
