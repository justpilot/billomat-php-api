<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use DateTimeImmutable;
use Justpilot\Billomat\Model\Enum\NetGross;

/**
 * Typisierter Payload für PUT /confirmations/{id}.
 *
 * Nur gesetzte (nicht-null) Felder werden serialisiert (Partial Update).
 * Nur Auftragsbestätigungen im Status DRAFT sind voll editierbar.
 *
 * Doku:
 * https://www.billomat.com/en/api/confirmations/
 *
 * Generiert aus tools/codegen/definitions/confirmations.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class ConfirmationUpdateOptions implements WriteOptions
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
         * Präfix der Auftragsbestätigungsnummer.
         *
         * Billomat-Feld: number_pre
         * Typ: ALNUM
         */
        public string|Clear|null $numberPre = null,
        /**
         * Mindestlänge der Auftragsbestätigungsnummer (wird mit führenden Nullen aufgefüllt).
         *
         * Billomat-Feld: number_length
         * Typ: INT
         */
        public int|Clear|null $numberLength = null,
        /**
         * Auftragsbestätigungsdatum.
         *
         * Format: YYYY-MM-DD
         *
         * Billomat-Feld: date
         * Typ: DATE
         */
        public DateTimeImmutable|Clear|null $date = null,
        /**
         * Komplette Adresse.
         *
         * Laut Doku veraltet – Billomat verarbeitet das Feld nicht mehr, bitte contact_id oder client_id verwenden.
         *
         * Billomat-Feld: address
         * Typ: ALNUM
         */
        public string|Clear|null $address = null,
        /**
         * Skonto in Prozent.
         *
         * Billomat-Feld: discount_rate
         */
        public float|Clear|null $discountRate = null,
        /**
         * Skontodatum.
         *
         * Format: YYYY-MM-DD
         *
         * Billomat-Feld: discount_date
         */
        public DateTimeImmutable|Clear|null $discountDate = null,
        /**
         * Skontozeitraum in Tagen.
         *
         * Billomat-Feld: discount_days
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
         * Preisbasis (Brutto- oder Netto-Preise).
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
         * Währung (ISO-Währungscode).
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
         * ID des Quell-Angebots.
         *
         * Billomat-Feld: offer_id
         * Typ: INT
         */
        public int|Clear|null $offerId = null,
        /**
         * ID des Freitextes zur Belegung von title, label, intro und note.
         *
         * Billomat-Feld: free_text_id
         * Typ: INT
         */
        public int|Clear|null $freeTextId = null,
        /**
         * ID der Vorlage, mit der die Auftragsbestätigung abgeschlossen wird.
         *
         * Billomat-Feld: template_id
         * Typ: INT
         */
        public int|Clear|null $templateId = null,
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

    /** Kopie mit geändertem `offerId`. */
    public function withOfferId(int|Clear|null $offerId): self
    {
        return clone($this, ['offerId' => $offerId]);
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
                'offer_id' => $this->offerId,
                'free_text_id' => $this->freeTextId,
                'template_id' => $this->templateId,
            ], static fn (mixed $v): bool => null !== $v),
        );
    }
}
