<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\InvoiceItemType;

use const DATE_ATOM;

/**
 * Repräsentiert eine Gutschriftsposition aus der Billomat-API.
 *
 * Dieses Model bildet die Struktur von GET /credit-note-items?credit_note_id={id} bzw. GET /credit-note-items/{id} ab.
 *
 * Dokumentation:
 * https://www.billomat.com/api/gutschriften/positionen/
 *
 * Generiert aus tools/codegen/definitions/credit-note-items.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class CreditNoteItem
{
    public function __construct(
        /** Interne Billomat-ID der Position. */
        public ?int $id,
        /** ID des zugehörigen Dokuments. */
        public ?int $creditNoteId,
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
        public float $unitPrice,
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
        public ?InvoiceItemType $type,
        /** Gesamtbetrag brutto. */
        public ?float $totalGross,
        /** Gesamtbetrag netto. */
        public ?float $totalNet,
        /** Unreduzierter Gesamtbetrag brutto. */
        public ?float $totalGrossUnreduced,
        /** Unreduzierter Gesamtbetrag netto. */
        public ?float $totalNetUnreduced,
        /**
         * Erstellungszeitpunkt der Position.
         *
         * @deprecated since 3.1 – Billomat liefert `created` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.
         */
        public ?DateTimeImmutable $created,
        /** Freies Zusatzfeld (siehe Custom-Meta-Attribute). */
        public ?string $customfield = null,
        /** Steuerrate bei Reverse-Charge in Prozent. */
        public ?float $taxRateReverseCharge = null,
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
            creditNoteId: ScalarCaster::toIntOrNull($data['credit_note_id'] ?? null),
            articleId: ScalarCaster::toIntOrNull($data['article_id'] ?? null),
            position: ScalarCaster::toIntOrNull($data['position'] ?? null),
            unit: ScalarCaster::toStringOrNull($data['unit'] ?? null),
            quantity: ScalarCaster::toFloatOrNull($data['quantity'] ?? null) ?? 0.0,
            unitPrice: ScalarCaster::toFloatOrNull($data['unit_price'] ?? null) ?? 0.0,
            taxName: ScalarCaster::toStringOrNull($data['tax_name'] ?? null),
            taxRate: ScalarCaster::toFloatOrNull($data['tax_rate'] ?? null),
            taxChangedManually: ScalarCaster::toBoolOrNull($data['tax_changed_manually'] ?? null),
            title: ScalarCaster::toStringOrNull($data['title'] ?? null),
            description: ScalarCaster::toStringOrNull($data['description'] ?? null),
            reduction: ScalarCaster::toStringOrNull($data['reduction'] ?? null),
            type: InvoiceItemType::tryFrom(ScalarCaster::toStringOrNull($data['type'] ?? null) ?? ''),
            totalGross: ScalarCaster::toFloatOrNull($data['total_gross'] ?? null),
            totalNet: ScalarCaster::toFloatOrNull($data['total_net'] ?? null),
            totalGrossUnreduced: ScalarCaster::toFloatOrNull($data['total_gross_unreduced'] ?? null),
            totalNetUnreduced: ScalarCaster::toFloatOrNull($data['total_net_unreduced'] ?? null),
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
            taxRateReverseCharge: ScalarCaster::toFloatOrNull($data['tax_rate_reverse_charge'] ?? null),
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
            'credit_note_id' => $this->creditNoteId,
            'article_id' => $this->articleId,
            'position' => $this->position,
            'unit' => $this->unit,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice,
            'tax_name' => $this->taxName,
            'tax_rate' => $this->taxRate,
            'tax_changed_manually' => $this->taxChangedManually,
            'title' => $this->title,
            'description' => $this->description,
            'reduction' => $this->reduction,
            'type' => $this->type?->value,
            'total_gross' => $this->totalGross,
            'total_net' => $this->totalNet,
            'total_gross_unreduced' => $this->totalGrossUnreduced,
            'total_net_unreduced' => $this->totalNetUnreduced,
            'created' => $this->created?->format(DATE_ATOM),
            'customfield' => $this->customfield,
            'tax_rate_reverse_charge' => $this->taxRateReverseCharge,
        ];
    }
}
