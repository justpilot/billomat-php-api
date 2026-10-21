<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\ArticleType;
use Justpilot\Billomat\Model\Enum\NetGross;

use const DATE_ATOM;

/**
 * Artikel (Article) aus der Billomat-API.
 *
 * Dieses Model bildet die Struktur von GET /articles bzw. GET /articles/{id} ab.
 * Es ist als Read-Model gedacht: Die Erstellung erfolgt über ArticleCreateOptions.
 *
 * Dokumentation:
 * https://www.billomat.com/en/api/articles/
 *
 * Generiert aus tools/codegen/definitions/articles.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class Article
{
    public function __construct(
        /** Interne Billomat-ID des Artikels. */
        public ?int $id,
        /** Erstellungszeitpunkt des Artikels. */
        public ?DateTimeImmutable $created,
        /** Vollständige Artikelnummer (Präfix + laufende Nummer). */
        public ?string $articleNumber = null,
        /** Laufende Nummer (ohne Präfix). */
        public ?int $number = null,
        /** Präfix der Artikelnummer. */
        public ?string $numberPre = null,
        /** Mindestlänge der Artikelnummer (wird mit führenden Nullen aufgefüllt). */
        public ?int $numberLength = null,
        /** Titel des Artikels. */
        public ?string $title = null,
        /** Beschreibung. */
        public ?string $description = null,
        /** Verkaufspreis. */
        public ?float $salesPrice = null,
        /**
         * Währung (ISO-Währungscode).
         *
         * Beispiel: "EUR"
         */
        public ?string $currencyCode = null,
        /**
         * Preis für Kunden der Preisgruppe 2.
         *
         * Ohne Angabe gilt der Normalpreis.
         */
        public ?float $salesPrice2 = null,
        /**
         * Preis für Kunden der Preisgruppe 3.
         *
         * Ohne Angabe gilt der Normalpreis.
         */
        public ?float $salesPrice3 = null,
        /**
         * Preis für Kunden der Preisgruppe 4.
         *
         * Ohne Angabe gilt der Normalpreis.
         */
        public ?float $salesPrice4 = null,
        /**
         * Preis für Kunden der Preisgruppe 5.
         *
         * Ohne Angabe gilt der Normalpreis.
         */
        public ?float $salesPrice5 = null,
        /**
         * Bezeichnung der Einheit.
         *
         * Besser `unitId` verwenden.
         *
         * @deprecated since 3.1 – Billomat liefert `unit` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.
         */
        public ?string $unit = null,
        /** ID der verknüpften Einheit. */
        public ?int $unitId = null,
        /** Einkaufspreis. */
        public ?float $purchasePrice = null,
        /**
         * Währung des Einkaufspreises (ISO-Währungscode).
         *
         * @deprecated since 3.1 – Billomat liefert `purchase_price_currency_code` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.
         */
        public ?string $purchasePriceCurrencyCode = null,
        /** ID des verknüpften Lieferanten. */
        public ?int $supplierId = null,
        /** ID des verknüpften Steuersatzes. */
        public ?int $taxId = null,
        /**
         * ID der Artikelkategorie.
         *
         * @deprecated since 3.1 – Billomat liefert `category_id` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.
         */
        public ?int $categoryId = null,
        /** Zeitpunkt der letzten Änderung. */
        public ?DateTimeImmutable $updated = null,
        /** Freies Zusatzfeld (siehe Custom-Meta-Attribute). */
        public ?string $customfield = null,
        /**
         * Archivierungsstatus.
         *
         * true = archiviert, false = aktiv.
         */
        public ?bool $archived = null,
        /** Artikeltyp: Produkt oder Dienstleistung. */
        public ?ArticleType $type = null,
        /** Erlöskonto. */
        public ?string $revenueAccountNumber = null,
        /** Kostenstelle. */
        public ?string $costCenter = null,
        /** Preisbasis des Einkaufspreises (netto oder brutto). */
        public ?NetGross $purchasePriceNetGross = null,
    ) {
    }

    /**
     * Hydriert das Model aus einem Billomat-Response-Array.
     *
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ScalarCaster::toIntOrNull($data['id'] ?? null),
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            articleNumber: ScalarCaster::toStringOrNull($data['article_number'] ?? null),
            number: ScalarCaster::toIntOrNull($data['number'] ?? null),
            numberPre: ScalarCaster::toStringOrNull($data['number_pre'] ?? null),
            numberLength: ScalarCaster::toIntOrNull($data['number_length'] ?? null),
            title: ScalarCaster::toStringOrNull($data['title'] ?? null),
            description: ScalarCaster::toStringOrNull($data['description'] ?? null),
            salesPrice: ScalarCaster::toFloatOrNull($data['sales_price'] ?? null),
            currencyCode: ScalarCaster::toStringOrNull($data['currency_code'] ?? null),
            salesPrice2: ScalarCaster::toFloatOrNull($data['sales_price2'] ?? null),
            salesPrice3: ScalarCaster::toFloatOrNull($data['sales_price3'] ?? null),
            salesPrice4: ScalarCaster::toFloatOrNull($data['sales_price4'] ?? null),
            salesPrice5: ScalarCaster::toFloatOrNull($data['sales_price5'] ?? null),
            unit: ScalarCaster::toStringOrNull($data['unit'] ?? null),
            unitId: ScalarCaster::toIntOrNull($data['unit_id'] ?? null),
            purchasePrice: ScalarCaster::toFloatOrNull($data['purchase_price'] ?? null),
            purchasePriceCurrencyCode: ScalarCaster::toStringOrNull($data['purchase_price_currency_code'] ?? null),
            supplierId: ScalarCaster::toIntOrNull($data['supplier_id'] ?? null),
            taxId: ScalarCaster::toIntOrNull($data['tax_id'] ?? null),
            categoryId: ScalarCaster::toIntOrNull($data['category_id'] ?? null),
            updated: ScalarCaster::toDateTimeOrNull($data['updated'] ?? null),
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
            archived: ScalarCaster::toBoolOrNull($data['archived'] ?? null),
            type: ArticleType::tryFrom(ScalarCaster::toStringOrNull($data['type'] ?? null) ?? ''),
            revenueAccountNumber: ScalarCaster::toStringOrNull($data['revenue_account_number'] ?? null),
            costCenter: ScalarCaster::toStringOrNull($data['cost_center'] ?? null),
            purchasePriceNetGross: NetGross::tryFrom(ScalarCaster::toStringOrNull($data['purchase_price_net_gross'] ?? null) ?? ''),
        );
    }

    /**
     * Exportiert das Model als Array mit Billomat-Feldnamen.
     *
     * Praktisch für Debugging oder Log-Ausgaben.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'created' => $this->created?->format(DATE_ATOM),
            'article_number' => $this->articleNumber,
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
            'updated' => $this->updated?->format(DATE_ATOM),
            'customfield' => $this->customfield,
            'archived' => $this->archived,
            'type' => $this->type?->value,
            'revenue_account_number' => $this->revenueAccountNumber,
            'cost_center' => $this->costCenter,
            'purchase_price_net_gross' => $this->purchasePriceNetGross?->value,
        ];
    }
}
