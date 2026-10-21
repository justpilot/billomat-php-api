<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\ArticleType;
use Justpilot\Billomat\Model\Enum\NetGross;

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
final class ArticleCreateOptions
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
    ) {
    }

    /**
     * Laufende Nummer (ohne Präfix).
     *
     * Billomat-Feld: number
     * Typ: INT
     * Default: nächste freie Artikelnummer
     */
    public ?int $number = null;

    /**
     * Präfix der Artikelnummer.
     *
     * Billomat-Feld: number_pre
     * Typ: ALNUM
     * Default: Wert aus Einstellungen
     */
    public ?string $numberPre = null;

    /**
     * Mindestlänge der Artikelnummer (wird mit führenden Nullen aufgefüllt).
     *
     * Billomat-Feld: number_length
     * Typ: INT
     * Default: Wert aus den Einstellungen
     */
    public ?int $numberLength = null;

    /**
     * Beschreibung.
     *
     * Billomat-Feld: description
     * Typ: ALNUM
     * Default: leerer String
     */
    public ?string $description = null;

    /**
     * Verkaufspreis.
     *
     * Billomat-Feld: sales_price
     * Typ: FLOAT
     * Default: 0.0
     */
    public ?float $salesPrice = null;

    /**
     * Währung (ISO-Währungscode).
     *
     * Beispiel: "EUR"
     *
     * Billomat-Feld: currency_code
     * Typ: ISO-Währungscode
     * Default: Standard-Währung aus Einstellungen
     */
    public ?string $currencyCode = null;

    /**
     * Preis für Kunden der Preisgruppe 2.
     *
     * Ohne Angabe gilt der Normalpreis.
     *
     * Billomat-Feld: sales_price2
     * Typ: FLOAT
     */
    public ?float $salesPrice2 = null;

    /**
     * Preis für Kunden der Preisgruppe 3.
     *
     * Ohne Angabe gilt der Normalpreis.
     *
     * Billomat-Feld: sales_price3
     * Typ: FLOAT
     */
    public ?float $salesPrice3 = null;

    /**
     * Preis für Kunden der Preisgruppe 4.
     *
     * Ohne Angabe gilt der Normalpreis.
     *
     * Billomat-Feld: sales_price4
     * Typ: FLOAT
     */
    public ?float $salesPrice4 = null;

    /**
     * Preis für Kunden der Preisgruppe 5.
     *
     * Ohne Angabe gilt der Normalpreis.
     *
     * Billomat-Feld: sales_price5
     * Typ: FLOAT
     */
    public ?float $salesPrice5 = null;

    /**
     * Bezeichnung der Einheit.
     *
     * Besser `unitId` verwenden.
     *
     * Billomat-Feld: unit
     */
    public ?string $unit = null;

    /**
     * ID der verknüpften Einheit.
     *
     * Billomat-Feld: unit_id
     * Typ: INT
     * Default: null
     */
    public ?int $unitId = null;

    /**
     * Einkaufspreis.
     *
     * Billomat-Feld: purchase_price
     * Typ: FLOAT
     * Default: null
     */
    public ?float $purchasePrice = null;

    /**
     * Währung des Einkaufspreises (ISO-Währungscode).
     *
     * Billomat-Feld: purchase_price_currency_code
     */
    public ?string $purchasePriceCurrencyCode = null;

    /**
     * ID des verknüpften Lieferanten.
     *
     * Billomat-Feld: supplier_id
     * Typ: INT
     * Default: null
     */
    public ?int $supplierId = null;

    /**
     * ID des verknüpften Steuersatzes.
     *
     * Billomat-Feld: tax_id
     * Typ: INT
     * Default: null
     */
    public ?int $taxId = null;

    /**
     * ID der Artikelkategorie.
     *
     * Billomat-Feld: category_id
     */
    public ?int $categoryId = null;

    /**
     * Artikeltyp: Produkt oder Dienstleistung.
     *
     * Billomat-Feld: type
     * Typ: ENUM (PRODUCT/SERVICE)
     */
    public ?ArticleType $type = null;

    /**
     * Preisbasis des Einkaufspreises (netto oder brutto).
     *
     * Billomat-Feld: purchase_price_net_gross
     * Typ: ALNUM („NET“, „GROSS“)
     * Default: NET
     */
    public ?NetGross $purchasePriceNetGross = null;

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [
            'number' => $this->number,
            'number_pre' => $this->numberPre,
            'number_length' => $this->numberLength,
            'title' => $this->title,
            'description' => $this->description,
            'sales_price' => $this->salesPrice,
            'currency_code' => $this->currencyCode,
            'sales_price2' => $this->salesPrice2,
            'sales_price3' => $this->salesPrice3,
            'sales_price4' => $this->salesPrice4,
            'sales_price5' => $this->salesPrice5,
            'unit' => $this->unit,
            'unit_id' => $this->unitId,
            'purchase_price' => $this->purchasePrice,
            'purchase_price_currency_code' => $this->purchasePriceCurrencyCode,
            'supplier_id' => $this->supplierId,
            'tax_id' => $this->taxId,
            'category_id' => $this->categoryId,
            'type' => $this->type?->value,
            'purchase_price_net_gross' => $this->purchasePriceNetGross?->value,
        ];

        // Null-Felder entfernen
        return array_filter($data, static fn (mixed $v): bool => null !== $v);
    }
}
