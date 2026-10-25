<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\ItemType;
use Justpilot\Billomat\Model\Enum\NetGross;
use Justpilot\Billomat\Model\Money;

/**
 * Typisierter Payload für PUT /articles/{id} gemäß Billomat-API.
 *
 * Nur gesetzte (nicht-null) Felder werden serialisiert (Partial Update).
 *
 * Doku:
 * https://www.billomat.com/en/api/articles/
 *
 * Generiert aus tools/codegen/definitions/articles.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class ArticleUpdateOptions implements WriteOptions
{
    public function __construct(
        /**
         * Laufende Nummer (ohne Präfix).
         *
         * Billomat-Feld: number
         * Typ: INT
         */
        public int|Clear|null $number = null,
        /**
         * Präfix der Artikelnummer.
         *
         * Billomat-Feld: number_pre
         * Typ: ALNUM
         */
        public string|Clear|null $numberPre = null,
        /**
         * Mindestlänge der Artikelnummer (wird mit führenden Nullen aufgefüllt).
         *
         * Billomat-Feld: number_length
         * Typ: INT
         */
        public int|Clear|null $numberLength = null,
        /**
         * Titel des Artikels.
         *
         * Billomat-Feld: title
         * Typ: ALNUM
         */
        public string|Clear|null $title = null,
        /**
         * Beschreibung.
         *
         * Billomat-Feld: description
         * Typ: ALNUM
         */
        public string|Clear|null $description = null,
        /**
         * Verkaufspreis.
         *
         * Billomat-Feld: sales_price
         * Typ: FLOAT
         */
        public Money|Clear|null $salesPrice = null,
        /**
         * Währung (ISO-Währungscode).
         *
         * Beispiel: "EUR"
         *
         * Billomat-Feld: currency_code
         * Typ: ISO-Währungscode
         */
        public string|Clear|null $currencyCode = null,
        /**
         * Preis für Kunden der Preisgruppe 2.
         *
         * Ohne Angabe gilt der Normalpreis.
         *
         * Billomat-Feld: sales_price2
         * Typ: FLOAT
         */
        public Money|Clear|null $salesPrice2 = null,
        /**
         * Preis für Kunden der Preisgruppe 3.
         *
         * Ohne Angabe gilt der Normalpreis.
         *
         * Billomat-Feld: sales_price3
         * Typ: FLOAT
         */
        public Money|Clear|null $salesPrice3 = null,
        /**
         * Preis für Kunden der Preisgruppe 4.
         *
         * Ohne Angabe gilt der Normalpreis.
         *
         * Billomat-Feld: sales_price4
         * Typ: FLOAT
         */
        public Money|Clear|null $salesPrice4 = null,
        /**
         * Preis für Kunden der Preisgruppe 5.
         *
         * Ohne Angabe gilt der Normalpreis.
         *
         * Billomat-Feld: sales_price5
         * Typ: FLOAT
         */
        public Money|Clear|null $salesPrice5 = null,
        /**
         * Bezeichnung der Einheit.
         *
         * Besser `unitId` verwenden.
         *
         * Billomat-Feld: unit
         */
        public string|Clear|null $unit = null,
        /**
         * ID der verknüpften Einheit.
         *
         * Billomat-Feld: unit_id
         * Typ: INT
         */
        public int|Clear|null $unitId = null,
        /**
         * Einkaufspreis.
         *
         * Billomat-Feld: purchase_price
         * Typ: FLOAT
         */
        public Money|Clear|null $purchasePrice = null,
        /**
         * Währung des Einkaufspreises (ISO-Währungscode).
         *
         * Billomat-Feld: purchase_price_currency_code
         */
        public string|Clear|null $purchasePriceCurrencyCode = null,
        /**
         * ID des verknüpften Lieferanten.
         *
         * Billomat-Feld: supplier_id
         * Typ: INT
         */
        public int|Clear|null $supplierId = null,
        /**
         * ID des verknüpften Steuersatzes.
         *
         * Billomat-Feld: tax_id
         * Typ: INT
         */
        public int|Clear|null $taxId = null,
        /**
         * ID der Artikelkategorie.
         *
         * Billomat-Feld: category_id
         */
        public int|Clear|null $categoryId = null,
        /**
         * Artikeltyp: Produkt oder Dienstleistung.
         *
         * Billomat-Feld: type
         * Typ: ENUM (PRODUCT/SERVICE)
         */
        public ItemType|Clear|null $type = null,
        /**
         * Preisbasis des Einkaufspreises (netto oder brutto).
         *
         * Billomat-Feld: purchase_price_net_gross
         * Typ: ALNUM („NET“, „GROSS“)
         */
        public NetGross|Clear|null $purchasePriceNetGross = null,
    ) {
    }

    /** Kopie mit geändertem `number`. */
    public function withNumber(int|Clear|null $number): self
    {
        return clone($this, ['number' => $number]);
    }

    /** Kopie mit geändertem `numberPre`. */
    public function withNumberPre(string|Clear|null $numberPre): self
    {
        return clone($this, ['numberPre' => $numberPre]);
    }

    /** Kopie mit geändertem `numberLength`. */
    public function withNumberLength(int|Clear|null $numberLength): self
    {
        return clone($this, ['numberLength' => $numberLength]);
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

    /** Kopie mit geändertem `salesPrice`. */
    public function withSalesPrice(Money|Clear|null $salesPrice): self
    {
        return clone($this, ['salesPrice' => $salesPrice]);
    }

    /** Kopie mit geändertem `currencyCode`. */
    public function withCurrencyCode(string|Clear|null $currencyCode): self
    {
        return clone($this, ['currencyCode' => $currencyCode]);
    }

    /** Kopie mit geändertem `salesPrice2`. */
    public function withSalesPrice2(Money|Clear|null $salesPrice2): self
    {
        return clone($this, ['salesPrice2' => $salesPrice2]);
    }

    /** Kopie mit geändertem `salesPrice3`. */
    public function withSalesPrice3(Money|Clear|null $salesPrice3): self
    {
        return clone($this, ['salesPrice3' => $salesPrice3]);
    }

    /** Kopie mit geändertem `salesPrice4`. */
    public function withSalesPrice4(Money|Clear|null $salesPrice4): self
    {
        return clone($this, ['salesPrice4' => $salesPrice4]);
    }

    /** Kopie mit geändertem `salesPrice5`. */
    public function withSalesPrice5(Money|Clear|null $salesPrice5): self
    {
        return clone($this, ['salesPrice5' => $salesPrice5]);
    }

    /** Kopie mit geändertem `unit`. */
    public function withUnit(string|Clear|null $unit): self
    {
        return clone($this, ['unit' => $unit]);
    }

    /** Kopie mit geändertem `unitId`. */
    public function withUnitId(int|Clear|null $unitId): self
    {
        return clone($this, ['unitId' => $unitId]);
    }

    /** Kopie mit geändertem `purchasePrice`. */
    public function withPurchasePrice(Money|Clear|null $purchasePrice): self
    {
        return clone($this, ['purchasePrice' => $purchasePrice]);
    }

    /** Kopie mit geändertem `purchasePriceCurrencyCode`. */
    public function withPurchasePriceCurrencyCode(string|Clear|null $purchasePriceCurrencyCode): self
    {
        return clone($this, ['purchasePriceCurrencyCode' => $purchasePriceCurrencyCode]);
    }

    /** Kopie mit geändertem `supplierId`. */
    public function withSupplierId(int|Clear|null $supplierId): self
    {
        return clone($this, ['supplierId' => $supplierId]);
    }

    /** Kopie mit geändertem `taxId`. */
    public function withTaxId(int|Clear|null $taxId): self
    {
        return clone($this, ['taxId' => $taxId]);
    }

    /** Kopie mit geändertem `categoryId`. */
    public function withCategoryId(int|Clear|null $categoryId): self
    {
        return clone($this, ['categoryId' => $categoryId]);
    }

    /** Kopie mit geändertem `type`. */
    public function withType(ItemType|Clear|null $type): self
    {
        return clone($this, ['type' => $type]);
    }

    /** Kopie mit geändertem `purchasePriceNetGross`. */
    public function withPurchasePriceNetGross(NetGross|Clear|null $purchasePriceNetGross): self
    {
        return clone($this, ['purchasePriceNetGross' => $purchasePriceNetGross]);
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
                'number' => $this->number,
                'number_pre' => $this->numberPre,
                'number_length' => $this->numberLength,
                'title' => $this->title,
                'description' => $this->description,
                'sales_price' => $this->salesPrice instanceof Money ? $this->salesPrice->toString() : $this->salesPrice,
                'currency_code' => $this->currencyCode,
                'sales_price2' => $this->salesPrice2 instanceof Money ? $this->salesPrice2->toString() : $this->salesPrice2,
                'sales_price3' => $this->salesPrice3 instanceof Money ? $this->salesPrice3->toString() : $this->salesPrice3,
                'sales_price4' => $this->salesPrice4 instanceof Money ? $this->salesPrice4->toString() : $this->salesPrice4,
                'sales_price5' => $this->salesPrice5 instanceof Money ? $this->salesPrice5->toString() : $this->salesPrice5,
                'unit' => $this->unit,
                'unit_id' => $this->unitId,
                'purchase_price' => $this->purchasePrice instanceof Money ? $this->purchasePrice->toString() : $this->purchasePrice,
                'purchase_price_currency_code' => $this->purchasePriceCurrencyCode,
                'supplier_id' => $this->supplierId,
                'tax_id' => $this->taxId,
                'category_id' => $this->categoryId,
                'type' => $this->type instanceof ItemType ? $this->type->value : $this->type,
                'purchase_price_net_gross' => $this->purchasePriceNetGross instanceof NetGross ? $this->purchasePriceNetGross->value : $this->purchasePriceNetGross,
            ], static fn (mixed $v): bool => null !== $v),
        );
    }
}
