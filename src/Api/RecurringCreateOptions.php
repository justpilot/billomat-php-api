<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use DateTimeImmutable;
use Justpilot\Billomat\Model\Enum\NetGross;
use Justpilot\Billomat\Model\Enum\RecurringAction;
use Justpilot\Billomat\Model\Enum\RecurringCycle;
use Justpilot\Billomat\Model\Enum\SupplyDateType;

/**
 * Typisierter Payload für POST /recurrings.
 *
 * Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren ignoriert.
 * Positionen lassen sich beim Anlegen über `items:` bzw. withItem() direkt mitsenden;
 * spätere Änderungen an Positionen laufen über $billomat->recurrings->items.
 *
 * Doku:
 * https://www.billomat.com/api/abo-rechnungen/
 *
 * Generiert aus tools/codegen/definitions/recurrings.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class RecurringCreateOptions implements WriteOptions
{
    public function __construct(
        /**
         * ID des Kunden.
         *
         * Billomat-Feld: client_id
         * Typ: INT
         */
        public int $clientId,
        /**
         * ID des Kontakts.
         *
         * Billomat-Feld: contact_id
         * Typ: INT
         */
        public ?int $contactId = null,
        /**
         * Komplette Adresse.
         *
         * Wird ein leerer Wert übergeben, wird die aktuelle Kunden-Adresse verwendet.
         *
         * Billomat-Feld: address
         * Typ: ALNUM
         * Default: Adresse des Kunden (wird ein leerer Wert übergeben, wird die aktuelle Kunden-Adresse verwendet)
         */
        public ?string $address = null,
        /**
         * Liefer-/Leistungsdatum.
         *
         * Format: YYYY-MM-DD
         *
         * Billomat-Feld: supply_date
         * Typ: MIXED (DATE/ALNUM)
         */
        public ?DateTimeImmutable $supplyDate = null,
        /**
         * Typ des Liefer-/Leistungsdatums.
         *
         * Billomat-Feld: supply_date_type
         * Typ: ALNUM („SUPPLY_DATE“, „DELIVERY_DATE“, „SUPPLY_TEXT“, „DELIVERY_TEXT“)
         */
        public ?SupplyDateType $supplyDateType = null,
        /**
         * Tage bis zur Fälligkeit der erzeugten Rechnungen.
         *
         * Billomat-Feld: due_days
         * Typ: INT
         * Default: Fälligkeit in Tagen aus Einstellungen
         */
        public ?int $dueDays = null,
        /**
         * Skonto in Prozent.
         *
         * Billomat-Feld: discount_rate
         * Typ: FLOAT
         * Default: Wert aus Einstellungen
         */
        public ?int $discountRate = null,
        /**
         * Skontofrist in Tagen.
         *
         * Billomat-Feld: discount_days
         * Typ: INT
         * Default: Skontozeitraum in Tagen aus Einstellungen
         */
        public ?int $discountDays = null,
        /**
         * Name der Abo-Rechnung (nur intern sichtbar).
         *
         * Billomat-Feld: name
         * Typ: ALNUM
         */
        public ?string $name = null,
        /**
         * Präfix der Rechnungsnummer.
         *
         * Billomat-Feld: number_pre
         */
        public ?string $numberPre = null,
        /**
         * Dokumentenüberschrift.
         *
         * Billomat-Feld: title
         * Typ: ALNUM
         */
        public ?string $title = null,
        /**
         * Bezeichnung.
         *
         * Billomat-Feld: label
         * Typ: ALNUM
         */
        public ?string $label = null,
        /**
         * Einleitungstext.
         *
         * Billomat-Feld: intro
         * Typ: ALNUM
         * Default: Wert aus Einstellungen
         */
        public ?string $intro = null,
        /**
         * Anmerkungstext.
         *
         * Billomat-Feld: note
         * Typ: ALNUM
         * Default: Wert aus Einstellungen
         */
        public ?string $note = null,
        /**
         * Währung (ISO-Währungscode).
         *
         * Billomat-Feld: currency_code
         * Typ: ISO-Währungscode
         * Default: Standard-Währung
         */
        public ?string $currencyCode = null,
        /**
         * Preisbasis (Brutto- oder Netto-Preise).
         *
         * Billomat-Feld: net_gross
         * Typ: ALNUM („NET“, „GROSS“)
         * Default: Wert aus den Einstellungen
         */
        public ?NetGross $netGross = null,
        /**
         * Währungskurs (für Umrechnung in die Standard-Währung).
         *
         * Billomat-Feld: quote
         * Typ: FLOAT
         * Default: 1.0000
         */
        public ?float $quote = null,
        /**
         * Akzeptierte Zahlungsarten (kommasepariert).
         *
         * Billomat-Feld: payment_types
         * Typ: ALNUM
         * Default: Wert aus den Einstellungen
         */
        public ?string $paymentTypes = null,
        /**
         * Aktion, die bei jedem Lauf ausgeführt wird.
         *
         * Billomat-Feld: action
         * Typ: ALNUM
         * Default: CREATE
         */
        public ?RecurringAction $action = null,
        /**
         * Anzahl der Intervall-Einheiten zwischen zwei Läufen.
         *
         * Billomat-Feld: cycle_number
         * Typ: INT
         * Default: 1
         */
        public ?int $cycleNumber = null,
        /**
         * Intervall-Einheit (z. B. monatlich).
         *
         * Billomat-Feld: cycle
         * Typ: ALNUM
         * Default: MONTHLY
         */
        public ?RecurringCycle $cycle = null,
        /**
         * Stunde, zu der die Rechnung erzeugt wird.
         *
         * Billomat-Feld: hour
         * Typ: INT
         * Default: 2
         */
        public ?int $hour = null,
        /**
         * Startdatum.
         *
         * Format: YYYY-MM-DD
         *
         * Billomat-Feld: start_date
         * Typ: DATE
         * Default: morgiges Datum
         */
        public ?DateTimeImmutable $startDate = null,
        /**
         * Enddatum.
         *
         * Format: YYYY-MM-DD
         *
         * Billomat-Feld: end_date
         * Typ: DATE
         */
        public ?DateTimeImmutable $endDate = null,
        /**
         * Datum der nächsten Rechnung.
         *
         * Format: YYYY-MM-DD
         *
         * Billomat-Feld: next_creation_date
         * Typ: DATE
         * Default: start_date
         */
        public ?DateTimeImmutable $nextCreationDate = null,
        /**
         * Anzahl der Läufe, nach denen die Abo-Rechnung endet.
         *
         * Billomat-Feld: iterations
         */
        public ?int $iterations = null,
        /**
         * Rabatt (absolut oder als Prozentwert, z. B. "10" oder "10%").
         *
         * Billomat-Feld: reduction
         * Typ: ALNUM
         */
        public ?string $reduction = null,
        /**
         * Absender-Adresse der E-Mail.
         *
         * Billomat-Feld: email_sender
         * Typ: ALNUM
         */
        public ?string $emailSender = null,
        /**
         * Betreff der E-Mail.
         *
         * Billomat-Feld: email_subject
         * Typ: ALNUM
         */
        public ?string $emailSubject = null,
        /**
         * Text der E-Mail.
         *
         * Billomat-Feld: email_message
         * Typ: ALNUM
         */
        public ?string $emailMessage = null,
        /**
         * ID der E-Mail-Vorlage.
         *
         * Billomat-Feld: email_template_id
         * Typ: INT
         */
        public ?int $emailTemplateId = null,
        /**
         * ID des Freitextes zur Belegung von title, label, intro und note.
         *
         * Billomat-Feld: free_text_id
         * Typ: INT
         */
        public ?int $freeTextId = null,
        /**
         * ID der Vorlage für die erzeugten Rechnungen.
         *
         * Billomat-Feld: template_id
         * Typ: INT
         */
        public ?int $templateId = null,
        /**
         * Dateiname des PDF-Anhangs.
         *
         * Billomat-Feld: email_filename
         * Typ: ALNUM
         */
        public ?string $emailFilename = null,
        /**
         * E-Mail zusätzlich als BCC an den Absender senden?
         *
         * Billomat-Feld: email_bcc
         * Typ: BOOL
         */
        public ?bool $emailBcc = null,
        /**
         * Brief farbig drucken?
         *
         * Billomat-Feld: letter_color
         * Typ: BOOL
         * Default: 0
         */
        public ?bool $letterColor = null,
        /**
         * Brief beidseitig drucken?
         *
         * Billomat-Feld: letter_duplex
         * Typ: BOOL
         * Default: 1
         */
        public ?bool $letterDuplex = null,
        /**
         * Papiergewicht des Briefs in g/m².
         *
         * Billomat-Feld: letter_paper_weight
         * Typ: INT
         * Default: 90
         */
        public ?int $letterPaperWeight = null,
        /**
         * ID des Quell-Angebots.
         *
         * Billomat-Feld: offer_id
         * Typ: INT
         */
        public ?int $offerId = null,
        /**
         * ID der Quell-Auftragsbestätigung.
         *
         * Billomat-Feld: confirmation_id
         * Typ: INT
         */
        public ?int $confirmationId = null,
        /**
         * Positionen, die beim Anlegen mitgesendet werden.
         *
         * @var list<RecurringItemCreateOptions>
         */
        public array $items = [],
    ) {
    }

    /** Kopie mit geändertem `clientId`. */
    public function withClientId(int $clientId): self
    {
        return clone($this, ['clientId' => $clientId]);
    }

    /** Kopie mit geändertem `contactId`. */
    public function withContactId(?int $contactId): self
    {
        return clone($this, ['contactId' => $contactId]);
    }

    /** Kopie mit geändertem `address`. */
    public function withAddress(?string $address): self
    {
        return clone($this, ['address' => $address]);
    }

    /** Kopie mit geändertem `supplyDate`. */
    public function withSupplyDate(?DateTimeImmutable $supplyDate): self
    {
        return clone($this, ['supplyDate' => $supplyDate]);
    }

    /** Kopie mit geändertem `supplyDateType`. */
    public function withSupplyDateType(?SupplyDateType $supplyDateType): self
    {
        return clone($this, ['supplyDateType' => $supplyDateType]);
    }

    /** Kopie mit geändertem `dueDays`. */
    public function withDueDays(?int $dueDays): self
    {
        return clone($this, ['dueDays' => $dueDays]);
    }

    /** Kopie mit geändertem `discountRate`. */
    public function withDiscountRate(?int $discountRate): self
    {
        return clone($this, ['discountRate' => $discountRate]);
    }

    /** Kopie mit geändertem `discountDays`. */
    public function withDiscountDays(?int $discountDays): self
    {
        return clone($this, ['discountDays' => $discountDays]);
    }

    /** Kopie mit geändertem `name`. */
    public function withName(?string $name): self
    {
        return clone($this, ['name' => $name]);
    }

    /** Kopie mit geändertem `numberPre`. */
    public function withNumberPre(?string $numberPre): self
    {
        return clone($this, ['numberPre' => $numberPre]);
    }

    /** Kopie mit geändertem `title`. */
    public function withTitle(?string $title): self
    {
        return clone($this, ['title' => $title]);
    }

    /** Kopie mit geändertem `label`. */
    public function withLabel(?string $label): self
    {
        return clone($this, ['label' => $label]);
    }

    /** Kopie mit geändertem `intro`. */
    public function withIntro(?string $intro): self
    {
        return clone($this, ['intro' => $intro]);
    }

    /** Kopie mit geändertem `note`. */
    public function withNote(?string $note): self
    {
        return clone($this, ['note' => $note]);
    }

    /** Kopie mit geändertem `currencyCode`. */
    public function withCurrencyCode(?string $currencyCode): self
    {
        return clone($this, ['currencyCode' => $currencyCode]);
    }

    /** Kopie mit geändertem `netGross`. */
    public function withNetGross(?NetGross $netGross): self
    {
        return clone($this, ['netGross' => $netGross]);
    }

    /** Kopie mit geändertem `quote`. */
    public function withQuote(?float $quote): self
    {
        return clone($this, ['quote' => $quote]);
    }

    /** Kopie mit geändertem `paymentTypes`. */
    public function withPaymentTypes(?string $paymentTypes): self
    {
        return clone($this, ['paymentTypes' => $paymentTypes]);
    }

    /** Kopie mit geändertem `action`. */
    public function withAction(?RecurringAction $action): self
    {
        return clone($this, ['action' => $action]);
    }

    /** Kopie mit geändertem `cycleNumber`. */
    public function withCycleNumber(?int $cycleNumber): self
    {
        return clone($this, ['cycleNumber' => $cycleNumber]);
    }

    /** Kopie mit geändertem `cycle`. */
    public function withCycle(?RecurringCycle $cycle): self
    {
        return clone($this, ['cycle' => $cycle]);
    }

    /** Kopie mit geändertem `hour`. */
    public function withHour(?int $hour): self
    {
        return clone($this, ['hour' => $hour]);
    }

    /** Kopie mit geändertem `startDate`. */
    public function withStartDate(?DateTimeImmutable $startDate): self
    {
        return clone($this, ['startDate' => $startDate]);
    }

    /** Kopie mit geändertem `endDate`. */
    public function withEndDate(?DateTimeImmutable $endDate): self
    {
        return clone($this, ['endDate' => $endDate]);
    }

    /** Kopie mit geändertem `nextCreationDate`. */
    public function withNextCreationDate(?DateTimeImmutable $nextCreationDate): self
    {
        return clone($this, ['nextCreationDate' => $nextCreationDate]);
    }

    /** Kopie mit geändertem `iterations`. */
    public function withIterations(?int $iterations): self
    {
        return clone($this, ['iterations' => $iterations]);
    }

    /** Kopie mit geändertem `reduction`. */
    public function withReduction(?string $reduction): self
    {
        return clone($this, ['reduction' => $reduction]);
    }

    /** Kopie mit geändertem `emailSender`. */
    public function withEmailSender(?string $emailSender): self
    {
        return clone($this, ['emailSender' => $emailSender]);
    }

    /** Kopie mit geändertem `emailSubject`. */
    public function withEmailSubject(?string $emailSubject): self
    {
        return clone($this, ['emailSubject' => $emailSubject]);
    }

    /** Kopie mit geändertem `emailMessage`. */
    public function withEmailMessage(?string $emailMessage): self
    {
        return clone($this, ['emailMessage' => $emailMessage]);
    }

    /** Kopie mit geändertem `emailTemplateId`. */
    public function withEmailTemplateId(?int $emailTemplateId): self
    {
        return clone($this, ['emailTemplateId' => $emailTemplateId]);
    }

    /** Kopie mit geändertem `freeTextId`. */
    public function withFreeTextId(?int $freeTextId): self
    {
        return clone($this, ['freeTextId' => $freeTextId]);
    }

    /** Kopie mit geändertem `templateId`. */
    public function withTemplateId(?int $templateId): self
    {
        return clone($this, ['templateId' => $templateId]);
    }

    /** Kopie mit geändertem `emailFilename`. */
    public function withEmailFilename(?string $emailFilename): self
    {
        return clone($this, ['emailFilename' => $emailFilename]);
    }

    /** Kopie mit geändertem `emailBcc`. */
    public function withEmailBcc(?bool $emailBcc): self
    {
        return clone($this, ['emailBcc' => $emailBcc]);
    }

    /** Kopie mit geändertem `letterColor`. */
    public function withLetterColor(?bool $letterColor): self
    {
        return clone($this, ['letterColor' => $letterColor]);
    }

    /** Kopie mit geändertem `letterDuplex`. */
    public function withLetterDuplex(?bool $letterDuplex): self
    {
        return clone($this, ['letterDuplex' => $letterDuplex]);
    }

    /** Kopie mit geändertem `letterPaperWeight`. */
    public function withLetterPaperWeight(?int $letterPaperWeight): self
    {
        return clone($this, ['letterPaperWeight' => $letterPaperWeight]);
    }

    /** Kopie mit geändertem `offerId`. */
    public function withOfferId(?int $offerId): self
    {
        return clone($this, ['offerId' => $offerId]);
    }

    /** Kopie mit geändertem `confirmationId`. */
    public function withConfirmationId(?int $confirmationId): self
    {
        return clone($this, ['confirmationId' => $confirmationId]);
    }

    /** Kopie mit einer zusätzlichen Position. */
    public function withItem(RecurringItemCreateOptions $item): self
    {
        return clone($this, ['items' => [...$this->items, $item]]);
    }

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = array_filter([
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
        ], static fn (mixed $v): bool => null !== $v);

        if ([] !== $this->items) {
            $data['recurring-items'] = [
                'recurring-item' => array_map(static fn (RecurringItemCreateOptions $item): array => $item->toArray(), $this->items),
            ];
        }

        return $data;
    }
}
