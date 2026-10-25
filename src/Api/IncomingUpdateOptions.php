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
final class IncomingUpdateOptions implements WriteOptions
{
    /**
     * ID des Lieferanten.
     *
     * Billomat-Feld: supplier_id
     * Typ: INT
     */
    public ?int $supplierId = null;

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
     */
    public ?DateTimeImmutable $supplyDate = null;

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
     */
    public ?int $dueDays = null;

    /**
     * Rechnungsnummer des Lieferanten.
     *
     * Billomat-Feld: incoming_number
     */
    public ?string $incomingNumber = null;

    /**
     * Rechnungsnummer des Lieferanten.
     *
     * So liefert Billomat die Nummer auch in den Antworten aus (im Read-Model: `incomingNumber`).
     *
     * Billomat-Feld: number
     * Typ: ALNUM
     */
    public ?string $number = null;

    /**
     * Komplette Absendeadresse.
     *
     * Billomat-Feld: address
     * Typ: ALNUM
     */
    public ?string $address = null;

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
     * Gesamtbetrag brutto.
     *
     * Billomat-Feld: total_gross
     * Typ: FLOAT
     */
    public ?float $totalGross = null;

    /**
     * Gesamtbetrag netto.
     *
     * Billomat-Feld: total_net
     * Typ: FLOAT
     */
    public ?float $totalNet = null;

    /**
     * Währung (ISO-Währungscode).
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
     * Kundennummer beim Lieferanten (z. B. eigene Vertragsnummer).
     *
     * Billomat-Feld: client_number
     * Typ: ALNUM
     */
    public ?string $clientNumber = null;

    /**
     * Aufwandskonto laut SKR/Konten-Mapping.
     *
     * Billomat-Feld: expense_account_number
     * Typ: INT
     */
    public ?int $expenseAccountNumber = null;

    /**
     * Kategorie.
     *
     * Billomat-Feld: category
     * Typ: ALNUM
     */
    public ?string $category = null;

    /**
     * Base64-codiertes Belegdokument (PDF oder Bild).
     *
     * Billomat-Feld: base64file
     * Typ: BASE64FILE
     */
    public ?string $base64file = null;

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [
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
            'total_gross' => $this->totalGross,
            'total_net' => $this->totalNet,
            'currency_code' => $this->currencyCode,
            'quote' => $this->quote,
            'base64file' => $this->base64file,
            'category' => $this->category,
            'client_number' => $this->clientNumber,
            'expense_account_number' => $this->expenseAccountNumber,
        ];

        // Null-Felder entfernen
        return array_filter($data, static fn (mixed $v): bool => null !== $v);
    }
}
