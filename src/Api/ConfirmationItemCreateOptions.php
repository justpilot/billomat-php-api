<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\InvoiceItemType;

/**
 * Typisierter Payload für POST /confirmation-items gemäß Billomat-API
 * sowie für eingebettete Positionen beim Anlegen des Dokuments.
 *
 * Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren ignoriert.
 *
 * Doku:
 * https://www.billomat.com/en/api/confirmations/items/
 *
 * Generiert aus tools/codegen/definitions/confirmation-items.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final class ConfirmationItemCreateOptions
{
    public function __construct(
        /**
         * Menge.
         *
         * Billomat-Feld: quantity
         */
        public float $quantity,
        /**
         * Einzelpreis.
         *
         * Billomat-Feld: unit_price
         */
        public float $unitPrice,
    ) {
    }

    /**
     * ID des Artikels (falls verknüpft).
     *
     * Billomat-Feld: article_id
     */
    public ?int $articleId = null;

    /**
     * Laufende Positionsnummer innerhalb des Dokuments.
     *
     * Wird beim Anlegen normalerweise automatisch vergeben (anhängen ans Ende).
     *
     * Billomat-Feld: position
     */
    public ?int $position = null;

    /**
     * Einheit (z. B. "Stück", "Stunde").
     *
     * Billomat-Feld: unit
     */
    public ?string $unit = null;

    /**
     * Steuerbezeichnung (z. B. "MwSt").
     *
     * Billomat-Feld: tax_name
     */
    public ?string $taxName = null;

    /**
     * Steuerrate in Prozent.
     *
     * Billomat-Feld: tax_rate
     */
    public ?float $taxRate = null;

    /**
     * Flag, ob die Steuerrate manuell geändert wurde.
     *
     * Laut Doku Pflicht, *wenn* nicht der Standard-Steuersatz angewendet werden soll.
     *
     * Billomat-Feld: tax_changed_manually
     */
    public ?bool $taxChangedManually = null;

    /**
     * Titel der Position.
     *
     * Billomat-Feld: title
     */
    public ?string $title = null;

    /**
     * Beschreibung der Position.
     *
     * Billomat-Feld: description
     */
    public ?string $description = null;

    /**
     * Positionsrabatt (absolut oder als Prozentwert, z. B. "10" oder "10%").
     *
     * Billomat-Feld: reduction
     */
    public ?string $reduction = null;

    /**
     * Typ der Position (Produkt/Dienstleistung).
     *
     * Billomat-Feld: type
     */
    public ?InvoiceItemType $type = null;

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [
            'type' => $this->type?->value,
            'article_id' => $this->articleId,
            'title' => $this->title,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice,
            'unit' => $this->unit,
            'tax_name' => $this->taxName,
            'tax_rate' => $this->taxRate,
            'tax_changed_manually' => $this->taxChangedManually,
            'reduction' => $this->reduction,
            'position' => $this->position,
        ];

        // Null-Felder entfernen
        return array_filter($data, static fn (mixed $v): bool => null !== $v);
    }
}
