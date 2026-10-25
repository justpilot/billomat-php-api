<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\ItemType;
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
    /** Billomat-Felder, die das Model abbildet. */
    private const array FIELDS = [
        'id' => true,
        'created' => true,
        'article_number' => true,
        'number' => true,
        'number_pre' => true,
        'number_length' => true,
        'title' => true,
        'description' => true,
        'sales_price' => true,
        'currency_code' => true,
        'sales_price2' => true,
        'sales_price3' => true,
        'sales_price4' => true,
        'sales_price5' => true,
        'unit_id' => true,
        'purchase_price' => true,
        'supplier_id' => true,
        'tax_id' => true,
        'updated' => true,
        'customfield' => true,
        'archived' => true,
        'type' => true,
        'revenue_account_number' => true,
        'cost_center' => true,
        'purchase_price_net_gross' => true,
    ];

    /** @param array<string, mixed> $extra */
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
        public ?Money $salesPrice = null,
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
        public ?Money $salesPrice2 = null,
        /**
         * Preis für Kunden der Preisgruppe 3.
         *
         * Ohne Angabe gilt der Normalpreis.
         */
        public ?Money $salesPrice3 = null,
        /**
         * Preis für Kunden der Preisgruppe 4.
         *
         * Ohne Angabe gilt der Normalpreis.
         */
        public ?Money $salesPrice4 = null,
        /**
         * Preis für Kunden der Preisgruppe 5.
         *
         * Ohne Angabe gilt der Normalpreis.
         */
        public ?Money $salesPrice5 = null,
        /** ID der verknüpften Einheit. */
        public ?int $unitId = null,
        /** Einkaufspreis. */
        public ?Money $purchasePrice = null,
        /** ID des verknüpften Lieferanten. */
        public ?int $supplierId = null,
        /** ID des verknüpften Steuersatzes. */
        public ?int $taxId = null,
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
        public ?ItemType $type = null,
        /** Erlöskonto. */
        public ?string $revenueAccountNumber = null,
        /** Kostenstelle. */
        public ?string $costCenter = null,
        /** Preisbasis des Einkaufspreises (netto oder brutto). */
        public ?NetGross $purchasePriceNetGross = null,
        /**
         * Felder, die Billomat liefert, das SDK aber (noch) nicht abbildet – roh, wie in der Response.
         *
         * Neue Billomat-Felder gehen so nicht verloren; toArray() gibt sie mit aus.
         */
        public array $extra = [],
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
            salesPrice: ScalarCaster::toMoneyOrNull($data['sales_price'] ?? null),
            currencyCode: ScalarCaster::toStringOrNull($data['currency_code'] ?? null),
            salesPrice2: ScalarCaster::toMoneyOrNull($data['sales_price2'] ?? null),
            salesPrice3: ScalarCaster::toMoneyOrNull($data['sales_price3'] ?? null),
            salesPrice4: ScalarCaster::toMoneyOrNull($data['sales_price4'] ?? null),
            salesPrice5: ScalarCaster::toMoneyOrNull($data['sales_price5'] ?? null),
            unitId: ScalarCaster::toIntOrNull($data['unit_id'] ?? null),
            purchasePrice: ScalarCaster::toMoneyOrNull($data['purchase_price'] ?? null),
            supplierId: ScalarCaster::toIntOrNull($data['supplier_id'] ?? null),
            taxId: ScalarCaster::toIntOrNull($data['tax_id'] ?? null),
            updated: ScalarCaster::toDateTimeOrNull($data['updated'] ?? null),
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
            archived: ScalarCaster::toBoolOrNull($data['archived'] ?? null),
            type: ItemType::fromApi(ScalarCaster::toStringOrNull($data['type'] ?? null)),
            revenueAccountNumber: ScalarCaster::toStringOrNull($data['revenue_account_number'] ?? null),
            costCenter: ScalarCaster::toStringOrNull($data['cost_center'] ?? null),
            purchasePriceNetGross: NetGross::fromApi(ScalarCaster::toStringOrNull($data['purchase_price_net_gross'] ?? null)),
            extra: array_diff_key($data, self::FIELDS),
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
            ...$this->extra,
            'id' => $this->id,
            'created' => $this->created?->format(DATE_ATOM),
            'article_number' => $this->articleNumber,
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
            'unit_id' => $this->unitId,
            'purchase_price' => $this->purchasePrice?->toString(),
            'supplier_id' => $this->supplierId,
            'tax_id' => $this->taxId,
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
