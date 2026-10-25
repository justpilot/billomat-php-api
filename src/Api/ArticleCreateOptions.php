<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\ItemType;
use Justpilot\Billomat\Model\Enum\NetGross;
use Justpilot\Billomat\Model\Money;

/**
 * Typisierter Payload für POST /articles gemäß Billomat-API.
 *
 * Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren ignoriert.
 *
 * Doku:
 * https://www.billomat.com/en/api/articles/
 *
 * Generiert aus tools/codegen/definitions/articles.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class ArticleCreateOptions implements WriteOptions
{
    public function __construct(
        /**
         * Titel des Artikels.
         *
         * Billomat-Feld: title
         * Typ: ALNUM
         * Default: leerer String
         */
        public string $title,
        /**
         * Laufende Nummer (ohne Präfix).
         *
         * Billomat-Feld: number
         * Typ: INT
         * Default: nächste freie Artikelnummer
         */
        public ?int $number = null,
        /**
         * Präfix der Artikelnummer.
         *
         * Billomat-Feld: number_pre
         * Typ: ALNUM
         * Default: Wert aus Einstellungen
         */
        public ?string $numberPre = null,
        /**
         * Mindestlänge der Artikelnummer (wird mit führenden Nullen aufgefüllt).
         *
         * Billomat-Feld: number_length
         * Typ: INT
         * Default: Wert aus den Einstellungen
         */
        public ?int $numberLength = null,
        /**
         * Beschreibung.
         *
         * Billomat-Feld: description
         * Typ: ALNUM
         * Default: leerer String
         */
        public ?string $description = null,
        /**
         * Verkaufspreis.
         *
         * Billomat-Feld: sales_price
         * Typ: FLOAT
         * Default: 0.0
         */
        public ?Money $salesPrice = null,
        /**
         * Währung (ISO-Währungscode).
         *
         * Beispiel: "EUR"
         *
         * Billomat-Feld: currency_code
         * Typ: ISO-Währungscode
         * Default: Standard-Währung aus Einstellungen
         */
        public ?string $currencyCode = null,
        /**
         * Preis für Kunden der Preisgruppe 2.
         *
         * Ohne Angabe gilt der Normalpreis.
         *
         * Billomat-Feld: sales_price2
         * Typ: FLOAT
         */
        public ?Money $salesPrice2 = null,
        /**
         * Preis für Kunden der Preisgruppe 3.
         *
         * Ohne Angabe gilt der Normalpreis.
         *
         * Billomat-Feld: sales_price3
         * Typ: FLOAT
         */
        public ?Money $salesPrice3 = null,
        /**
         * Preis für Kunden der Preisgruppe 4.
         *
         * Ohne Angabe gilt der Normalpreis.
         *
         * Billomat-Feld: sales_price4
         * Typ: FLOAT
         */
        public ?Money $salesPrice4 = null,
        /**
         * Preis für Kunden der Preisgruppe 5.
         *
         * Ohne Angabe gilt der Normalpreis.
         *
         * Billomat-Feld: sales_price5
         * Typ: FLOAT
         */
        public ?Money $salesPrice5 = null,
        /**
         * Bezeichnung der Einheit.
         *
         * Besser `unitId` verwenden.
         *
         * Billomat-Feld: unit
         */
        public ?string $unit = null,
        /**
         * ID der verknüpften Einheit.
         *
         * Billomat-Feld: unit_id
         * Typ: INT
         * Default: null
         */
        public ?int $unitId = null,
        /**
         * Einkaufspreis.
         *
         * Billomat-Feld: purchase_price
         * Typ: FLOAT
         * Default: null
         */
        public ?Money $purchasePrice = null,
        /**
         * Währung des Einkaufspreises (ISO-Währungscode).
         *
         * Billomat-Feld: purchase_price_currency_code
         */
        public ?string $purchasePriceCurrencyCode = null,
        /**
         * ID des verknüpften Lieferanten.
         *
         * Billomat-Feld: supplier_id
         * Typ: INT
         * Default: null
         */
        public ?int $supplierId = null,
        /**
         * ID des verknüpften Steuersatzes.
         *
         * Billomat-Feld: tax_id
         * Typ: INT
         * Default: null
         */
        public ?int $taxId = null,
        /**
         * ID der Artikelkategorie.
         *
         * Billomat-Feld: category_id
         */
        public ?int $categoryId = null,
        /**
         * Artikeltyp: Produkt oder Dienstleistung.
         *
         * Billomat-Feld: type
         * Typ: ENUM (PRODUCT/SERVICE)
         */
        public ?ItemType $type = null,
        /**
         * Preisbasis des Einkaufspreises (netto oder brutto).
         *
         * Billomat-Feld: purchase_price_net_gross
         * Typ: ALNUM („NET“, „GROSS“)
         * Default: NET
         */
        public ?NetGross $purchasePriceNetGross = null,
    ) {
    }

    /** Kopie mit geändertem `title`. */
    public function withTitle(string $title): self
    {
        return clone($this, ['title' => $title]);
    }

    /** Kopie mit geändertem `number`. */
    public function withNumber(?int $number): self
    {
        return clone($this, ['number' => $number]);
    }

    /** Kopie mit geändertem `numberPre`. */
    public function withNumberPre(?string $numberPre): self
    {
        return clone($this, ['numberPre' => $numberPre]);
    }

    /** Kopie mit geändertem `numberLength`. */
    public function withNumberLength(?int $numberLength): self
    {
        return clone($this, ['numberLength' => $numberLength]);
    }

    /** Kopie mit geändertem `description`. */
    public function withDescription(?string $description): self
    {
        return clone($this, ['description' => $description]);
    }

    /** Kopie mit geändertem `salesPrice`. */
    public function withSalesPrice(?Money $salesPrice): self
    {
        return clone($this, ['salesPrice' => $salesPrice]);
    }

    /** Kopie mit geändertem `currencyCode`. */
    public function withCurrencyCode(?string $currencyCode): self
    {
        return clone($this, ['currencyCode' => $currencyCode]);
    }

    /** Kopie mit geändertem `salesPrice2`. */
    public function withSalesPrice2(?Money $salesPrice2): self
    {
        return clone($this, ['salesPrice2' => $salesPrice2]);
    }

    /** Kopie mit geändertem `salesPrice3`. */
    public function withSalesPrice3(?Money $salesPrice3): self
    {
        return clone($this, ['salesPrice3' => $salesPrice3]);
    }

    /** Kopie mit geändertem `salesPrice4`. */
    public function withSalesPrice4(?Money $salesPrice4): self
    {
        return clone($this, ['salesPrice4' => $salesPrice4]);
    }

    /** Kopie mit geändertem `salesPrice5`. */
    public function withSalesPrice5(?Money $salesPrice5): self
    {
        return clone($this, ['salesPrice5' => $salesPrice5]);
    }

    /** Kopie mit geändertem `unit`. */
    public function withUnit(?string $unit): self
    {
        return clone($this, ['unit' => $unit]);
    }

    /** Kopie mit geändertem `unitId`. */
    public function withUnitId(?int $unitId): self
    {
        return clone($this, ['unitId' => $unitId]);
    }

    /** Kopie mit geändertem `purchasePrice`. */
    public function withPurchasePrice(?Money $purchasePrice): self
    {
        return clone($this, ['purchasePrice' => $purchasePrice]);
    }

    /** Kopie mit geändertem `purchasePriceCurrencyCode`. */
    public function withPurchasePriceCurrencyCode(?string $purchasePriceCurrencyCode): self
    {
        return clone($this, ['purchasePriceCurrencyCode' => $purchasePriceCurrencyCode]);
    }

    /** Kopie mit geändertem `supplierId`. */
    public function withSupplierId(?int $supplierId): self
    {
        return clone($this, ['supplierId' => $supplierId]);
    }

    /** Kopie mit geändertem `taxId`. */
    public function withTaxId(?int $taxId): self
    {
        return clone($this, ['taxId' => $taxId]);
    }

    /** Kopie mit geändertem `categoryId`. */
    public function withCategoryId(?int $categoryId): self
    {
        return clone($this, ['categoryId' => $categoryId]);
    }

    /** Kopie mit geändertem `type`. */
    public function withType(?ItemType $type): self
    {
        return clone($this, ['type' => $type]);
    }

    /** Kopie mit geändertem `purchasePriceNetGross`. */
    public function withPurchasePriceNetGross(?NetGross $purchasePriceNetGross): self
    {
        return clone($this, ['purchasePriceNetGross' => $purchasePriceNetGross]);
    }

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'number' => $this->number,
            'number_pre' => $this->numberPre,
            'number_length' => $this->numberLength,
            'title' => $this->title,
            'description' => $this->description,
            'sales_price' => $this->salesPrice?->toString(),
            'currency_code' => $this->currencyCode,
            'sales_price2' => $this->salesPrice2?->toString(),
            'sales_price3' => $this->salesPrice3?->toString(),
            'sales_price4' => $this->salesPrice4?->toString(),
            'sales_price5' => $this->salesPrice5?->toString(),
            'unit' => $this->unit,
            'unit_id' => $this->unitId,
            'purchase_price' => $this->purchasePrice?->toString(),
            'purchase_price_currency_code' => $this->purchasePriceCurrencyCode,
            'supplier_id' => $this->supplierId,
            'tax_id' => $this->taxId,
            'category_id' => $this->categoryId,
            'type' => $this->type?->value,
            'purchase_price_net_gross' => $this->purchasePriceNetGross?->value,
        ], static fn (mixed $v): bool => null !== $v);
    }
}
