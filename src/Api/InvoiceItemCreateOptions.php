<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Typisierter Payload für eine Rechnungsposition (Invoice Item).
 *
 * Wird typischerweise in Kombination mit InvoiceCreateOptions verwendet.
 * Die Felder orientieren sich an der Billomat-Dokumentation für Invoice-Items.
 */
final class InvoiceItemCreateOptions
{
    /**
     * ID des Artikels (optional).
     *
     * Billomat-Feld: article_id
     * Typ: INT
     */
    public ?int $articleId = null;

    /**
     * Titel / Bezeichnung der Position.
     *
     * Billomat-Feld: title
     * Typ: ALNUM
     */
    public ?string $title = null;

    /**
     * Beschreibung der Position.
     *
     * Billomat-Feld: description
     * Typ: ALNUM
     */
    public ?string $description = null;

    /**
     * Menge.
     *
     * Billomat-Feld: quantity
     * Typ: FLOAT
     */
    public float $quantity;

    /**
     * Einzelpreis (Netto oder Brutto, abhängig von der Rechnung).
     *
     * Billomat-Feld: unit_price
     * Typ: FLOAT
     */
    public float $unitPrice;

    /**
     * Einheit (z. B. "Stunde", "Stück").
     *
     * Billomat-Feld: unit
     * Typ: ALNUM
     */
    public ?string $unit = null;

    /**
     * Steuername (z. B. "USt 19%").
     *
     * Billomat-Feld: tax_name
     * Typ: ALNUM
     */
    public ?string $taxName = null;

    /**
     * Steuersatz in Prozent (z. B. 19.0).
     *
     * Billomat-Feld: tax_rate
     * Typ: FLOAT
     */
    public ?float $taxRate = null;

    /**
     * Positionsrabatt (z. B. "10" oder "10%").
     *
     * Billomat-Feld: reduction
     * Typ: ALNUM
     */
    public ?string $reduction = null;

    /**
     * Sortierreihenfolge der Position.
     *
     * Billomat-Feld: position
     * Typ: INT
     */
    public ?int $position = null;

    public function __construct(
        float $quantity,
        float $unitPrice,
    )
    {
        $this->quantity = $quantity;
        $this->unitPrice = $unitPrice;
    }

    /**
     * Serialisiert die Positionsdaten in ein Billomat-Payload-Array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [
            'article_id' => $this->articleId,
            'title' => $this->title,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice,
            'unit' => $this->unit,
            'tax_name' => $this->taxName,
            'tax_rate' => $this->taxRate,
            'reduction' => $this->reduction,
            'position' => $this->position,
        ];

        // Null-Werte raus, aber quantity/unit_price bleiben drin
        return array_filter(
            $data,
            static fn($v, $k) => $v !== null || \in_array($k, ['quantity', 'unit_price'], true),
            ARRAY_FILTER_USE_BOTH
        );
    }
}