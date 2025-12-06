<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Model\Enum\InvoiceItemType;

/**
 * Repräsentiert eine Rechnungsposition (Invoice Item) aus der Billomat-API.
 *
 * Dokumentation:
 * https://www.billomat.com/api/rechnungen/positionen/
 */
final readonly class InvoiceItem
{
    /** Interne Billomat-ID der Position. */
    public ?int $id;

    /** ID der zugehörigen Rechnung. */
    public ?int $invoiceId;

    /** ID des Artikels (falls verknüpft). */
    public ?int $articleId;

    /** Laufende Positionsnummer innerhalb der Rechnung. */
    public ?int $position;

    /** Einheit (z. B. "Stück", "Stunde"). */
    public ?string $unit;

    /** Menge. */
    public float $quantity;

    /** Einzelpreis. */
    public float $unitPrice;

    /** Steuerbezeichnung (z. B. "MwSt"). */
    public ?string $taxName;

    /** Steuerrate in Prozent. */
    public ?float $taxRate;

    /** Flag, ob die Steuerrate manuell geändert wurde. */
    public ?bool $taxChangedManually;

    /** Titel der Position. */
    public ?string $title;

    /** Beschreibung der Position. */
    public ?string $description;

    /** Positionsrabatt (z. B. "10" oder "10%"). */
    public ?string $reduction;

    /** Typ der Position (Produkt/Dienstleistung). */
    public ?InvoiceItemType $type;

    /** Gesamtbetrag brutto. */
    public ?float $totalGross;

    /** Gesamtbetrag netto. */
    public ?float $totalNet;

    /** Unreduzierter Gesamtbetrag brutto. */
    public ?float $totalGrossUnreduced;

    /** Unreduzierter Gesamtbetrag netto. */
    public ?float $totalNetUnreduced;

    /** Erstellungszeitpunkt der Position. */
    public ?\DateTimeImmutable $created;

    public function __construct(
        ?int                $id,
        ?int                $invoiceId,
        ?int                $articleId,
        ?int                $position,
        ?string             $unit,
        float               $quantity,
        float               $unitPrice,
        ?string             $taxName,
        ?float              $taxRate,
        ?bool               $taxChangedManually,
        ?string             $title,
        ?string             $description,
        ?string             $reduction,
        ?InvoiceItemType    $type,
        ?float              $totalGross,
        ?float              $totalNet,
        ?float              $totalGrossUnreduced,
        ?float              $totalNetUnreduced,
        ?\DateTimeImmutable $created,
    )
    {
        $this->id = $id;
        $this->invoiceId = $invoiceId;
        $this->articleId = $articleId;
        $this->position = $position;
        $this->unit = $unit;
        $this->quantity = $quantity;
        $this->unitPrice = $unitPrice;
        $this->taxName = $taxName;
        $this->taxRate = $taxRate;
        $this->taxChangedManually = $taxChangedManually;
        $this->title = $title;
        $this->description = $description;
        $this->reduction = $reduction;
        $this->type = $type;
        $this->totalGross = $totalGross;
        $this->totalNet = $totalNet;
        $this->totalGrossUnreduced = $totalGrossUnreduced;
        $this->totalNetUnreduced = $totalNetUnreduced;
        $this->created = $created;
    }

    /**
     * Hydriert eine InvoiceItem-Instanz aus einem Billomat-Array.
     *
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $created = null;
        if (!empty($data['created'])) {
            try {
                $created = new \DateTimeImmutable((string)$data['created']);
            } catch (\Throwable) {
                $created = null;
            }
        }

        return new self(
            id: isset($data['id']) ? (int)$data['id'] : null,
            invoiceId: isset($data['invoice_id']) ? (int)$data['invoice_id'] : null,
            articleId: isset($data['article_id']) && $data['article_id'] !== ''
                ? (int)$data['article_id']
                : null,
            position: isset($data['position']) ? (int)$data['position'] : null,
            unit: $data['unit'] ?? null,
            quantity: isset($data['quantity']) ? (float)$data['quantity'] : 0.0,
            unitPrice: isset($data['unit_price']) ? (float)$data['unit_price'] : 0.0,
            taxName: $data['tax_name'] ?? null,
            taxRate: isset($data['tax_rate']) ? (float)$data['tax_rate'] : null,
            taxChangedManually: isset($data['tax_changed_manually'])
                ? filter_var($data['tax_changed_manually'], FILTER_VALIDATE_BOOLEAN)
                : null,
            title: $data['title'] ?? null,
            description: $data['description'] ?? null,
            reduction: $data['reduction'] ?? null,
            type: isset($data['type']) ? InvoiceItemType::tryFrom((string)$data['type']) : null,
            totalGross: isset($data['total_gross']) ? (float)$data['total_gross'] : null,
            totalNet: isset($data['total_net']) ? (float)$data['total_net'] : null,
            totalGrossUnreduced: isset($data['total_gross_unreduced'])
                ? (float)$data['total_gross_unreduced']
                : null,
            totalNetUnreduced: isset($data['total_net_unreduced'])
                ? (float)$data['total_net_unreduced']
                : null,
            created: $created,
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