<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\InvoiceItemType;
use Justpilot\Billomat\Model\Money;

/**
 * Typisierter Payload für POST /credit-note-items gemäß Billomat-API
 * sowie für eingebettete Positionen beim Anlegen des Dokuments.
 *
 * Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren ignoriert.
 *
 * Doku:
 * https://www.billomat.com/api/gutschriften/positionen/
 *
 * Generiert aus tools/codegen/definitions/credit-note-items.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class CreditNoteItemCreateOptions implements WriteOptions
{
    public function __construct(
        /**
         * Menge.
         *
         * Billomat-Feld: quantity
         * Typ: FLOAT
         * Default: 0.0
         */
        public float $quantity,
        /**
         * Einzelpreis.
         *
         * Billomat-Feld: unit_price
         * Typ: FLOAT
         * Default: 0.0
         */
        public Money $unitPrice,
        /**
         * ID des Artikels (falls verknüpft).
         *
         * Billomat-Feld: article_id
         * Typ: INT
         */
        public ?int $articleId = null,
        /**
         * Laufende Positionsnummer innerhalb des Dokuments.
         *
         * Wird beim Anlegen normalerweise automatisch vergeben (anhängen ans Ende).
         *
         * Billomat-Feld: position
         */
        public ?int $position = null,
        /**
         * Einheit (z. B. "Stück", "Stunde").
         *
         * Billomat-Feld: unit
         * Typ: ALNUM
         */
        public ?string $unit = null,
        /**
         * Steuerbezeichnung (z. B. "MwSt").
         *
         * Billomat-Feld: tax_name
         * Typ: ALNUM
         * Default: Standard-Steuersatz
         */
        public ?string $taxName = null,
        /**
         * Steuerrate in Prozent.
         *
         * Billomat-Feld: tax_rate
         * Typ: FLOAT
         * Default: Standard-Steuersatz
         */
        public ?float $taxRate = null,
        /**
         * Flag, ob die Steuerrate manuell geändert wurde.
         *
         * Laut Doku Pflicht, *wenn* nicht der Standard-Steuersatz angewendet werden soll.
         *
         * Billomat-Feld: tax_changed_manually
         */
        public ?bool $taxChangedManually = null,
        /**
         * Titel der Position.
         *
         * Billomat-Feld: title
         * Typ: ALNUM
         */
        public ?string $title = null,
        /**
         * Beschreibung der Position.
         *
         * Billomat-Feld: description
         * Typ: ALNUM
         */
        public ?string $description = null,
        /**
         * Positionsrabatt (absolut oder als Prozentwert, z. B. "10" oder "10%").
         *
         * Billomat-Feld: reduction
         * Typ: ALNUM
         */
        public ?string $reduction = null,
        /**
         * Typ der Position (Produkt/Dienstleistung).
         *
         * Billomat-Feld: type
         */
        public ?InvoiceItemType $type = null,
    ) {
    }

    /** Kopie mit geändertem `quantity`. */
    public function withQuantity(float $quantity): self
    {
        return clone($this, ['quantity' => $quantity]);
    }

    /** Kopie mit geändertem `unitPrice`. */
    public function withUnitPrice(Money $unitPrice): self
    {
        return clone($this, ['unitPrice' => $unitPrice]);
    }

    /** Kopie mit geändertem `articleId`. */
    public function withArticleId(?int $articleId): self
    {
        return clone($this, ['articleId' => $articleId]);
    }

    /** Kopie mit geändertem `position`. */
    public function withPosition(?int $position): self
    {
        return clone($this, ['position' => $position]);
    }

    /** Kopie mit geändertem `unit`. */
    public function withUnit(?string $unit): self
    {
        return clone($this, ['unit' => $unit]);
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

    /** Kopie mit geändertem `taxChangedManually`. */
    public function withTaxChangedManually(?bool $taxChangedManually): self
    {
        return clone($this, ['taxChangedManually' => $taxChangedManually]);
    }

    /** Kopie mit geändertem `title`. */
    public function withTitle(?string $title): self
    {
        return clone($this, ['title' => $title]);
    }

    /** Kopie mit geändertem `description`. */
    public function withDescription(?string $description): self
    {
        return clone($this, ['description' => $description]);
    }

    /** Kopie mit geändertem `reduction`. */
    public function withReduction(?string $reduction): self
    {
        return clone($this, ['reduction' => $reduction]);
    }

    /** Kopie mit geändertem `type`. */
    public function withType(?InvoiceItemType $type): self
    {
        return clone($this, ['type' => $type]);
    }

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'type' => $this->type?->value,
            'article_id' => $this->articleId,
            'title' => $this->title,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice->toString(),
            'unit' => $this->unit,
            'tax_name' => $this->taxName,
            'tax_rate' => $this->taxRate,
            'tax_changed_manually' => $this->taxChangedManually,
            'reduction' => $this->reduction,
            'position' => $this->position,
        ], static fn (mixed $v): bool => null !== $v);
    }
}
