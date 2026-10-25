<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\InvoiceItemType;

/**
 * Repräsentiert eine Position einer Auftragsbestätigung aus der Billomat-API.
 *
 * Dieses Model bildet die Struktur von GET /confirmation-items?confirmation_id={id} bzw. GET /confirmation-items/{id} ab.
 *
 * Dokumentation:
 * https://www.billomat.com/en/api/confirmations/items/
 *
 * Generiert aus tools/codegen/definitions/confirmation-items.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class ConfirmationItem
{
    public function __construct(
        /** Interne Billomat-ID der Position. */
        public ?int $id,
        /** ID des zugehörigen Dokuments. */
        public ?int $confirmationId,
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
        /** Freies Zusatzfeld (siehe Custom-Meta-Attribute). */
        public ?string $customfield = null,
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
            confirmationId: ScalarCaster::toIntOrNull($data['confirmation_id'] ?? null),
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
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
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
            'confirmation_id' => $this->confirmationId,
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
            'customfield' => $this->customfield,
        ];
    }
}
