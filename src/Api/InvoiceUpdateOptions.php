<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use DateTimeImmutable;
use Justpilot\Billomat\Model\Enum\NetGross;
use Justpilot\Billomat\Model\Enum\SupplyDateType;

/**
 * Typisierter Payload für PUT /invoices/{id} (Rechnung bearbeiten).
 *
 * Nur gesetzte (nicht-null) Felder werden serialisiert (Partial Update).
 * Laut Billomat nur im Status DRAFT bearbeitbar; Positionen und Kommentare
 * werden über die Sub-Ressourcen (/invoice-items, /invoice-comments) verwaltet.
 *
 * Doku:
 * https://www.billomat.com/en/api/invoices/
 *
 * Generiert aus tools/codegen/definitions/invoices.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final class InvoiceUpdateOptions implements WriteOptions
{
    /**
     * ID des Kunden.
     *
     * Billomat-Feld: client_id
     * Typ: INT
     */
    public ?int $clientId = null;

    /**
     * ID des Kontakts.
     *
     * Billomat-Feld: contact_id
     * Typ: INT
     */
    public ?int $contactId = null;

    /**
     * Laufende Nummer (ohne Präfix).
     *
     * Billomat-Feld: number
     * Typ: INT
     */
    public ?int $number = null;

    /**
     * Präfix der Rechnungsnummer (z. B. "RE").
     *
     * Billomat-Feld: number_pre
     * Typ: ALNUM
     */
    public ?string $numberPre = null;

    /**
     * Mindestlänge der Rechnungsnummer (wird mit führenden Nullen aufgefüllt).
     *
     * Billomat-Feld: number_length
     * Typ: INT
     */
    public ?int $numberLength = null;

    /**
     * Rechnungsdatum.
     *
     * Format: YYYY-MM-DD
     *
     * Billomat-Feld: date
     * Typ: DATE
     */
    public ?DateTimeImmutable $date = null;

    /**
     * Liefer-/Leistungsdatum.
     *
     * Format: YYYY-MM-DD
     *
     * Billomat-Feld: supply_date
     * Typ: MIXED (DATE/ALNUM)
     */
    public ?DateTimeImmutable $supplyDate = null;

    /**
     * Typ des Liefer-/Leistungsdatums.
     *
     * Billomat-Feld: supply_date_type
     * Typ: ALNUM („SUPPLY_DATE“, „DELIVERY_DATE“, „SUPPLY_TEXT“, „DELIVERY_TEXT“)
     */
    public ?SupplyDateType $supplyDateType = null;

    /**
     * Fälligkeitsdatum.
     *
     * Format: YYYY-MM-DD
     *
     * Billomat-Feld: due_date
     * Typ: DATE
     */
    public ?DateTimeImmutable $dueDate = null;

    /**
     * Tage bis zur Fälligkeit.
     *
     * Billomat-Feld: due_days
     * Typ: INT
     */
    public ?int $dueDays = null;

    /**
     * Vollständige Rechnungsadresse (formatiert).
     *
     * Billomat-Feld: address
     * Typ: ALNUM
     */
    public ?string $address = null;

    /**
     * Skonto in Prozent.
     *
     * Billomat-Feld: discount_rate
     * Typ: INT
     */
    public ?float $discountRate = null;

    /**
     * Skontodatum.
     *
     * Format: YYYY-MM-DD
     *
     * Billomat-Feld: discount_date
     * Typ: DATE
     */
    public ?DateTimeImmutable $discountDate = null;

    /**
     * Skontofrist in Tagen.
     *
     * Billomat-Feld: discount_days
     * Typ: INT
     */
    public ?int $discountDays = null;

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
     * Preisbasis (NET, GROSS oder SETTINGS).
     *
     * Billomat-Feld: net_gross
     * Typ: ALNUM („NET“, „GROSS“)
     */
    public ?NetGross $netGross = null;

    /**
     * Rabatt (absolut oder als Prozentwert, z. B. "10" oder "10%").
     *
     * Billomat-Feld: reduction
     * Typ: ALNUM
     */
    public ?string $reduction = null;

    /**
     * Währung (ISO-Währungscode, z. B. "EUR").
     *
     * Billomat-Feld: currency_code
     * Typ: ISO-Währungscode
     */
    public ?string $currencyCode = null;

    /**
     * Währungskurs (für Umrechnung in die Standard-Währung).
     *
     * Billomat-Feld: quote
     * Typ: FLOAT
     */
    public ?float $quote = null;

    /**
     * ID der korrigierten Rechnung (bei Korrekturrechnung).
     *
     * Billomat-Feld: invoice_id
     * Typ: INT
     */
    public ?int $invoiceId = null;

    /**
     * ID des Angebots, aus dem die Rechnung entstanden ist.
     *
     * Billomat-Feld: offer_id
     * Typ: INT
     */
    public ?int $offerId = null;

    /**
     * ID der Auftragsbestätigung, aus der die Rechnung entstanden ist.
     *
     * Billomat-Feld: confirmation_id
     * Typ: INT
     */
    public ?int $confirmationId = null;

    /**
     * ID der Abo-Rechnung, aus der die Rechnung entstanden ist.
     *
     * Billomat-Feld: recurring_id
     * Typ: INT
     */
    public ?int $recurringId = null;

    /**
     * Akzeptierte Zahlungsarten (kommasepariert).
     *
     * Beispiel: "BANK_TRANSFER,CASH"
     *
     * Billomat-Feld: payment_types
     * Typ: ALNUM
     */
    public ?string $paymentTypes = null;

    /**
     * ID der Vorlage, mit der die Rechnung abgeschlossen wird.
     *
     * Billomat-Feld: template_id
     * Typ: INT
     */
    public ?int $templateId = null;

    /**
     * ID des Freitextes zur Belegung von title, label, intro und note.
     *
     * Billomat-Feld: free_text_id
     * Typ: INT
     */
    public ?int $freeTextId = null;

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
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

        // Null-Felder entfernen
        return array_filter($data, static fn (mixed $v): bool => null !== $v);
    }
}
