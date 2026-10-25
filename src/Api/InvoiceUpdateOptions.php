<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use DateTimeImmutable;
use Justpilot\Billomat\Model\Enum\NetGross;
use Justpilot\Billomat\Model\Enum\PaymentType;
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
final readonly class InvoiceUpdateOptions implements WriteOptions
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
         * Laufende Nummer (ohne Präfix).
         *
         * Billomat-Feld: number
         * Typ: INT
         */
        public int|Clear|null $number = null,
        /**
         * Präfix der Rechnungsnummer (z. B. "RE").
         *
         * Billomat-Feld: number_pre
         * Typ: ALNUM
         */
        public string|Clear|null $numberPre = null,
        /**
         * Mindestlänge der Rechnungsnummer (wird mit führenden Nullen aufgefüllt).
         *
         * Billomat-Feld: number_length
         * Typ: INT
         */
        public int|Clear|null $numberLength = null,
        /**
         * Rechnungsdatum.
         *
         * Format: YYYY-MM-DD
         *
         * Billomat-Feld: date
         * Typ: DATE
         */
        public DateTimeImmutable|Clear|null $date = null,
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
         * Fälligkeitsdatum.
         *
         * Format: YYYY-MM-DD
         *
         * Billomat-Feld: due_date
         * Typ: DATE
         */
        public DateTimeImmutable|Clear|null $dueDate = null,
        /**
         * Tage bis zur Fälligkeit.
         *
         * Billomat-Feld: due_days
         * Typ: INT
         */
        public int|Clear|null $dueDays = null,
        /**
         * Vollständige Rechnungsadresse (formatiert).
         *
         * Billomat-Feld: address
         * Typ: ALNUM
         */
        public string|Clear|null $address = null,
        /**
         * Skonto in Prozent.
         *
         * Billomat-Feld: discount_rate
         * Typ: INT
         */
        public float|Clear|null $discountRate = null,
        /**
         * Skontodatum.
         *
         * Format: YYYY-MM-DD
         *
         * Billomat-Feld: discount_date
         * Typ: DATE
         */
        public DateTimeImmutable|Clear|null $discountDate = null,
        /**
         * Skontofrist in Tagen.
         *
         * Billomat-Feld: discount_days
         * Typ: INT
         */
        public int|Clear|null $discountDays = null,
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
         * Preisbasis (NET, GROSS oder SETTINGS).
         *
         * Billomat-Feld: net_gross
         * Typ: ALNUM („NET“, „GROSS“)
         */
        public NetGross|Clear|null $netGross = null,
        /**
         * Rabatt (absolut oder als Prozentwert, z. B. "10" oder "10%").
         *
         * Billomat-Feld: reduction
         * Typ: ALNUM
         */
        public string|Clear|null $reduction = null,
        /**
         * Währung (ISO-Währungscode, z. B. "EUR").
         *
         * Billomat-Feld: currency_code
         * Typ: ISO-Währungscode
         */
        public string|Clear|null $currencyCode = null,
        /**
         * Währungskurs (für Umrechnung in die Standard-Währung).
         *
         * Billomat-Feld: quote
         * Typ: FLOAT
         */
        public float|Clear|null $quote = null,
        /**
         * ID der korrigierten Rechnung (bei Korrekturrechnung).
         *
         * Billomat-Feld: invoice_id
         * Typ: INT
         */
        public int|Clear|null $invoiceId = null,
        /**
         * ID des Angebots, aus dem die Rechnung entstanden ist.
         *
         * Billomat-Feld: offer_id
         * Typ: INT
         */
        public int|Clear|null $offerId = null,
        /**
         * ID der Auftragsbestätigung, aus der die Rechnung entstanden ist.
         *
         * Billomat-Feld: confirmation_id
         * Typ: INT
         */
        public int|Clear|null $confirmationId = null,
        /**
         * ID der Abo-Rechnung, aus der die Rechnung entstanden ist.
         *
         * Billomat-Feld: recurring_id
         * Typ: INT
         */
        public int|Clear|null $recurringId = null,
        /**
         * Akzeptierte Zahlungsarten.
         *
         * Billomat überträgt sie kommasepariert, z. B. "BANK_TRANSFER,CASH".
         *
         * Billomat-Feld: payment_types
         * Typ: ALNUM
         *
         * @var list<PaymentType>|Clear
         */
        public array|Clear $paymentTypes = [],
        /**
         * ID der Vorlage, mit der die Rechnung abgeschlossen wird.
         *
         * Billomat-Feld: template_id
         * Typ: INT
         */
        public int|Clear|null $templateId = null,
        /**
         * ID des Freitextes zur Belegung von title, label, intro und note.
         *
         * Billomat-Feld: free_text_id
         * Typ: INT
         */
        public int|Clear|null $freeTextId = null,
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

    /** Kopie mit geändertem `number`. */
    public function withNumber(int|Clear|null $number): self
    {
        return clone($this, ['number' => $number]);
    }

    /** Kopie mit geändertem `numberPre`. */
    public function withNumberPre(string|Clear|null $numberPre): self
    {
        return clone($this, ['numberPre' => $numberPre]);
    }

    /** Kopie mit geändertem `numberLength`. */
    public function withNumberLength(int|Clear|null $numberLength): self
    {
        return clone($this, ['numberLength' => $numberLength]);
    }

    /** Kopie mit geändertem `date`. */
    public function withDate(DateTimeImmutable|Clear|null $date): self
    {
        return clone($this, ['date' => $date]);
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

    /** Kopie mit geändertem `dueDate`. */
    public function withDueDate(DateTimeImmutable|Clear|null $dueDate): self
    {
        return clone($this, ['dueDate' => $dueDate]);
    }

    /** Kopie mit geändertem `dueDays`. */
    public function withDueDays(int|Clear|null $dueDays): self
    {
        return clone($this, ['dueDays' => $dueDays]);
    }

    /** Kopie mit geändertem `address`. */
    public function withAddress(string|Clear|null $address): self
    {
        return clone($this, ['address' => $address]);
    }

    /** Kopie mit geändertem `discountRate`. */
    public function withDiscountRate(float|Clear|null $discountRate): self
    {
        return clone($this, ['discountRate' => $discountRate]);
    }

    /** Kopie mit geändertem `discountDate`. */
    public function withDiscountDate(DateTimeImmutable|Clear|null $discountDate): self
    {
        return clone($this, ['discountDate' => $discountDate]);
    }

    /** Kopie mit geändertem `discountDays`. */
    public function withDiscountDays(int|Clear|null $discountDays): self
    {
        return clone($this, ['discountDays' => $discountDays]);
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

    /** Kopie mit geändertem `netGross`. */
    public function withNetGross(NetGross|Clear|null $netGross): self
    {
        return clone($this, ['netGross' => $netGross]);
    }

    /** Kopie mit geändertem `reduction`. */
    public function withReduction(string|Clear|null $reduction): self
    {
        return clone($this, ['reduction' => $reduction]);
    }

    /** Kopie mit geändertem `currencyCode`. */
    public function withCurrencyCode(string|Clear|null $currencyCode): self
    {
        return clone($this, ['currencyCode' => $currencyCode]);
    }

    /** Kopie mit geändertem `quote`. */
    public function withQuote(float|Clear|null $quote): self
    {
        return clone($this, ['quote' => $quote]);
    }

    /** Kopie mit geändertem `invoiceId`. */
    public function withInvoiceId(int|Clear|null $invoiceId): self
    {
        return clone($this, ['invoiceId' => $invoiceId]);
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

    /** Kopie mit geändertem `recurringId`. */
    public function withRecurringId(int|Clear|null $recurringId): self
    {
        return clone($this, ['recurringId' => $recurringId]);
    }

    /**
     * Kopie mit geändertem `paymentTypes`.
     *
     * @param list<PaymentType>|Clear $paymentTypes
     */
    public function withPaymentTypes(array|Clear $paymentTypes): self
    {
        return clone($this, ['paymentTypes' => $paymentTypes]);
    }

    /** Kopie mit geändertem `templateId`. */
    public function withTemplateId(int|Clear|null $templateId): self
    {
        return clone($this, ['templateId' => $templateId]);
    }

    /** Kopie mit geändertem `freeTextId`. */
    public function withFreeTextId(int|Clear|null $freeTextId): self
    {
        return clone($this, ['freeTextId' => $freeTextId]);
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
                'number' => $this->number,
                'number_length' => $this->numberLength,
                'date' => $this->date instanceof DateTimeImmutable ? $this->date->format('Y-m-d') : $this->date,
                'supply_date' => $this->supplyDate instanceof DateTimeImmutable ? $this->supplyDate->format('Y-m-d') : $this->supplyDate,
                'supply_date_type' => $this->supplyDateType instanceof SupplyDateType ? $this->supplyDateType->value : $this->supplyDateType,
                'due_days' => $this->dueDays,
                'due_date' => $this->dueDate instanceof DateTimeImmutable ? $this->dueDate->format('Y-m-d') : $this->dueDate,
                'discount_rate' => $this->discountRate,
                'discount_days' => $this->discountDays,
                'discount_date' => $this->discountDate instanceof DateTimeImmutable ? $this->discountDate->format('Y-m-d') : $this->discountDate,
                'title' => $this->title,
                'label' => $this->label,
                'intro' => $this->intro,
                'note' => $this->note,
                'reduction' => $this->reduction,
                'net_gross' => $this->netGross instanceof NetGross ? $this->netGross->value : $this->netGross,
                'currency_code' => $this->currencyCode,
                'quote' => $this->quote,
                'payment_types' => $this->paymentTypes instanceof Clear ? $this->paymentTypes : ([] === $this->paymentTypes ? null : implode(',', array_map(static fn (PaymentType $v): string => $v->value, $this->paymentTypes))),
                'invoice_id' => $this->invoiceId,
                'offer_id' => $this->offerId,
                'confirmation_id' => $this->confirmationId,
                'recurring_id' => $this->recurringId,
                'free_text_id' => $this->freeTextId,
                'template_id' => $this->templateId,
            ], static fn (mixed $v): bool => null !== $v),
        );
    }
}
