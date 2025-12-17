<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\NetGross;
use Justpilot\Billomat\Model\Enum\SupplyDateType;

/**
 * Typisierter Payload für PUT /invoices/{id} (Rechnung bearbeiten).
 *
 * Laut Billomat:
 * - Nur im Status DRAFT bearbeitbar.
 * - Positionen und Kommentare nicht hier, sondern über die jeweilige Ressource.
 *
 * Dokumentation:
 * https://www.billomat.com/api/rechnungen/
 */
final class InvoiceUpdateOptions
{
    /**
     * Rechnungsdatum.
     *
     * Billomat-Feld: date
     * Typ: DATE
     */
    public ?\DateTimeImmutable $date = null;

    /**
     * Liefer-/Leistungsdatum.
     *
     * Billomat-Feld: supply_date
     * Typ: MIXED (DATE/ALNUM)
     */
    public ?\DateTimeImmutable $supplyDate = null;

    /**
     * Typ des Liefer-/Leistungsdatums.
     *
     * Billomat-Feld: supply_date_type
     * Typ: ENUM (SUPPLY_DATE, DELIVERY_DATE, SUPPLY_TEXT, DELIVERY_TEXT)
     */
    public ?SupplyDateType $supplyDateType = null;

    /**
     * Tage bis Fälligkeit.
     *
     * Billomat-Feld: due_days
     * Typ: INT
     */
    public ?int $dueDays = null;

    /**
     * Fälligkeitsdatum.
     *
     * Billomat-Feld: due_date
     * Typ: DATE
     */
    public ?\DateTimeImmutable $dueDate = null;

    /**
     * Dokumentenüberschrift.
     *
     * Billomat-Feld: title
     * Typ: ALNUM
     */
    public ?string $title = null;

    /**
     * Bezeichnung.
     *
     * Billomat-Feld: label
     * Typ: ALNUM
     */
    public ?string $label = null;

    /**
     * Einleitungstext.
     *
     * Billomat-Feld: intro
     * Typ: ALNUM
     */
    public ?string $intro = null;

    /**
     * Anmerkungstext.
     *
     * Billomat-Feld: note
     * Typ: ALNUM
     */
    public ?string $note = null;

    /**
     * Rabatt (z.B. "10" oder "10%").
     *
     * Billomat-Feld: reduction
     * Typ: ALNUM
     */
    public ?string $reduction = null;

    /**
     * Preisbasis (NET/GROSS/SETTINGS).
     *
     * Billomat-Feld: net_gross
     * Typ: ENUM
     */
    public ?NetGross $netGross = null;

    /**
     * Währungscode (z.B. EUR).
     *
     * Billomat-Feld: currency_code
     * Typ: ISO-Währungscode
     */
    public ?string $currencyCode = null;

    /**
     * Währungskurs.
     *
     * Billomat-Feld: quote
     * Typ: FLOAT
     */
    public ?float $quote = null;

    /**
     * Zahlungsarten (kommasepariert).
     *
     * Billomat-Feld: payment_types
     * Typ: ALNUM
     */
    public ?string $paymentTypes = null;

    /**
     * Serialisiert zu Billomat-Feldnamen.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [
            'date' => $this->date?->format('Y-m-d'),
            'supply_date' => $this->supplyDate?->format('Y-m-d'),
            'supply_date_type' => $this->supplyDateType?->value,
            'due_days' => $this->dueDays,
            'due_date' => $this->dueDate?->format('Y-m-d'),
            'title' => $this->title,
            'label' => $this->label,
            'intro' => $this->intro,
            'note' => $this->note,
            'reduction' => $this->reduction,
            'net_gross' => $this->netGross?->value,
            'currency_code' => $this->currencyCode,
            'quote' => $this->quote,
            'payment_types' => $this->paymentTypes,
        ];

        return array_filter($data, static fn($v) => $v !== null);
    }
}