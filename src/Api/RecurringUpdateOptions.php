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
 * Positionen lassen sich hier nicht bearbeiten, dafür ist $billomat->recurrings->items zuständig.
 *
 * Doku:
 * https://www.billomat.com/api/abo-rechnungen/
 *
 * Generiert aus tools/codegen/definitions/recurrings.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class RecurringUpdateOptions implements WriteOptions
{
    public function __construct(
        /**
         * ID des Kunden.
         *
         * Billomat-Feld: client_id
         * Typ: INT
         */
        public int|Clear|null $clientId = null,
        /**
         * ID des Kontakts.
         *
         * Billomat-Feld: contact_id
         * Typ: INT
         */
        public int|Clear|null $contactId = null,
        /**
         * Komplette Adresse.
         *
         * Wird ein leerer Wert übergeben, wird die aktuelle Kunden-Adresse verwendet.
         *
         * Billomat-Feld: address
         * Typ: ALNUM
         */
        public string|Clear|null $address = null,
        /**
         * Liefer-/Leistungsdatum.
         *
         * Format: YYYY-MM-DD
         *
         * Billomat-Feld: supply_date
         * Typ: MIXED (DATE/ALNUM)
         */
        public DateTimeImmutable|Clear|null $supplyDate = null,
        /**
         * Typ des Liefer-/Leistungsdatums.
         *
         * Billomat-Feld: supply_date_type
         * Typ: ALNUM („SUPPLY_DATE“, „DELIVERY_DATE“, „SUPPLY_TEXT“, „DELIVERY_TEXT“)
         */
        public SupplyDateType|Clear|null $supplyDateType = null,
        /**
         * Tage bis zur Fälligkeit der erzeugten Rechnungen.
         *
         * Billomat-Feld: due_days
         * Typ: INT
         */
        public int|Clear|null $dueDays = null,
        /**
         * Skonto in Prozent.
         *
         * Billomat-Feld: discount_rate
         * Typ: FLOAT
         */
        public int|Clear|null $discountRate = null,
        /**
         * Skontofrist in Tagen.
         *
         * Billomat-Feld: discount_days
         * Typ: INT
         */
        public int|Clear|null $discountDays = null,
        /**
         * Name der Abo-Rechnung (nur intern sichtbar).
         *
         * Billomat-Feld: name
         * Typ: ALNUM
         */
        public string|Clear|null $name = null,
        /**
         * Präfix der Rechnungsnummer.
         *
         * Billomat-Feld: number_pre
         */
        public string|Clear|null $numberPre = null,
        /**
         * Dokumentenüberschrift.
         *
         * Billomat-Feld: title
         * Typ: ALNUM
         */
        public string|Clear|null $title = null,
        /**
         * Bezeichnung.
         *
         * Billomat-Feld: label
         * Typ: ALNUM
         */
        public string|Clear|null $label = null,
        /**
         * Einleitungstext.
         *
         * Billomat-Feld: intro
         * Typ: ALNUM
         */
        public string|Clear|null $intro = null,
        /**
         * Anmerkungstext.
         *
         * Billomat-Feld: note
         * Typ: ALNUM
         */
        public string|Clear|null $note = null,
        /**
         * Währung (ISO-Währungscode).
         *
         * Billomat-Feld: currency_code
         * Typ: ISO-Währungscode
         */
        public string|Clear|null $currencyCode = null,
        /**
         * Preisbasis (Brutto- oder Netto-Preise).
         *
         * Billomat-Feld: net_gross
         * Typ: ALNUM („NET“, „GROSS“)
         */
        public NetGross|Clear|null $netGross = null,
        /**
         * Währungskurs (für Umrechnung in die Standard-Währung).
         *
         * Billomat-Feld: quote
         * Typ: FLOAT
         */
        public float|Clear|null $quote = null,
        /**
         * Akzeptierte Zahlungsarten (kommasepariert).
         *
         * Billomat-Feld: payment_types
         * Typ: ALNUM
         */
        public string|Clear|null $paymentTypes = null,
        /**
         * Aktion, die bei jedem Lauf ausgeführt wird.
         *
         * Billomat-Feld: action
         * Typ: ALNUM
         */
        public RecurringAction|Clear|null $action = null,
        /**
         * Anzahl der Intervall-Einheiten zwischen zwei Läufen.
         *
         * Billomat-Feld: cycle_number
         * Typ: INT
         */
        public int|Clear|null $cycleNumber = null,
        /**
         * Intervall-Einheit (z. B. monatlich).
         *
         * Billomat-Feld: cycle
         * Typ: ALNUM
         */
        public RecurringCycle|Clear|null $cycle = null,
        /**
         * Stunde, zu der die Rechnung erzeugt wird.
         *
         * Billomat-Feld: hour
         * Typ: INT
         */
        public int|Clear|null $hour = null,
        /**
         * Startdatum.
         *
         * Format: YYYY-MM-DD
         *
         * Billomat-Feld: start_date
         * Typ: DATE
         */
        public DateTimeImmutable|Clear|null $startDate = null,
        /**
         * Enddatum.
         *
         * Format: YYYY-MM-DD
         *
         * Billomat-Feld: end_date
         * Typ: DATE
         */
        public DateTimeImmutable|Clear|null $endDate = null,
        /**
         * Datum der nächsten Rechnung.
         *
         * Format: YYYY-MM-DD
         *
         * Billomat-Feld: next_creation_date
         * Typ: DATE
         */
        public DateTimeImmutable|Clear|null $nextCreationDate = null,
        /**
         * Anzahl der Läufe, nach denen die Abo-Rechnung endet.
         *
         * Billomat-Feld: iterations
         */
        public int|Clear|null $iterations = null,
        /**
         * Rabatt (absolut oder als Prozentwert, z. B. "10" oder "10%").
         *
         * Billomat-Feld: reduction
         * Typ: ALNUM
         */
        public string|Clear|null $reduction = null,
        /**
         * Absender-Adresse der E-Mail.
         *
         * Billomat-Feld: email_sender
         * Typ: ALNUM
         */
        public string|Clear|null $emailSender = null,
        /**
         * Betreff der E-Mail.
         *
         * Billomat-Feld: email_subject
         * Typ: ALNUM
         */
        public string|Clear|null $emailSubject = null,
        /**
         * Text der E-Mail.
         *
         * Billomat-Feld: email_message
         * Typ: ALNUM
         */
        public string|Clear|null $emailMessage = null,
        /**
         * ID der E-Mail-Vorlage.
         *
         * Billomat-Feld: email_template_id
         * Typ: INT
         */
        public int|Clear|null $emailTemplateId = null,
        /**
         * ID des Freitextes zur Belegung von title, label, intro und note.
         *
         * Billomat-Feld: free_text_id
         * Typ: INT
         */
        public int|Clear|null $freeTextId = null,
        /**
         * ID der Vorlage für die erzeugten Rechnungen.
         *
         * Billomat-Feld: template_id
         * Typ: INT
         */
        public int|Clear|null $templateId = null,
        /**
         * Dateiname des PDF-Anhangs.
         *
         * Billomat-Feld: email_filename
         * Typ: ALNUM
         */
        public string|Clear|null $emailFilename = null,
        /**
         * E-Mail zusätzlich als BCC an den Absender senden?
         *
         * Billomat-Feld: email_bcc
         * Typ: BOOL
         */
        public bool|Clear|null $emailBcc = null,
        /**
         * Brief farbig drucken?
         *
         * Billomat-Feld: letter_color
         * Typ: BOOL
         */
        public bool|Clear|null $letterColor = null,
        /**
         * Brief beidseitig drucken?
         *
         * Billomat-Feld: letter_duplex
         * Typ: BOOL
         */
        public bool|Clear|null $letterDuplex = null,
        /**
         * Papiergewicht des Briefs in g/m².
         *
         * Billomat-Feld: letter_paper_weight
         * Typ: INT
         */
        public int|Clear|null $letterPaperWeight = null,
        /**
         * ID des Quell-Angebots.
         *
         * Billomat-Feld: offer_id
         * Typ: INT
         */
        public int|Clear|null $offerId = null,
        /**
         * ID der Quell-Auftragsbestätigung.
         *
         * Billomat-Feld: confirmation_id
         * Typ: INT
         */
        public int|Clear|null $confirmationId = null,
    ) {
    }

    /** Kopie mit geändertem `clientId`. */
    public function withClientId(int|Clear|null $clientId): self
    {
        return clone($this, ['clientId' => $clientId]);
    }

    /** Kopie mit geändertem `contactId`. */
    public function withContactId(int|Clear|null $contactId): self
    {
        return clone($this, ['contactId' => $contactId]);
    }

    /** Kopie mit geändertem `address`. */
    public function withAddress(string|Clear|null $address): self
    {
        return clone($this, ['address' => $address]);
    }

    /** Kopie mit geändertem `supplyDate`. */
    public function withSupplyDate(DateTimeImmutable|Clear|null $supplyDate): self
    {
        return clone($this, ['supplyDate' => $supplyDate]);
    }

    /** Kopie mit geändertem `supplyDateType`. */
    public function withSupplyDateType(SupplyDateType|Clear|null $supplyDateType): self
    {
        return clone($this, ['supplyDateType' => $supplyDateType]);
    }

    /** Kopie mit geändertem `dueDays`. */
    public function withDueDays(int|Clear|null $dueDays): self
    {
        return clone($this, ['dueDays' => $dueDays]);
    }

    /** Kopie mit geändertem `discountRate`. */
    public function withDiscountRate(int|Clear|null $discountRate): self
    {
        return clone($this, ['discountRate' => $discountRate]);
    }

    /** Kopie mit geändertem `discountDays`. */
    public function withDiscountDays(int|Clear|null $discountDays): self
    {
        return clone($this, ['discountDays' => $discountDays]);
    }

    /** Kopie mit geändertem `name`. */
    public function withName(string|Clear|null $name): self
    {
        return clone($this, ['name' => $name]);
    }

    /** Kopie mit geändertem `numberPre`. */
    public function withNumberPre(string|Clear|null $numberPre): self
    {
        return clone($this, ['numberPre' => $numberPre]);
    }

    /** Kopie mit geändertem `title`. */
    public function withTitle(string|Clear|null $title): self
    {
        return clone($this, ['title' => $title]);
    }

    /** Kopie mit geändertem `label`. */
    public function withLabel(string|Clear|null $label): self
    {
        return clone($this, ['label' => $label]);
    }

    /** Kopie mit geändertem `intro`. */
    public function withIntro(string|Clear|null $intro): self
    {
        return clone($this, ['intro' => $intro]);
    }

    /** Kopie mit geändertem `note`. */
    public function withNote(string|Clear|null $note): self
    {
        return clone($this, ['note' => $note]);
    }

    /** Kopie mit geändertem `currencyCode`. */
    public function withCurrencyCode(string|Clear|null $currencyCode): self
    {
        return clone($this, ['currencyCode' => $currencyCode]);
    }

    /** Kopie mit geändertem `netGross`. */
    public function withNetGross(NetGross|Clear|null $netGross): self
    {
        return clone($this, ['netGross' => $netGross]);
    }

    /** Kopie mit geändertem `quote`. */
    public function withQuote(float|Clear|null $quote): self
    {
        return clone($this, ['quote' => $quote]);
    }

    /** Kopie mit geändertem `paymentTypes`. */
    public function withPaymentTypes(string|Clear|null $paymentTypes): self
    {
        return clone($this, ['paymentTypes' => $paymentTypes]);
    }

    /** Kopie mit geändertem `action`. */
    public function withAction(RecurringAction|Clear|null $action): self
    {
        return clone($this, ['action' => $action]);
    }

    /** Kopie mit geändertem `cycleNumber`. */
    public function withCycleNumber(int|Clear|null $cycleNumber): self
    {
        return clone($this, ['cycleNumber' => $cycleNumber]);
    }

    /** Kopie mit geändertem `cycle`. */
    public function withCycle(RecurringCycle|Clear|null $cycle): self
    {
        return clone($this, ['cycle' => $cycle]);
    }

    /** Kopie mit geändertem `hour`. */
    public function withHour(int|Clear|null $hour): self
    {
        return clone($this, ['hour' => $hour]);
    }

    /** Kopie mit geändertem `startDate`. */
    public function withStartDate(DateTimeImmutable|Clear|null $startDate): self
    {
        return clone($this, ['startDate' => $startDate]);
    }

    /** Kopie mit geändertem `endDate`. */
    public function withEndDate(DateTimeImmutable|Clear|null $endDate): self
    {
        return clone($this, ['endDate' => $endDate]);
    }

    /** Kopie mit geändertem `nextCreationDate`. */
    public function withNextCreationDate(DateTimeImmutable|Clear|null $nextCreationDate): self
    {
        return clone($this, ['nextCreationDate' => $nextCreationDate]);
    }

    /** Kopie mit geändertem `iterations`. */
    public function withIterations(int|Clear|null $iterations): self
    {
        return clone($this, ['iterations' => $iterations]);
    }

    /** Kopie mit geändertem `reduction`. */
    public function withReduction(string|Clear|null $reduction): self
    {
        return clone($this, ['reduction' => $reduction]);
    }

    /** Kopie mit geändertem `emailSender`. */
    public function withEmailSender(string|Clear|null $emailSender): self
    {
        return clone($this, ['emailSender' => $emailSender]);
    }

    /** Kopie mit geändertem `emailSubject`. */
    public function withEmailSubject(string|Clear|null $emailSubject): self
    {
        return clone($this, ['emailSubject' => $emailSubject]);
    }

    /** Kopie mit geändertem `emailMessage`. */
    public function withEmailMessage(string|Clear|null $emailMessage): self
    {
        return clone($this, ['emailMessage' => $emailMessage]);
    }

    /** Kopie mit geändertem `emailTemplateId`. */
    public function withEmailTemplateId(int|Clear|null $emailTemplateId): self
    {
        return clone($this, ['emailTemplateId' => $emailTemplateId]);
    }

    /** Kopie mit geändertem `freeTextId`. */
    public function withFreeTextId(int|Clear|null $freeTextId): self
    {
        return clone($this, ['freeTextId' => $freeTextId]);
    }

    /** Kopie mit geändertem `templateId`. */
    public function withTemplateId(int|Clear|null $templateId): self
    {
        return clone($this, ['templateId' => $templateId]);
    }

    /** Kopie mit geändertem `emailFilename`. */
    public function withEmailFilename(string|Clear|null $emailFilename): self
    {
        return clone($this, ['emailFilename' => $emailFilename]);
    }

    /** Kopie mit geändertem `emailBcc`. */
    public function withEmailBcc(bool|Clear|null $emailBcc): self
    {
        return clone($this, ['emailBcc' => $emailBcc]);
    }

    /** Kopie mit geändertem `letterColor`. */
    public function withLetterColor(bool|Clear|null $letterColor): self
    {
        return clone($this, ['letterColor' => $letterColor]);
    }

    /** Kopie mit geändertem `letterDuplex`. */
    public function withLetterDuplex(bool|Clear|null $letterDuplex): self
    {
        return clone($this, ['letterDuplex' => $letterDuplex]);
    }

    /** Kopie mit geändertem `letterPaperWeight`. */
    public function withLetterPaperWeight(int|Clear|null $letterPaperWeight): self
    {
        return clone($this, ['letterPaperWeight' => $letterPaperWeight]);
    }

    /** Kopie mit geändertem `offerId`. */
    public function withOfferId(int|Clear|null $offerId): self
    {
        return clone($this, ['offerId' => $offerId]);
    }

    /** Kopie mit geändertem `confirmationId`. */
    public function withConfirmationId(int|Clear|null $confirmationId): self
    {
        return clone($this, ['confirmationId' => $confirmationId]);
    }

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
     * Nicht gesetzte Felder (null) fehlen im Payload, {@see Clear::Field} wird als null gesendet und leert das Feld.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return array_map(
            static fn (mixed $v): mixed => $v instanceof Clear ? null : $v,
            array_filter([
                'client_id' => $this->clientId,
                'contact_id' => $this->contactId,
                'address' => $this->address,
                'number_pre' => $this->numberPre,
                'supply_date' => $this->supplyDate instanceof DateTimeImmutable ? $this->supplyDate->format('Y-m-d') : $this->supplyDate,
                'supply_date_type' => $this->supplyDateType instanceof SupplyDateType ? $this->supplyDateType->value : $this->supplyDateType,
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
                'net_gross' => $this->netGross instanceof NetGross ? $this->netGross->value : $this->netGross,
                'quote' => $this->quote,
                'payment_types' => $this->paymentTypes,
                'action' => $this->action instanceof RecurringAction ? $this->action->value : $this->action,
                'cycle_number' => $this->cycleNumber,
                'cycle' => $this->cycle instanceof RecurringCycle ? $this->cycle->value : $this->cycle,
                'hour' => $this->hour,
                'start_date' => $this->startDate instanceof DateTimeImmutable ? $this->startDate->format('Y-m-d') : $this->startDate,
                'end_date' => $this->endDate instanceof DateTimeImmutable ? $this->endDate->format('Y-m-d') : $this->endDate,
                'iterations' => $this->iterations,
                'email_sender' => $this->emailSender,
                'email_subject' => $this->emailSubject,
                'email_message' => $this->emailMessage,
                'email_template_id' => $this->emailTemplateId,
                'free_text_id' => $this->freeTextId,
                'template_id' => $this->templateId,
                'next_creation_date' => $this->nextCreationDate instanceof DateTimeImmutable ? $this->nextCreationDate->format('Y-m-d') : $this->nextCreationDate,
                'email_filename' => $this->emailFilename,
                'email_bcc' => $this->emailBcc,
                'letter_color' => $this->letterColor,
                'letter_duplex' => $this->letterDuplex,
                'letter_paper_weight' => $this->letterPaperWeight,
                'offer_id' => $this->offerId,
                'confirmation_id' => $this->confirmationId,
            ], static fn (mixed $v): bool => null !== $v),
        );
    }
}
