<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\ItemType;

/**
 * Repräsentiert eine Position einer Abo-Rechnung aus der Billomat-API.
 *
 * Dieses Model bildet die Struktur von GET /recurring-items?recurring_id={id} bzw. GET /recurring-items/{id} ab.
 *
 * Dokumentation:
 * https://www.billomat.com/api/abo-rechnungen/positionen/
 *
 * Generiert aus tools/codegen/definitions/recurring-items.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class RecurringItem
{
    /** Billomat-Felder, die das Model abbildet. */
    private const array FIELDS = [
        'id' => true,
        'recurring_id' => true,
        'article_id' => true,
        'position' => true,
        'unit' => true,
        'quantity' => true,
        'unit_price' => true,
        'tax_name' => true,
        'tax_rate' => true,
        'tax_changed_manually' => true,
        'title' => true,
        'description' => true,
        'reduction' => true,
        'type' => true,
        'total_gross' => true,
        'total_net' => true,
        'total_gross_unreduced' => true,
        'total_net_unreduced' => true,
        'customfield' => true,
    ];

    /** @param array<string, mixed> $extra */
    public function __construct(
        /** Interne Billomat-ID der Position. */
        public ?int $id,
        /** ID des zugehörigen Dokuments. */
        public ?int $recurringId,
        /** ID des Artikels (falls verknüpft). */
        public ?int $articleId,
        /**
         * Laufende Positionsnummer innerhalb des Dokuments.
         *
         * Wird beim Anlegen normalerweise automatisch vergeben (anhängen ans Ende).
         */
        public ?int $position,
        /** Einheit (z. B. "Stück", "Stunde"). */
        public ?string $unit,
        /** Menge. */
        public float $quantity,
        /** Einzelpreis. */
        public Money $unitPrice,
        /** Steuerbezeichnung (z. B. "MwSt"). */
        public ?string $taxName,
        /** Steuerrate in Prozent. */
        public ?float $taxRate,
        /**
         * Flag, ob die Steuerrate manuell geändert wurde.
         *
         * Laut Doku Pflicht, *wenn* nicht der Standard-Steuersatz angewendet werden soll.
         */
        public ?bool $taxChangedManually,
        /** Titel der Position. */
        public ?string $title,
        /** Beschreibung der Position. */
        public ?string $description,
        /** Positionsrabatt (absolut oder als Prozentwert, z. B. "10" oder "10%"). */
        public ?string $reduction,
        /** Typ der Position (Produkt/Dienstleistung). */
        public ?ItemType $type,
        /** Gesamtbetrag brutto. */
        public ?Money $totalGross,
        /** Gesamtbetrag netto. */
        public ?Money $totalNet,
        /** Unreduzierter Gesamtbetrag brutto. */
        public ?Money $totalGrossUnreduced,
        /** Unreduzierter Gesamtbetrag netto. */
        public ?Money $totalNetUnreduced,
        /** Freies Zusatzfeld (siehe Custom-Meta-Attribute). */
        public ?string $customfield = null,
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
            recurringId: ScalarCaster::toIntOrNull($data['recurring_id'] ?? null),
            articleId: ScalarCaster::toIntOrNull($data['article_id'] ?? null),
            position: ScalarCaster::toIntOrNull($data['position'] ?? null),
            unit: ScalarCaster::toStringOrNull($data['unit'] ?? null),
            quantity: ScalarCaster::toFloatOrNull($data['quantity'] ?? null) ?? 0.0,
            unitPrice: ScalarCaster::toMoneyOrNull($data['unit_price'] ?? null) ?? Money::zero(),
            taxName: ScalarCaster::toStringOrNull($data['tax_name'] ?? null),
            taxRate: ScalarCaster::toFloatOrNull($data['tax_rate'] ?? null),
            taxChangedManually: ScalarCaster::toBoolOrNull($data['tax_changed_manually'] ?? null),
            title: ScalarCaster::toStringOrNull($data['title'] ?? null),
            description: ScalarCaster::toStringOrNull($data['description'] ?? null),
            reduction: ScalarCaster::toStringOrNull($data['reduction'] ?? null),
            type: ItemType::tryFrom(ScalarCaster::toStringOrNull($data['type'] ?? null) ?? ''),
            totalGross: ScalarCaster::toMoneyOrNull($data['total_gross'] ?? null),
            totalNet: ScalarCaster::toMoneyOrNull($data['total_net'] ?? null),
            totalGrossUnreduced: ScalarCaster::toMoneyOrNull($data['total_gross_unreduced'] ?? null),
            totalNetUnreduced: ScalarCaster::toMoneyOrNull($data['total_net_unreduced'] ?? null),
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
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
            'recurring_id' => $this->recurringId,
            'article_id' => $this->articleId,
            'position' => $this->position,
            'unit' => $this->unit,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice->toString(),
            'tax_name' => $this->taxName,
            'tax_rate' => $this->taxRate,
            'tax_changed_manually' => $this->taxChangedManually,
            'title' => $this->title,
            'description' => $this->description,
            'reduction' => $this->reduction,
            'type' => $this->type?->value,
            'total_gross' => $this->totalGross?->toString(),
            'total_net' => $this->totalNet?->toString(),
            'total_gross_unreduced' => $this->totalGrossUnreduced?->toString(),
            'total_net_unreduced' => $this->totalNetUnreduced?->toString(),
            'customfield' => $this->customfield,
        ];
    }
}
