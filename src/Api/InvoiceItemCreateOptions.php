<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\InvoiceItemType;

/**
 * Typisierter Payload für POST /invoices (invoice-items-Block)
 * bzw. POST /invoice-items.
 *
 * Dokumentation:
 * https://www.billomat.com/api/rechnungen/positionen/
 */
final class InvoiceItemCreateOptions
{
    /**
     * Typ der Position (Produkt/Dienstleistung).
     *
     * Billomat-Feld: type
     * ENUM: PRODUCT / SERVICE
     */
    public ?InvoiceItemType $type = null;

    /**
     * ID des Artikels.
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
     * Preis pro Einheit.
     *
     * Billomat-Feld: unit_price
     * Typ: FLOAT
     */
    public float $unitPrice;

    /**
     * Einheit (z. B. "Stück", "Stunde").
     *
     * Billomat-Feld: unit
     * Typ: ALNUM
     */
    public ?string $unit = null;

    /**
     * Steuerbezeichnung (z. B. "MwSt").
     *
     * Billomat-Feld: tax_name
     * Typ: ALNUM
     */
    public ?string $taxName = null;

    /**
     * Steuerrate in Prozent (z. B. 19.0).
     *
     * Billomat-Feld: tax_rate
     * Typ: FLOAT
     */
    public ?float $taxRate = null;

    /**
     * Flag, ob die Steuerrate manuell geändert wurde.
     *
     * Billomat-Feld: tax_changed_manually
     * Typ: BOOL
     *
     * Hinweis: Laut Doku Pflicht, *wenn* nicht der Standard-Steuersatz
     * angewendet werden soll.
     */
    public ?bool $taxChangedManually = null;

    /**
     * Rabatt (absolut oder als Prozentwert, z. B. "10" oder "10%").
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
     *
     * Wird bei POST /invoice-items in der API normalerweise automatisch
     * vergeben (anhängen ans Ende). Kann aber für spätere PUT-Requests
     * nützlich sein.
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

        // Null-Werte raus, aber quantity/unit_price bleiben drin
        return array_filter(
            $data,
            static fn($v, string $k) => $v !== null || \in_array($k, ['quantity', 'unit_price'], true),
            ARRAY_FILTER_USE_BOTH,
        );
    }
}