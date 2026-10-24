<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use DateTimeImmutable;
use Justpilot\Billomat\Model\Enum\NetGross;
use Justpilot\Billomat\Model\Enum\RecurringAction;
use Justpilot\Billomat\Model\Enum\RecurringCycle;
use Justpilot\Billomat\Model\Enum\SupplyDateType;

/**
 * Typisierter Payload für PUT /recurrings/{id}.
 *
 * Nur gesetzte (nicht-null) Felder werden serialisiert (Partial Update).
 * Positionen lassen sich hier nicht bearbeiten, dafür ist RecurringItemsApi zuständig.
 *
 * Doku:
 * https://www.billomat.com/api/abo-rechnungen/
 *
 * Generiert aus tools/codegen/definitions/recurrings.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final class RecurringUpdateOptions
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
     * Komplette Adresse.
     *
     * Wird ein leerer Wert übergeben, wird die aktuelle Kunden-Adresse verwendet.
     *
     * Billomat-Feld: address
     * Typ: ALNUM
     */
    public ?string $address = null;

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
     * Tage bis zur Fälligkeit der erzeugten Rechnungen.
     *
     * Billomat-Feld: due_days
     * Typ: INT
     */
    public ?int $dueDays = null;

    /**
     * Skonto in Prozent.
     *
     * Billomat-Feld: discount_rate
     * Typ: FLOAT
     */
    public ?int $discountRate = null;

    /**
     * Skontofrist in Tagen.
     *
     * Billomat-Feld: discount_days
     * Typ: INT
     */
    public ?int $discountDays = null;

    /**
     * Name der Abo-Rechnung (nur intern sichtbar).
     *
     * Billomat-Feld: name
     * Typ: ALNUM
     */
    public ?string $name = null;

    /**
     * Präfix der Rechnungsnummer.
     *
     * Billomat-Feld: number_pre
     */
    public ?string $numberPre = null;

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
     * Währung (ISO-Währungscode).
     *
     * Billomat-Feld: currency_code
     * Typ: ISO-Währungscode
     */
    public ?string $currencyCode = null;

    /**
     * Preisbasis (Brutto- oder Netto-Preise).
     *
     * Billomat-Feld: net_gross
     * Typ: ALNUM („NET“, „GROSS“)
     */
    public ?NetGross $netGross = null;

    /**
     * Währungskurs (für Umrechnung in die Standard-Währung).
     *
     * Billomat-Feld: quote
     * Typ: FLOAT
     */
    public ?float $quote = null;

    /**
     * Akzeptierte Zahlungsarten (kommasepariert).
     *
     * Billomat-Feld: payment_types
     * Typ: ALNUM
     */
    public ?string $paymentTypes = null;

    /**
     * Aktion, die bei jedem Lauf ausgeführt wird.
     *
     * Billomat-Feld: action
     * Typ: ALNUM
     */
    public ?RecurringAction $action = null;

    /**
     * Anzahl der Intervall-Einheiten zwischen zwei Läufen.
     *
     * Billomat-Feld: cycle_number
     * Typ: INT
     */
    public ?int $cycleNumber = null;

    /**
     * Intervall-Einheit (z. B. monatlich).
     *
     * Billomat-Feld: cycle
     * Typ: ALNUM
     */
    public ?RecurringCycle $cycle = null;

    /**
     * Stunde, zu der die Rechnung erzeugt wird.
     *
     * Billomat-Feld: hour
     * Typ: INT
     */
    public ?int $hour = null;

    /**
     * Startdatum.
     *
     * Format: YYYY-MM-DD
     *
     * Billomat-Feld: start_date
     * Typ: DATE
     */
    public ?DateTimeImmutable $startDate = null;

    /**
     * Enddatum.
     *
     * Format: YYYY-MM-DD
     *
     * Billomat-Feld: end_date
     * Typ: DATE
     */
    public ?DateTimeImmutable $endDate = null;

    /**
     * Datum der nächsten Rechnung.
     *
     * Format: YYYY-MM-DD
     *
     * Billomat-Feld: next_creation_date
     * Typ: DATE
     */
    public ?DateTimeImmutable $nextCreationDate = null;

    /**
     * Anzahl der Läufe, nach denen die Abo-Rechnung endet.
     *
     * Billomat-Feld: iterations
     */
    public ?int $iterations = null;

    /**
     * Rabatt (absolut oder als Prozentwert, z. B. "10" oder "10%").
     *
     * Billomat-Feld: reduction
     * Typ: ALNUM
     */
    public ?string $reduction = null;

    /**
     * Absender-Adresse der E-Mail.
     *
     * Billomat-Feld: email_sender
     * Typ: ALNUM
     */
    public ?string $emailSender = null;

    /**
     * Betreff der E-Mail.
     *
     * Billomat-Feld: email_subject
     * Typ: ALNUM
     */
    public ?string $emailSubject = null;

    /**
     * Text der E-Mail.
     *
     * Billomat-Feld: email_message
     * Typ: ALNUM
     */
    public ?string $emailMessage = null;

    /**
     * ID der E-Mail-Vorlage.
     *
     * Billomat-Feld: email_template_id
     * Typ: INT
     */
    public ?int $emailTemplateId = null;

    /**
     * ID des Freitextes zur Belegung von title, label, intro und note.
     *
     * Billomat-Feld: free_text_id
     * Typ: INT
     */
    public ?int $freeTextId = null;

    /**
     * ID der Vorlage für die erzeugten Rechnungen.
     *
     * Billomat-Feld: template_id
     * Typ: INT
     */
    public ?int $templateId = null;

    /**
     * Dateiname des PDF-Anhangs.
     *
     * Billomat-Feld: email_filename
     * Typ: ALNUM
     */
    public ?string $emailFilename = null;

    /**
     * E-Mail zusätzlich als BCC an den Absender senden?
     *
     * Billomat-Feld: email_bcc
     * Typ: BOOL
     */
    public ?bool $emailBcc = null;

    /**
     * Brief farbig drucken?
     *
     * Billomat-Feld: letter_color
     * Typ: BOOL
     */
    public ?bool $letterColor = null;

    /**
     * Brief beidseitig drucken?
     *
     * Billomat-Feld: letter_duplex
     * Typ: BOOL
     */
    public ?bool $letterDuplex = null;

    /**
     * Papiergewicht des Briefs in g/m².
     *
     * Billomat-Feld: letter_paper_weight
     * Typ: INT
     */
    public ?int $letterPaperWeight = null;

    /**
     * ID des Quell-Angebots.
     *
     * Billomat-Feld: offer_id
     * Typ: INT
     */
    public ?int $offerId = null;

    /**
     * ID der Quell-Auftragsbestätigung.
     *
     * Billomat-Feld: confirmation_id
     * Typ: INT
     */
    public ?int $confirmationId = null;

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
            'supply_date' => $this->supplyDate?->format('Y-m-d'),
            'supply_date_type' => $this->supplyDateType?->value,
            'due_days' => $this->dueDays,
            'discount_rate' => $this->discountRate,
            'discount_days' => $this->discountDays,
            'name' => $this->name,
            'title' => $this->title,
            'label' => $this->label,
            'intro' => $this->intro,
            'note' => $this->note,
            'reduction' => $this->reduction,
            'currency_code' => $this->currencyCode,
            'net_gross' => $this->netGross?->value,
            'quote' => $this->quote,
            'payment_types' => $this->paymentTypes,
            'action' => $this->action?->value,
            'cycle_number' => $this->cycleNumber,
            'cycle' => $this->cycle?->value,
            'hour' => $this->hour,
            'start_date' => $this->startDate?->format('Y-m-d'),
            'end_date' => $this->endDate?->format('Y-m-d'),
            'iterations' => $this->iterations,
            'email_sender' => $this->emailSender,
            'email_subject' => $this->emailSubject,
            'email_message' => $this->emailMessage,
            'email_template_id' => $this->emailTemplateId,
            'free_text_id' => $this->freeTextId,
            'template_id' => $this->templateId,
            'next_creation_date' => $this->nextCreationDate?->format('Y-m-d'),
            'email_filename' => $this->emailFilename,
            'email_bcc' => $this->emailBcc,
            'letter_color' => $this->letterColor,
            'letter_duplex' => $this->letterDuplex,
            'letter_paper_weight' => $this->letterPaperWeight,
            'offer_id' => $this->offerId,
            'confirmation_id' => $this->confirmationId,
        ];

        // Null-Felder entfernen
        return array_filter($data, static fn (mixed $v): bool => null !== $v);
    }
}
