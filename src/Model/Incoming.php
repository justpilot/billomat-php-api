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
    /** Billomat-Felder, die das Model abbildet. */
    private const array FIELDS = [
        'id' => true,
        'supplier_id' => true,
        'created' => true,
        'date' => true,
        'due_date' => true,
        'status' => true,
        'number' => true,
        'incoming_number' => true,
        'address' => true,
        'label' => true,
        'note' => true,
        'total_gross' => true,
        'total_net' => true,
        'paid_amount' => true,
        'open_amount' => true,
        'currency_code' => true,
        'quote' => true,
        'updated' => true,
        'customfield' => true,
        'client_number' => true,
        'expense_account_number' => true,
        'category' => true,
        'page_count' => true,
        'has_attachment' => true,
    ];

    /** @param array<string, mixed> $extra */
    public function __construct(
        /** Interne Billomat-ID der Eingangsrechnung. */
        public ?int $id,
        /** ID des Lieferanten. */
        public ?int $supplierId,
        /** Erstellungszeitpunkt der Eingangsrechnung. */
        public ?DateTimeImmutable $created = null,
        /** Rechnungsdatum. */
        public ?DateTimeImmutable $date = null,
        /** Fälligkeitsdatum. */
        public ?DateTimeImmutable $dueDate = null,
        /** Status der Eingangsrechnung. */
        public ?IncomingStatus $status = null,
        /** Rechnungsnummer des Lieferanten. */
        public ?string $incomingNumber = null,
        /** Komplette Absendeadresse. */
        public ?string $address = null,
        /** Bezeichnung. */
        public ?string $label = null,
        /** Anmerkungstext. */
        public ?string $note = null,
        /** Gesamtbetrag brutto. */
        public ?Money $totalGross = null,
        /** Gesamtbetrag netto. */
        public ?Money $totalNet = null,
        /** Bereits bezahlter Betrag. */
        public ?Money $paidAmount = null,
        /** Offener Betrag. */
        public ?Money $openAmount = null,
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
        /**
         * Felder, die Billomat liefert, das SDK aber (noch) nicht abbildet – roh, wie in der Response.
         *
         * Neue Billomat-Felder gehen so nicht verloren; toArray() gibt sie mit aus.
         */
        public array $extra = [],
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
            dueDate: ScalarCaster::toDateTimeOrNull($data['due_date'] ?? null),
            status: IncomingStatus::fromApi(ScalarCaster::toStringOrNull($data['status'] ?? null)),
            incomingNumber: ScalarCaster::toStringOrNull($data['number'] ?? $data['incoming_number'] ?? null),
            address: ScalarCaster::toStringOrNull($data['address'] ?? null),
            label: ScalarCaster::toStringOrNull($data['label'] ?? null),
            note: ScalarCaster::toStringOrNull($data['note'] ?? null),
            totalGross: ScalarCaster::toMoneyOrNull($data['total_gross'] ?? null),
            totalNet: ScalarCaster::toMoneyOrNull($data['total_net'] ?? null),
            paidAmount: ScalarCaster::toMoneyOrNull($data['paid_amount'] ?? null),
            openAmount: ScalarCaster::toMoneyOrNull($data['open_amount'] ?? null),
            currencyCode: ScalarCaster::toStringOrNull($data['currency_code'] ?? null),
            quote: ScalarCaster::toFloatOrNull($data['quote'] ?? null),
            updated: ScalarCaster::toDateTimeOrNull($data['updated'] ?? null),
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
            clientNumber: ScalarCaster::toStringOrNull($data['client_number'] ?? null),
            expenseAccountNumber: ScalarCaster::toStringOrNull($data['expense_account_number'] ?? null),
            category: ScalarCaster::toStringOrNull($data['category'] ?? null),
            pageCount: ScalarCaster::toIntOrNull($data['page_count'] ?? null),
            hasAttachment: ScalarCaster::toBoolOrNull($data['has_attachment'] ?? null),
            extra: array_diff_key($data, self::FIELDS),
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
            ...$this->extra,
            'id' => $this->id,
            'supplier_id' => $this->supplierId,
            'created' => $this->created?->format(DATE_ATOM),
            'date' => $this->date?->format('Y-m-d'),
            'due_date' => $this->dueDate?->format('Y-m-d'),
            'status' => $this->status?->value,
            'incoming_number' => $this->incomingNumber,
            'address' => $this->address,
            'label' => $this->label,
            'note' => $this->note,
            'total_gross' => $this->totalGross?->toString(),
            'total_net' => $this->totalNet?->toString(),
            'paid_amount' => $this->paidAmount?->toString(),
            'open_amount' => $this->openAmount?->toString(),
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
