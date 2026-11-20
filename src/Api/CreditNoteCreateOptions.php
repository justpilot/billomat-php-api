<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use DateTimeImmutable;
use Justpilot\Billomat\Model\Enum\NetGross;

/**
 * Typisierter Payload für POST /credit-notes.
 *
 * Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren ignoriert.
 * Positionen lassen sich beim Anlegen über `items:` bzw. withItem() direkt mitsenden.
 *
 * Doku:
 * https://www.billomat.com/en/api/credit-notes/
 *
 * Generiert aus tools/codegen/definitions/credit-notes.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class CreditNoteCreateOptions implements WriteOptions
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
         * Laufende Nummer (ohne Präfix).
         *
         * Billomat-Feld: number
         * Typ: INT
         * Default: nächste freie Nummer
         */
        public ?int $number = null,
        /**
         * Präfix der Gutschriftsnummer.
         *
         * Billomat-Feld: number_pre
         * Typ: ALNUM
         * Default: Wert aus Einstellungen
         */
        public ?string $numberPre = null,
        /**
         * Mindestlänge der Gutschriftsnummer (wird mit führenden Nullen aufgefüllt).
         *
         * Billomat-Feld: number_length
         * Typ: INT
         * Default: Wert aus den Einstellungen
         */
        public ?int $numberLength = null,
        /**
         * Gutschriftsdatum.
         *
         * Format: YYYY-MM-DD
         *
         * Billomat-Feld: date
         * Typ: DATE
         * Default: heute
         */
        public ?DateTimeImmutable $date = null,
        /**
         * Komplette Adresse.
         *
         * Billomat-Feld: address
         * Typ: ALNUM
         * Default: Adresse des Kunden
         */
        public ?string $address = null,
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
         * Preisbasis (Brutto- oder Netto-Preise).
         *
         * Billomat-Feld: net_gross
         * Typ: ALNUM („NET“, „GROSS“)
         * Default: Wert aus den Einstellungen
         */
        public ?NetGross $netGross = null,
        /**
         * Rabatt (absolut oder als Prozentwert, z. B. "10" oder "10%").
         *
         * Billomat-Feld: reduction
         * Typ: ALNUM
         */
        public ?string $reduction = null,
        /**
         * Währung (ISO-Währungscode).
         *
         * Billomat-Feld: currency_code
         * Typ: ISO-Währungscode
         * Default: Standard-Währung
         */
        public ?string $currencyCode = null,
        /**
         * Währungskurs (für Umrechnung in die Standard-Währung).
         *
         * Billomat-Feld: quote
         * Typ: FLOAT
         * Default: 1.0000
         */
        public ?float $quote = null,
        /**
         * ID der Quell-Rechnung (Korrekturgutschrift).
         *
         * Billomat-Feld: invoice_id
         * Typ: INT
         */
        public ?int $invoiceId = null,
        /**
         * ID des Freitextes zur Belegung von title, label, intro und note.
         *
         * Billomat-Feld: free_text_id
         * Typ: INT
         */
        public ?int $freeTextId = null,
        /**
         * ID der Vorlage, mit der die Gutschrift abgeschlossen wird.
         *
         * Billomat-Feld: template_id
         * Typ: INT
         * Default: ID der Standardvorlage
         */
        public ?int $templateId = null,
        /**
         * Positionen, die beim Anlegen mitgesendet werden.
         *
         * @var list<CreditNoteItemCreateOptions>
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

    /** Kopie mit geändertem `number`. */
    public function withNumber(?int $number): self
    {
        return clone($this, ['number' => $number]);
    }

    /** Kopie mit geändertem `numberPre`. */
    public function withNumberPre(?string $numberPre): self
    {
        return clone($this, ['numberPre' => $numberPre]);
    }

    /** Kopie mit geändertem `numberLength`. */
    public function withNumberLength(?int $numberLength): self
    {
        return clone($this, ['numberLength' => $numberLength]);
    }

    /** Kopie mit geändertem `date`. */
    public function withDate(?DateTimeImmutable $date): self
    {
        return clone($this, ['date' => $date]);
    }

    /** Kopie mit geändertem `address`. */
    public function withAddress(?string $address): self
    {
        return clone($this, ['address' => $address]);
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

    /** Kopie mit geändertem `netGross`. */
    public function withNetGross(?NetGross $netGross): self
    {
        return clone($this, ['netGross' => $netGross]);
    }

    /** Kopie mit geändertem `reduction`. */
    public function withReduction(?string $reduction): self
    {
        return clone($this, ['reduction' => $reduction]);
    }

    /** Kopie mit geändertem `currencyCode`. */
    public function withCurrencyCode(?string $currencyCode): self
    {
        return clone($this, ['currencyCode' => $currencyCode]);
    }

    /** Kopie mit geändertem `quote`. */
    public function withQuote(?float $quote): self
    {
        return clone($this, ['quote' => $quote]);
    }

    /** Kopie mit geändertem `invoiceId`. */
    public function withInvoiceId(?int $invoiceId): self
    {
        return clone($this, ['invoiceId' => $invoiceId]);
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

    /** Kopie mit einer zusätzlichen Position. */
    public function withItem(CreditNoteItemCreateOptions $item): self
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
            'number' => $this->number,
            'number_length' => $this->numberLength,
            'date' => $this->date?->format('Y-m-d'),
            'title' => $this->title,
            'label' => $this->label,
            'intro' => $this->intro,
            'note' => $this->note,
            'reduction' => $this->reduction,
            'currency_code' => $this->currencyCode,
            'net_gross' => $this->netGross?->value,
            'quote' => $this->quote,
            'invoice_id' => $this->invoiceId,
            'free_text_id' => $this->freeTextId,
            'template_id' => $this->templateId,
        ], static fn (mixed $v): bool => null !== $v);

        if ([] !== $this->items) {
            $data['credit-note-items'] = [
                'credit-note-item' => array_map(static fn (CreditNoteItemCreateOptions $item): array => $item->toArray(), $this->items),
            ];
        }

        return $data;
    }
}
