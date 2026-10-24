<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\LetterStatus;

use const DATE_ATOM;

/**
 * Repräsentiert einen Brief (Letter) aus der Billomat-API.
 *
 * Dokumentation:
 * https://www.billomat.com/en/api/letters/
 *
 * Generiert aus tools/codegen/definitions/letters.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class Letter
{
    public function __construct(
        /** Interne Billomat-ID des Briefs. */
        public ?int $id,
        /** ID des Kunden. */
        public int $clientId,
        /** ID des Kontakts. */
        public ?int $contactId = null,
        /** Erstellungszeitpunkt des Briefs. */
        public ?DateTimeImmutable $created = null,
        /**
         * Briefnummer.
         *
         * @deprecated since 3.1 – Billomat liefert `letter_number` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.
         */
        public ?string $letterNumber = null,
        /**
         * Laufende Nummer (ohne Präfix).
         *
         * @deprecated since 3.1 – Billomat liefert `number` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.
         */
        public ?int $number = null,
        /**
         * Präfix der Briefnummer.
         *
         * @deprecated since 3.1 – Billomat liefert `number_pre` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.
         */
        public ?string $numberPre = null,
        /**
         * Mindestlänge der Briefnummer.
         *
         * @deprecated since 3.1 – Billomat liefert `number_length` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.
         */
        public ?int $numberLength = null,
        /** Status des Briefs. */
        public ?LetterStatus $status = null,
        /** Briefdatum. */
        public ?DateTimeImmutable $date = null,
        /**
         * Komplette Briefadresse.
         *
         * Laut Doku veraltet – Billomat verarbeitet das Feld nicht mehr, bitte contact_id oder client_id verwenden.
         */
        public ?string $address = null,
        /** Dokumentenüberschrift. */
        public ?string $subject = null,
        /** Bezeichnung. */
        public ?string $label = null,
        /** Brieftext. */
        public ?string $intro = null,
        /**
         * Anmerkung.
         *
         * @deprecated since 3.1 – Billomat liefert `note` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.
         */
        public ?string $note = null,
        /** ID der Vorlage, mit der der Brief abgeschlossen wird. */
        public ?int $templateId = null,
        /**
         * Persönliche URL zum Kundenportal.
         *
         * @deprecated since 3.1 – Billomat liefert `customerportal_url` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.
         */
        public ?string $customerportalUrl = null,
        /** Zeitpunkt der letzten Änderung. */
        public ?DateTimeImmutable $updated = null,
        /** Freies Zusatzfeld (siehe Custom-Meta-Attribute). */
        public ?string $customfield = null,
        /**
         * ID des Lieferanten.
         *
         * Wird statt `client_id` verwendet, wenn der Brief an einen Lieferanten geht.
         */
        public ?int $supplierId = null,
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
            letterNumber: ScalarCaster::toStringOrNull($data['letter_number'] ?? null),
            number: ScalarCaster::toIntOrNull($data['number'] ?? null),
            numberPre: ScalarCaster::toStringOrNull($data['number_pre'] ?? null),
            numberLength: ScalarCaster::toIntOrNull($data['number_length'] ?? null),
            status: LetterStatus::tryFrom(ScalarCaster::toStringOrNull($data['status'] ?? null) ?? ''),
            date: ScalarCaster::toDateTimeOrNull($data['date'] ?? null),
            address: ScalarCaster::toStringOrNull($data['address'] ?? null),
            subject: ScalarCaster::toStringOrNull($data['subject'] ?? null),
            label: ScalarCaster::toStringOrNull($data['label'] ?? null),
            intro: ScalarCaster::toStringOrNull($data['intro'] ?? null),
            note: ScalarCaster::toStringOrNull($data['note'] ?? null),
            templateId: ScalarCaster::toIntOrNull($data['template_id'] ?? null),
            customerportalUrl: ScalarCaster::toStringOrNull($data['customerportal_url'] ?? null),
            updated: ScalarCaster::toDateTimeOrNull($data['updated'] ?? null),
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
            supplierId: ScalarCaster::toIntOrNull($data['supplier_id'] ?? null),
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
            'client_id' => $this->clientId,
            'contact_id' => $this->contactId,
            'created' => $this->created?->format(DATE_ATOM),
            'letter_number' => $this->letterNumber,
            'number' => $this->number,
            'number_pre' => $this->numberPre,
            'number_length' => $this->numberLength,
            'status' => $this->status?->value,
            'date' => $this->date?->format('Y-m-d'),
            'address' => $this->address,
            'subject' => $this->subject,
            'label' => $this->label,
            'intro' => $this->intro,
            'note' => $this->note,
            'template_id' => $this->templateId,
            'customerportal_url' => $this->customerportalUrl,
            'updated' => $this->updated?->format(DATE_ATOM),
            'customfield' => $this->customfield,
            'supplier_id' => $this->supplierId,
        ];
    }
}
