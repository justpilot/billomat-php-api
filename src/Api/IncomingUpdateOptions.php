<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use DateTimeImmutable;

/**
 * Typisierter Payload für PUT /incomings/{id}.
 *
 * Nur gesetzte (nicht-null) Felder werden serialisiert (Partial Update).
 *
 * Doku:
 * https://www.billomat.com/en/api/incomings/
 *
 * Generiert aus tools/codegen/definitions/incomings.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class IncomingUpdateOptions implements WriteOptions
{
    public function __construct(
        /**
         * ID des Lieferanten.
         *
         * Billomat-Feld: supplier_id
         * Typ: INT
         */
        public int|Clear|null $supplierId = null,
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
         */
        public DateTimeImmutable|Clear|null $supplyDate = null,
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
         */
        public int|Clear|null $dueDays = null,
        /**
         * Rechnungsnummer des Lieferanten.
         *
         * Billomat-Feld: incoming_number
         */
        public string|Clear|null $incomingNumber = null,
        /**
         * Rechnungsnummer des Lieferanten.
         *
         * So liefert Billomat die Nummer auch in den Antworten aus (im Read-Model: `incomingNumber`).
         *
         * Billomat-Feld: number
         * Typ: ALNUM
         */
        public string|Clear|null $number = null,
        /**
         * Komplette Absendeadresse.
         *
         * Billomat-Feld: address
         * Typ: ALNUM
         */
        public string|Clear|null $address = null,
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
         * Gesamtbetrag brutto.
         *
         * Billomat-Feld: total_gross
         * Typ: FLOAT
         */
        public float|Clear|null $totalGross = null,
        /**
         * Gesamtbetrag netto.
         *
         * Billomat-Feld: total_net
         * Typ: FLOAT
         */
        public float|Clear|null $totalNet = null,
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
         * Kundennummer beim Lieferanten (z. B. eigene Vertragsnummer).
         *
         * Billomat-Feld: client_number
         * Typ: ALNUM
         */
        public string|Clear|null $clientNumber = null,
        /**
         * Aufwandskonto laut SKR/Konten-Mapping.
         *
         * Billomat-Feld: expense_account_number
         * Typ: INT
         */
        public int|Clear|null $expenseAccountNumber = null,
        /**
         * Kategorie.
         *
         * Billomat-Feld: category
         * Typ: ALNUM
         */
        public string|Clear|null $category = null,
        /**
         * Base64-codiertes Belegdokument (PDF oder Bild).
         *
         * Billomat-Feld: base64file
         * Typ: BASE64FILE
         */
        public string|Clear|null $base64file = null,
    ) {
    }

    /** Kopie mit geändertem `supplierId`. */
    public function withSupplierId(int|Clear|null $supplierId): self
    {
        return clone($this, ['supplierId' => $supplierId]);
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

    /** Kopie mit geändertem `incomingNumber`. */
    public function withIncomingNumber(string|Clear|null $incomingNumber): self
    {
        return clone($this, ['incomingNumber' => $incomingNumber]);
    }

    /** Kopie mit geändertem `number`. */
    public function withNumber(string|Clear|null $number): self
    {
        return clone($this, ['number' => $number]);
    }

    /** Kopie mit geändertem `address`. */
    public function withAddress(string|Clear|null $address): self
    {
        return clone($this, ['address' => $address]);
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

    /** Kopie mit geändertem `totalGross`. */
    public function withTotalGross(float|Clear|null $totalGross): self
    {
        return clone($this, ['totalGross' => $totalGross]);
    }

    /** Kopie mit geändertem `totalNet`. */
    public function withTotalNet(float|Clear|null $totalNet): self
    {
        return clone($this, ['totalNet' => $totalNet]);
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

    /** Kopie mit geändertem `clientNumber`. */
    public function withClientNumber(string|Clear|null $clientNumber): self
    {
        return clone($this, ['clientNumber' => $clientNumber]);
    }

    /** Kopie mit geändertem `expenseAccountNumber`. */
    public function withExpenseAccountNumber(int|Clear|null $expenseAccountNumber): self
    {
        return clone($this, ['expenseAccountNumber' => $expenseAccountNumber]);
    }

    /** Kopie mit geändertem `category`. */
    public function withCategory(string|Clear|null $category): self
    {
        return clone($this, ['category' => $category]);
    }

    /** Kopie mit geändertem `base64file`. */
    public function withBase64file(string|Clear|null $base64file): self
    {
        return clone($this, ['base64file' => $base64file]);
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
                'supplier_id' => $this->supplierId,
                'number' => $this->number,
                'incoming_number' => $this->incomingNumber,
                'date' => $this->date instanceof DateTimeImmutable ? $this->date->format('Y-m-d') : $this->date,
                'supply_date' => $this->supplyDate instanceof DateTimeImmutable ? $this->supplyDate->format('Y-m-d') : $this->supplyDate,
                'due_date' => $this->dueDate instanceof DateTimeImmutable ? $this->dueDate->format('Y-m-d') : $this->dueDate,
                'due_days' => $this->dueDays,
                'address' => $this->address,
                'label' => $this->label,
                'intro' => $this->intro,
                'note' => $this->note,
                'total_gross' => $this->totalGross,
                'total_net' => $this->totalNet,
                'currency_code' => $this->currencyCode,
                'quote' => $this->quote,
                'base64file' => $this->base64file,
                'category' => $this->category,
                'client_number' => $this->clientNumber,
                'expense_account_number' => $this->expenseAccountNumber,
            ], static fn (mixed $v): bool => null !== $v),
        );
    }
}
