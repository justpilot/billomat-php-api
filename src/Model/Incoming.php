<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\IncomingStatus;

use const DATE_ATOM;

/**
 * Eingangsrechnung (Incoming) aus der Billomat-API.
 *
 * Dokumentation:
 * https://www.billomat.com/en/api/incomings/
 *
 * Generiert aus tools/codegen/definitions/incomings.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class Incoming
{
    public function __construct(
        /** Interne Billomat-ID der Eingangsrechnung. */
        public ?int $id,
        /** ID des Lieferanten. */
        public ?int $supplierId,
        /** Erstellungszeitpunkt der Eingangsrechnung. */
        public ?DateTimeImmutable $created = null,
        /** Rechnungsdatum. */
        public ?DateTimeImmutable $date = null,
        /**
         * Liefer-/Leistungsdatum.
         *
         * @deprecated since 3.1 – Billomat liefert `supply_date` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.
         */
        public ?DateTimeImmutable $supplyDate = null,
        /** Fälligkeitsdatum. */
        public ?DateTimeImmutable $dueDate = null,
        /**
         * Tage bis zur Fälligkeit.
         *
         * @deprecated since 3.1 – Billomat liefert `due_days` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.
         */
        public ?int $dueDays = null,
        /**
         * Zahlungsdatum.
         *
         * @deprecated since 3.1 – Billomat liefert `paid_at` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.
         */
        public ?DateTimeImmutable $paidAt = null,
        /** Status der Eingangsrechnung. */
        public ?IncomingStatus $status = null,
        /** Rechnungsnummer des Lieferanten. */
        public ?string $incomingNumber = null,
        /** Komplette Absendeadresse. */
        public ?string $address = null,
        /** Bezeichnung. */
        public ?string $label = null,
        /**
         * Einleitungstext.
         *
         * @deprecated since 3.1 – Billomat liefert `intro` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.
         */
        public ?string $intro = null,
        /** Anmerkungstext. */
        public ?string $note = null,
        /** Gesamtbetrag brutto. */
        public ?float $totalGross = null,
        /** Gesamtbetrag netto. */
        public ?float $totalNet = null,
        /** Bereits bezahlter Betrag. */
        public ?float $paidAmount = null,
        /** Offener Betrag. */
        public ?float $openAmount = null,
        /** Währung (ISO-Währungscode). */
        public ?string $currencyCode = null,
        /** Währungskurs (für Umrechnung in die Standard-Währung). */
        public ?float $quote = null,
        /** Zeitpunkt der letzten Änderung. */
        public ?DateTimeImmutable $updated = null,
        /** Freies Zusatzfeld (siehe Custom-Meta-Attribute). */
        public ?string $customfield = null,
        /** Kundennummer beim Lieferanten (z. B. eigene Vertragsnummer). */
        public ?string $clientNumber = null,
        /** Aufwandskonto laut SKR/Konten-Mapping. */
        public ?string $expenseAccountNumber = null,
        /** Kategorie. */
        public ?string $category = null,
        /** Seitenzahl des Belegdokuments. */
        public ?int $pageCount = null,
        /** Ist ein Belegdokument angehängt? */
        public ?bool $hasAttachment = null,
    ) {
    }

    /**
     * Hydriert das Model aus einem Billomat-Response-Array.
     *
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ScalarCaster::toIntOrNull($data['id'] ?? null),
            supplierId: ScalarCaster::toIntOrNull($data['supplier_id'] ?? null),
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            date: ScalarCaster::toDateTimeOrNull($data['date'] ?? null),
            supplyDate: ScalarCaster::toDateTimeOrNull($data['supply_date'] ?? null),
            dueDate: ScalarCaster::toDateTimeOrNull($data['due_date'] ?? null),
            dueDays: ScalarCaster::toIntOrNull($data['due_days'] ?? null),
            paidAt: ScalarCaster::toDateTimeOrNull($data['paid_at'] ?? null),
            status: IncomingStatus::tryFrom(ScalarCaster::toStringOrNull($data['status'] ?? null) ?? ''),
            incomingNumber: ScalarCaster::toStringOrNull($data['number'] ?? $data['incoming_number'] ?? null),
            address: ScalarCaster::toStringOrNull($data['address'] ?? null),
            label: ScalarCaster::toStringOrNull($data['label'] ?? null),
            intro: ScalarCaster::toStringOrNull($data['intro'] ?? null),
            note: ScalarCaster::toStringOrNull($data['note'] ?? null),
            totalGross: ScalarCaster::toFloatOrNull($data['total_gross'] ?? null),
            totalNet: ScalarCaster::toFloatOrNull($data['total_net'] ?? null),
            paidAmount: ScalarCaster::toFloatOrNull($data['paid_amount'] ?? null),
            openAmount: ScalarCaster::toFloatOrNull($data['open_amount'] ?? null),
            currencyCode: ScalarCaster::toStringOrNull($data['currency_code'] ?? null),
            quote: ScalarCaster::toFloatOrNull($data['quote'] ?? null),
            updated: ScalarCaster::toDateTimeOrNull($data['updated'] ?? null),
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
            clientNumber: ScalarCaster::toStringOrNull($data['client_number'] ?? null),
            expenseAccountNumber: ScalarCaster::toStringOrNull($data['expense_account_number'] ?? null),
            category: ScalarCaster::toStringOrNull($data['category'] ?? null),
            pageCount: ScalarCaster::toIntOrNull($data['page_count'] ?? null),
            hasAttachment: ScalarCaster::toBoolOrNull($data['has_attachment'] ?? null),
        );
    }

    /**
     * Exportiert das Model als Array mit Billomat-Feldnamen.
     *
     * Praktisch für Debugging oder Log-Ausgaben.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'supplier_id' => $this->supplierId,
            'created' => $this->created?->format(DATE_ATOM),
            'date' => $this->date?->format('Y-m-d'),
            'supply_date' => $this->supplyDate?->format('Y-m-d'),
            'due_date' => $this->dueDate?->format('Y-m-d'),
            'due_days' => $this->dueDays,
            'paid_at' => $this->paidAt?->format('Y-m-d'),
            'status' => $this->status?->value,
            'incoming_number' => $this->incomingNumber,
            'address' => $this->address,
            'label' => $this->label,
            'intro' => $this->intro,
            'note' => $this->note,
            'total_gross' => $this->totalGross,
            'total_net' => $this->totalNet,
            'paid_amount' => $this->paidAmount,
            'open_amount' => $this->openAmount,
            'currency_code' => $this->currencyCode,
            'quote' => $this->quote,
            'updated' => $this->updated?->format(DATE_ATOM),
            'customfield' => $this->customfield,
            'client_number' => $this->clientNumber,
            'expense_account_number' => $this->expenseAccountNumber,
            'category' => $this->category,
            'page_count' => $this->pageCount,
            'has_attachment' => $this->hasAttachment,
        ];
    }
}
