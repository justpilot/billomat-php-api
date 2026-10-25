<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ListCaster;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\DeliveryNoteStatus;

use const DATE_ATOM;

/**
 * Repräsentiert einen Lieferschein (Delivery Note) aus der Billomat-API.
 *
 * Dokumentation:
 * https://www.billomat.com/en/api/delivery-notes/
 *
 * Generiert aus tools/codegen/definitions/delivery-notes.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class DeliveryNote
{
    /**
     * @param list<TaxSummary>       $taxes
     * @param list<DeliveryNoteItem> $items
     */
    public function __construct(
        /** Interne Billomat-ID des Lieferscheins. */
        public ?int $id,
        /** ID des Kunden. */
        public int $clientId,
        /** ID des Kontakts. */
        public ?int $contactId = null,
        /** Erstellungszeitpunkt des Lieferscheins. */
        public ?DateTimeImmutable $created = null,
        /** Lieferscheinnummer (wird beim Abschließen vergeben). */
        public ?string $deliveryNoteNumber = null,
        /** Laufende Nummer (ohne Präfix). */
        public ?int $number = null,
        /** Präfix der Lieferscheinnummer. */
        public ?string $numberPre = null,
        /** Mindestlänge der Lieferscheinnummer (wird mit führenden Nullen aufgefüllt). */
        public ?int $numberLength = null,
        /** Status des Lieferscheins. */
        public ?DeliveryNoteStatus $status = null,
        /** Lieferscheindatum. */
        public ?DateTimeImmutable $date = null,
        /**
         * Komplette Lieferscheinadresse.
         *
         * Laut Doku veraltet – Billomat verarbeitet das Feld nicht mehr, bitte contact_id oder client_id verwenden.
         */
        public ?string $address = null,
        /** Dokumentenüberschrift. */
        public ?string $title = null,
        /** Bezeichnung. */
        public ?string $label = null,
        /** Einleitungstext. */
        public ?string $intro = null,
        /** Anmerkungstext. */
        public ?string $note = null,
        /** ID der Quell-Rechnung. */
        public ?int $invoiceId = null,
        /** ID der Quell-Auftragsbestätigung. */
        public ?int $confirmationId = null,
        /** ID des Freitextes zur Belegung von title, label, intro und note. */
        public ?int $freeTextId = null,
        /** ID der Vorlage, mit der der Lieferschein abgeschlossen wird. */
        public ?int $templateId = null,
        /** Steuerzeilen des Lieferscheins. */
        public array $taxes = [],
        /** Positionen des Lieferscheins (nur bei GET /delivery-notes/{id}). */
        public array $items = [],
        /** Zeitpunkt der letzten Änderung. */
        public ?DateTimeImmutable $updated = null,
        /** Freies Zusatzfeld (siehe Custom-Meta-Attribute). */
        public ?string $customfield = null,
        /** ID des Quell-Angebots. */
        public ?int $offerId = null,
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
            deliveryNoteNumber: ScalarCaster::toStringOrNull($data['delivery_note_number'] ?? null),
            number: ScalarCaster::toIntOrNull($data['number'] ?? null),
            numberPre: ScalarCaster::toStringOrNull($data['number_pre'] ?? null),
            numberLength: ScalarCaster::toIntOrNull($data['number_length'] ?? null),
            status: DeliveryNoteStatus::tryFrom(ScalarCaster::toStringOrNull($data['status'] ?? null) ?? ''),
            date: ScalarCaster::toDateTimeOrNull($data['date'] ?? null),
            address: ScalarCaster::toStringOrNull($data['address'] ?? null),
            title: ScalarCaster::toStringOrNull($data['title'] ?? null),
            label: ScalarCaster::toStringOrNull($data['label'] ?? null),
            intro: ScalarCaster::toStringOrNull($data['intro'] ?? null),
            note: ScalarCaster::toStringOrNull($data['note'] ?? null),
            invoiceId: ScalarCaster::toIntOrNull($data['invoice_id'] ?? null),
            confirmationId: ScalarCaster::toIntOrNull($data['confirmation_id'] ?? null),
            freeTextId: ScalarCaster::toIntOrNull($data['free_text_id'] ?? null),
            templateId: ScalarCaster::toIntOrNull($data['template_id'] ?? null),
            taxes: ListCaster::toList($data['taxes']['tax'] ?? null, 'name', TaxSummary::fromArray(...)),
            items: ListCaster::toList($data['delivery-note-items']['delivery-note-item'] ?? null, 'id', DeliveryNoteItem::fromArray(...)),
            updated: ScalarCaster::toDateTimeOrNull($data['updated'] ?? null),
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
            offerId: ScalarCaster::toIntOrNull($data['offer_id'] ?? null),
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
            'delivery_note_number' => $this->deliveryNoteNumber,
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
            'invoice_id' => $this->invoiceId,
            'confirmation_id' => $this->confirmationId,
            'free_text_id' => $this->freeTextId,
            'template_id' => $this->templateId,
            'updated' => $this->updated?->format(DATE_ATOM),
            'customfield' => $this->customfield,
            'offer_id' => $this->offerId,
        ];

        if ([] !== $this->taxes) {
            $data['taxes'] = [
                'tax' => array_map(static fn (TaxSummary $tax): array => $tax->toArray(), $this->taxes),
            ];
        }

        if ([] !== $this->items) {
            $data['delivery-note-items'] = [
                'delivery-note-item' => array_map(static fn (DeliveryNoteItem $item): array => $item->toArray(), $this->items),
            ];
        }

        return $data;
    }
}
