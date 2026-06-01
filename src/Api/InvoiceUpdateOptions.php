<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use DateTimeImmutable;
use Justpilot\Billomat\Model\Enum\NetGross;
use Justpilot\Billomat\Model\Enum\SupplyDateType;

/**
 * Typisierter Payload für PUT /invoices/{id} (Rechnung bearbeiten).
 *
 * Laut Billomat:
 * - Nur im Status DRAFT bearbeitbar.
 * - `clientId` / `contactId` können nur im DRAFT-Status geändert werden.
 * - Positionen und Kommentare nicht hier, sondern über die jeweiligen
 *   Sub-Ressourcen (/invoice-items, /invoice-comments).
 *
 * Dokumentation: https://www.billomat.com/en/api/invoices/
 */
final class InvoiceUpdateOptions
{
    /**
     * ID des Kunden (nur im DRAFT änderbar).
     *
     * Billomat-Feld: client_id
     */
    public ?int $clientId = null;

    /**
     * ID des Kontakts.
     *
     * Billomat-Feld: contact_id
     */
    public ?int $contactId = null;

    /**
     * Vollständige (überschriebene) Rechnungsadresse.
     *
     * Billomat-Feld: address
     */
    public ?string $address = null;

    /** Präfix der Rechnungsnummer. */
    public ?string $numberPre = null;

    /** Laufende Nummer (ohne Präfix). */
    public ?int $number = null;

    /** Mindestlänge der Rechnungsnummer. */
    public ?int $numberLength = null;

    /**
     * Rechnungsdatum.
     *
     * Billomat-Feld: date
     */
    public ?DateTimeImmutable $date = null;

    /**
     * Liefer-/Leistungsdatum.
     *
     * Billomat-Feld: supply_date
     */
    public ?DateTimeImmutable $supplyDate = null;

    /**
     * Typ des Liefer-/Leistungsdatums.
     *
     * Billomat-Feld: supply_date_type
     */
    public ?SupplyDateType $supplyDateType = null;

    /**
     * Tage bis Fälligkeit.
     *
     * Billomat-Feld: due_days
     */
    public ?int $dueDays = null;

    /**
     * Fälligkeitsdatum.
     *
     * Billomat-Feld: due_date
     */
    public ?DateTimeImmutable $dueDate = null;

    /**
     * Skonto in Prozent.
     *
     * Billomat-Feld: discount_rate
     */
    public ?float $discountRate = null;

    /**
     * Skontofrist in Tagen.
     *
     * Billomat-Feld: discount_days
     */
    public ?int $discountDays = null;

    /**
     * Skontodatum.
     *
     * Billomat-Feld: discount_date
     */
    public ?DateTimeImmutable $discountDate = null;

    /** Dokumentenüberschrift. */
    public ?string $title = null;

    /** Bezeichnung / Label. */
    public ?string $label = null;

    /** Einleitungstext. */
    public ?string $intro = null;

    /** Anmerkungstext. */
    public ?string $note = null;

    /** Rabatt (z. B. "10" oder "10%"). */
    public ?string $reduction = null;

    /** Preisbasis (NET/GROSS/SETTINGS). */
    public ?NetGross $netGross = null;

    /** Währungscode (z. B. EUR). */
    public ?string $currencyCode = null;

    /** Währungskurs. */
    public ?float $quote = null;

    /** Zahlungsarten (CSV). */
    public ?string $paymentTypes = null;

    /** ID einer korrigierten Rechnung. */
    public ?int $invoiceId = null;

    /** ID des Quell-Angebots. */
    public ?int $offerId = null;

    /** ID der Quell-Auftragsbestätigung. */
    public ?int $confirmationId = null;

    /** ID der Quell-Abo-Rechnung. */
    public ?int $recurringId = null;

    /** ID eines Freitext-Bausteins. */
    public ?int $freeTextId = null;

    /** Vorlagen-ID für die PDF-Erzeugung. */
    public ?int $templateId = null;

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [
            'client_id' => $this->clientId,
            'contact_id' => $this->contactId,
            'address' => $this->address,
            'number_pre' => $this->numberPre,
            'number' => $this->number,
            'number_length' => $this->numberLength,
            'date' => $this->date?->format('Y-m-d'),
            'supply_date' => $this->supplyDate?->format('Y-m-d'),
            'supply_date_type' => $this->supplyDateType?->value,
            'due_days' => $this->dueDays,
            'due_date' => $this->dueDate?->format('Y-m-d'),
            'discount_rate' => $this->discountRate,
            'discount_days' => $this->discountDays,
            'discount_date' => $this->discountDate?->format('Y-m-d'),
            'title' => $this->title,
            'label' => $this->label,
            'intro' => $this->intro,
            'note' => $this->note,
            'reduction' => $this->reduction,
            'net_gross' => $this->netGross?->value,
            'currency_code' => $this->currencyCode,
            'quote' => $this->quote,
            'payment_types' => $this->paymentTypes,
            'invoice_id' => $this->invoiceId,
            'offer_id' => $this->offerId,
            'confirmation_id' => $this->confirmationId,
            'recurring_id' => $this->recurringId,
            'free_text_id' => $this->freeTextId,
            'template_id' => $this->templateId,
        ];

        return array_filter($data, static fn (int|string|float|null $v): bool => null !== $v);
    }
}
