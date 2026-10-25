<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use DateTimeImmutable;
use Justpilot\Billomat\Model\Money;

/**
 * Typisierter Payload für POST /incomings.
 *
 * Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren ignoriert.
 *
 * Doku:
 * https://www.billomat.com/en/api/incomings/
 *
 * Generiert aus tools/codegen/definitions/incomings.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class IncomingCreateOptions implements WriteOptions
{
    public function __construct(
        /**
         * ID des Lieferanten.
         *
         * Billomat-Feld: supplier_id
         * Typ: INT
         */
        public int $supplierId,
        /**
         * Rechnungsdatum.
         *
         * Format: YYYY-MM-DD
         *
         * Billomat-Feld: date
         * Typ: DATE
         */
        public ?DateTimeImmutable $date = null,
        /**
         * Liefer-/Leistungsdatum.
         *
         * Format: YYYY-MM-DD
         *
         * Billomat-Feld: supply_date
         */
        public ?DateTimeImmutable $supplyDate = null,
        /**
         * Fälligkeitsdatum.
         *
         * Format: YYYY-MM-DD
         *
         * Billomat-Feld: due_date
         * Typ: DATE
         */
        public ?DateTimeImmutable $dueDate = null,
        /**
         * Tage bis zur Fälligkeit.
         *
         * Billomat-Feld: due_days
         */
        public ?int $dueDays = null,
        /**
         * Rechnungsnummer des Lieferanten.
         *
         * Billomat-Feld: incoming_number
         */
        public ?string $incomingNumber = null,
        /**
         * Rechnungsnummer des Lieferanten.
         *
         * So liefert Billomat die Nummer auch in den Antworten aus (im Read-Model: `incomingNumber`).
         *
         * Billomat-Feld: number
         * Typ: ALNUM
         */
        public ?string $number = null,
        /**
         * Komplette Absendeadresse.
         *
         * Billomat-Feld: address
         * Typ: ALNUM
         * Default: Adresse des Lieferanten
         */
        public ?string $address = null,
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
         */
        public ?string $intro = null,
        /**
         * Anmerkungstext.
         *
         * Billomat-Feld: note
         * Typ: ALNUM
         */
        public ?string $note = null,
        /**
         * Gesamtbetrag brutto.
         *
         * Billomat-Feld: total_gross
         * Typ: FLOAT
         */
        public ?Money $totalGross = null,
        /**
         * Gesamtbetrag netto.
         *
         * Billomat-Feld: total_net
         * Typ: FLOAT
         */
        public ?Money $totalNet = null,
        /**
         * Währung (ISO-Währungscode).
         *
         * Billomat-Feld: currency_code
         * Typ: ISO-Währungscode
         * Default: Währung des Lieferanten
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
         * Kundennummer beim Lieferanten (z. B. eigene Vertragsnummer).
         *
         * Billomat-Feld: client_number
         * Typ: ALNUM
         */
        public ?string $clientNumber = null,
        /**
         * Aufwandskonto laut SKR/Konten-Mapping.
         *
         * Billomat-Feld: expense_account_number
         * Typ: INT
         */
        public ?int $expenseAccountNumber = null,
        /**
         * Kategorie.
         *
         * Billomat-Feld: category
         * Typ: ALNUM
         */
        public ?string $category = null,
        /**
         * Base64-codiertes Belegdokument (PDF oder Bild).
         *
         * Billomat-Feld: base64file
         * Typ: BASE64FILE
         */
        public ?string $base64file = null,
    ) {
    }

    /** Kopie mit geändertem `supplierId`. */
    public function withSupplierId(int $supplierId): self
    {
        return clone($this, ['supplierId' => $supplierId]);
    }

    /** Kopie mit geändertem `date`. */
    public function withDate(?DateTimeImmutable $date): self
    {
        return clone($this, ['date' => $date]);
    }

    /** Kopie mit geändertem `supplyDate`. */
    public function withSupplyDate(?DateTimeImmutable $supplyDate): self
    {
        return clone($this, ['supplyDate' => $supplyDate]);
    }

    /** Kopie mit geändertem `dueDate`. */
    public function withDueDate(?DateTimeImmutable $dueDate): self
    {
        return clone($this, ['dueDate' => $dueDate]);
    }

    /** Kopie mit geändertem `dueDays`. */
    public function withDueDays(?int $dueDays): self
    {
        return clone($this, ['dueDays' => $dueDays]);
    }

    /** Kopie mit geändertem `incomingNumber`. */
    public function withIncomingNumber(?string $incomingNumber): self
    {
        return clone($this, ['incomingNumber' => $incomingNumber]);
    }

    /** Kopie mit geändertem `number`. */
    public function withNumber(?string $number): self
    {
        return clone($this, ['number' => $number]);
    }

    /** Kopie mit geändertem `address`. */
    public function withAddress(?string $address): self
    {
        return clone($this, ['address' => $address]);
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

    /** Kopie mit geändertem `totalGross`. */
    public function withTotalGross(?Money $totalGross): self
    {
        return clone($this, ['totalGross' => $totalGross]);
    }

    /** Kopie mit geändertem `totalNet`. */
    public function withTotalNet(?Money $totalNet): self
    {
        return clone($this, ['totalNet' => $totalNet]);
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

    /** Kopie mit geändertem `clientNumber`. */
    public function withClientNumber(?string $clientNumber): self
    {
        return clone($this, ['clientNumber' => $clientNumber]);
    }

    /** Kopie mit geändertem `expenseAccountNumber`. */
    public function withExpenseAccountNumber(?int $expenseAccountNumber): self
    {
        return clone($this, ['expenseAccountNumber' => $expenseAccountNumber]);
    }

    /** Kopie mit geändertem `category`. */
    public function withCategory(?string $category): self
    {
        return clone($this, ['category' => $category]);
    }

    /** Kopie mit geändertem `base64file`. */
    public function withBase64file(?string $base64file): self
    {
        return clone($this, ['base64file' => $base64file]);
    }

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'supplier_id' => $this->supplierId,
            'number' => $this->number,
            'incoming_number' => $this->incomingNumber,
            'date' => $this->date?->format('Y-m-d'),
            'supply_date' => $this->supplyDate?->format('Y-m-d'),
            'due_date' => $this->dueDate?->format('Y-m-d'),
            'due_days' => $this->dueDays,
            'address' => $this->address,
            'label' => $this->label,
            'intro' => $this->intro,
            'note' => $this->note,
            'total_gross' => $this->totalGross?->toString(),
            'total_net' => $this->totalNet?->toString(),
            'currency_code' => $this->currencyCode,
            'quote' => $this->quote,
            'base64file' => $this->base64file,
            'category' => $this->category,
            'client_number' => $this->clientNumber,
            'expense_account_number' => $this->expenseAccountNumber,
        ], static fn (mixed $v): bool => null !== $v);
    }
}
