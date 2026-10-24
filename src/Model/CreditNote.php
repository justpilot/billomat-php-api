<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ListCaster;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\CreditNoteStatus;
use Justpilot\Billomat\Model\Enum\NetGross;

use const DATE_ATOM;

/**
 * Repräsentiert eine Gutschrift (Credit Note) aus der Billomat-API.
 *
 * Dokumentation:
 * https://www.billomat.com/en/api/credit-notes/
 *
 * Generiert aus tools/codegen/definitions/credit-notes.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class CreditNote
{
    /**
     * @param list<array{name: string, rate: float, amount: float}> $taxes
     * @param list<CreditNoteItem>                                  $items
     */
    public function __construct(
        /** Interne Billomat-ID der Gutschrift. */
        public ?int $id,
        /** ID des Kunden. */
        public int $clientId,
        /** ID des Kontakts. */
        public ?int $contactId = null,
        /** Erstellungszeitpunkt der Gutschrift. */
        public ?DateTimeImmutable $created = null,
        /** Gutschriftsnummer (wird beim Abschließen vergeben). */
        public ?string $creditNoteNumber = null,
        /** Laufende Nummer (ohne Präfix). */
        public ?int $number = null,
        /** Präfix der Gutschriftsnummer. */
        public ?string $numberPre = null,
        /** Mindestlänge der Gutschriftsnummer (wird mit führenden Nullen aufgefüllt). */
        public ?int $numberLength = null,
        /** Status der Gutschrift. */
        public ?CreditNoteStatus $status = null,
        /** Gutschriftsdatum. */
        public ?DateTimeImmutable $date = null,
        /** Komplette Adresse. */
        public ?string $address = null,
        /** Dokumentenüberschrift. */
        public ?string $title = null,
        /** Bezeichnung. */
        public ?string $label = null,
        /** Einleitungstext. */
        public ?string $intro = null,
        /** Anmerkungstext. */
        public ?string $note = null,
        /** Gesamtbetrag brutto. */
        public ?float $totalGross = null,
        /** Gesamtbetrag netto. */
        public ?float $totalNet = null,
        /** Preisbasis (Brutto- oder Netto-Preise). */
        public ?NetGross $netGross = null,
        /** Rabatt (absolut oder als Prozentwert, z. B. "10" oder "10%"). */
        public ?string $reduction = null,
        /** Gesamtbetrag brutto vor Rabatt. */
        public ?float $totalGrossUnreduced = null,
        /** Gesamtbetrag netto vor Rabatt. */
        public ?float $totalNetUnreduced = null,
        /** Bereits ausgezahlter Betrag. */
        public ?float $paidAmount = null,
        /** Noch offener Betrag. */
        public ?float $openAmount = null,
        /** Währung (ISO-Währungscode). */
        public ?string $currencyCode = null,
        /** Währungskurs (für Umrechnung in die Standard-Währung). */
        public ?float $quote = null,
        /** ID der Quell-Rechnung (Korrekturgutschrift). */
        public ?int $invoiceId = null,
        /** ID des Freitextes zur Belegung von title, label, intro und note. */
        public ?int $freeTextId = null,
        /** ID der Vorlage, mit der die Gutschrift abgeschlossen wird. */
        public ?int $templateId = null,
        /** Steuerzeilen der Gutschrift. */
        public array $taxes = [],
        /** Positionen der Gutschrift (nur bei GET /credit-notes/{id}). */
        public array $items = [],
        /** Zeitpunkt der letzten Änderung. */
        public ?DateTimeImmutable $updated = null,
        /** Freies Zusatzfeld (siehe Custom-Meta-Attribute). */
        public ?string $customfield = null,
        /** Summe der Rabatte. */
        public ?float $totalReduction = null,
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
            clientId: ScalarCaster::toIntOrNull($data['client_id'] ?? null) ?? 0,
            contactId: ScalarCaster::toIntOrNull($data['contact_id'] ?? null),
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            creditNoteNumber: ScalarCaster::toStringOrNull($data['credit_note_number'] ?? null),
            number: ScalarCaster::toIntOrNull($data['number'] ?? null),
            numberPre: ScalarCaster::toStringOrNull($data['number_pre'] ?? null),
            numberLength: ScalarCaster::toIntOrNull($data['number_length'] ?? null),
            status: CreditNoteStatus::tryFrom(ScalarCaster::toStringOrNull($data['status'] ?? null) ?? ''),
            date: ScalarCaster::toDateTimeOrNull($data['date'] ?? null),
            address: ScalarCaster::toStringOrNull($data['address'] ?? null),
            title: ScalarCaster::toStringOrNull($data['title'] ?? null),
            label: ScalarCaster::toStringOrNull($data['label'] ?? null),
            intro: ScalarCaster::toStringOrNull($data['intro'] ?? null),
            note: ScalarCaster::toStringOrNull($data['note'] ?? null),
            totalGross: ScalarCaster::toFloatOrNull($data['total_gross'] ?? null),
            totalNet: ScalarCaster::toFloatOrNull($data['total_net'] ?? null),
            netGross: NetGross::tryFrom(ScalarCaster::toStringOrNull($data['net_gross'] ?? null) ?? ''),
            reduction: ScalarCaster::toStringOrNull($data['reduction'] ?? null),
            totalGrossUnreduced: ScalarCaster::toFloatOrNull($data['total_gross_unreduced'] ?? null),
            totalNetUnreduced: ScalarCaster::toFloatOrNull($data['total_net_unreduced'] ?? null),
            paidAmount: ScalarCaster::toFloatOrNull($data['paid_amount'] ?? null),
            openAmount: ScalarCaster::toFloatOrNull($data['open_amount'] ?? null),
            currencyCode: ScalarCaster::toStringOrNull($data['currency_code'] ?? null),
            quote: ScalarCaster::toFloatOrNull($data['quote'] ?? null),
            invoiceId: ScalarCaster::toIntOrNull($data['invoice_id'] ?? null),
            freeTextId: ScalarCaster::toIntOrNull($data['free_text_id'] ?? null),
            templateId: ScalarCaster::toIntOrNull($data['template_id'] ?? null),
            taxes: ListCaster::toTaxList($data['taxes']['tax'] ?? null),
            items: ListCaster::toList($data['credit-note-items']['credit-note-item'] ?? null, 'id', CreditNoteItem::fromArray(...)),
            updated: ScalarCaster::toDateTimeOrNull($data['updated'] ?? null),
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
            totalReduction: ScalarCaster::toFloatOrNull($data['total_reduction'] ?? null),
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
        $data = [
            'id' => $this->id,
            'client_id' => $this->clientId,
            'contact_id' => $this->contactId,
            'created' => $this->created?->format(DATE_ATOM),
            'credit_note_number' => $this->creditNoteNumber,
            'number' => $this->number,
            'number_pre' => $this->numberPre,
            'number_length' => $this->numberLength,
            'status' => $this->status?->value,
            'date' => $this->date?->format('Y-m-d'),
            'address' => $this->address,
            'title' => $this->title,
            'label' => $this->label,
            'intro' => $this->intro,
            'note' => $this->note,
            'total_gross' => $this->totalGross,
            'total_net' => $this->totalNet,
            'net_gross' => $this->netGross?->value,
            'reduction' => $this->reduction,
            'total_gross_unreduced' => $this->totalGrossUnreduced,
            'total_net_unreduced' => $this->totalNetUnreduced,
            'paid_amount' => $this->paidAmount,
            'open_amount' => $this->openAmount,
            'currency_code' => $this->currencyCode,
            'quote' => $this->quote,
            'invoice_id' => $this->invoiceId,
            'free_text_id' => $this->freeTextId,
            'template_id' => $this->templateId,
            'updated' => $this->updated?->format(DATE_ATOM),
            'customfield' => $this->customfield,
            'total_reduction' => $this->totalReduction,
        ];

        if ([] !== $this->taxes) {
            $data['taxes'] = ['tax' => $this->taxes];
        }

        if ([] !== $this->items) {
            $data['credit-note-items'] = [
                'credit-note-item' => array_map(static fn (CreditNoteItem $item): array => $item->toArray(), $this->items),
            ];
        }

        return $data;
    }
}
