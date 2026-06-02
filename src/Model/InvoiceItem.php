<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\InvoiceItemType;

use const DATE_ATOM;

/**
 * Repräsentiert eine Rechnungsposition (Invoice Item) aus der Billomat-API.
 *
 * Dokumentation:
 * https://www.billomat.com/api/rechnungen/positionen/
 */
final readonly class InvoiceItem
{
    public function __construct(
        /** Interne Billomat-ID der Position. */
        public ?int $id,
        /** ID der zugehörigen Rechnung. */
        public ?int $invoiceId,
        /** ID des Artikels (falls verknüpft). */
        public ?int $articleId,
        /** Laufende Positionsnummer innerhalb der Rechnung. */
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
        /** Flag, ob die Steuerrate manuell geändert wurde. */
        public ?bool $taxChangedManually,
        /** Titel der Position. */
        public ?string $title,
        /** Beschreibung der Position. */
        public ?string $description,
        /** Positionsrabatt (z. B. "10" oder "10%"). */
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
        /** Erstellungszeitpunkt der Position. */
        public ?DateTimeImmutable $created
    ) {
    }

    /**
     * Hydriert eine InvoiceItem-Instanz aus einem Billomat-Array.
     *
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ScalarCaster::toIntOrNull($data['id'] ?? null),
            invoiceId: ScalarCaster::toIntOrNull($data['invoice_id'] ?? null),
            articleId: ScalarCaster::toIntOrNull($data['article_id'] ?? null),
            position: ScalarCaster::toIntOrNull($data['position'] ?? null),
            unit: ScalarCaster::toStringOrNull($data['unit'] ?? null),
            quantity: isset($data['quantity']) ? (float) $data['quantity'] : 0.0,
            unitPrice: isset($data['unit_price']) ? (float) $data['unit_price'] : 0.0,
            taxName: ScalarCaster::toStringOrNull($data['tax_name'] ?? null),
            taxRate: ScalarCaster::toFloatOrNull($data['tax_rate'] ?? null),
            taxChangedManually: ScalarCaster::toBoolOrNull($data['tax_changed_manually'] ?? null),
            title: ScalarCaster::toStringOrNull($data['title'] ?? null),
            description: ScalarCaster::toStringOrNull($data['description'] ?? null),
            reduction: ScalarCaster::toStringOrNull($data['reduction'] ?? null),
            type: isset($data['type']) ? InvoiceItemType::tryFrom((string) $data['type']) : null,
            totalGross: ScalarCaster::toFloatOrNull($data['total_gross'] ?? null),
            totalNet: ScalarCaster::toFloatOrNull($data['total_net'] ?? null),
            totalGrossUnreduced: ScalarCaster::toFloatOrNull($data['total_gross_unreduced'] ?? null),
            totalNetUnreduced: ScalarCaster::toFloatOrNull($data['total_net_unreduced'] ?? null),
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
        );
    }

    /**
     * Exportiert die Position als Array mit Billomat-Feldnamen.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'invoice_id' => $this->invoiceId,
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
        ];
    }
}
