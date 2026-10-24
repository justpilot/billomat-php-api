<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ListCaster;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\ReminderStatus;

use const DATE_ATOM;

/**
 * Repräsentiert eine Mahnung (Reminder) aus der Billomat-API.
 *
 * Eine Mahnung referenziert immer eine bestehende Rechnung.
 *
 * Dokumentation:
 * https://www.billomat.com/en/api/reminders/
 *
 * Generiert aus tools/codegen/definitions/reminders.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class Reminder
{
    /** @param list<ReminderItem> $items */
    public function __construct(
        /** Interne Billomat-ID der Mahnung. */
        public ?int $id,
        /** ID des Kunden. */
        public int $clientId,
        /** ID des Kontakts. */
        public ?int $contactId = null,
        /** ID der überfälligen Rechnung. */
        public ?int $invoiceId = null,
        /** Erstellungszeitpunkt der Mahnung. */
        public ?DateTimeImmutable $created = null,
        /**
         * Mahnungsnummer.
         *
         * @deprecated since 3.1 – Billomat liefert `reminder_number` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.
         */
        public ?string $reminderNumber = null,
        /**
         * Laufende Nummer (ohne Präfix).
         *
         * @deprecated since 3.1 – Billomat liefert `number` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.
         */
        public ?int $number = null,
        /**
         * Präfix der Mahnungsnummer.
         *
         * @deprecated since 3.1 – Billomat liefert `number_pre` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.
         */
        public ?string $numberPre = null,
        /**
         * Mindestlänge der Mahnungsnummer.
         *
         * @deprecated since 3.1 – Billomat liefert `number_length` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.
         */
        public ?int $numberLength = null,
        /** Status der Mahnung. */
        public ?ReminderStatus $status = null,
        /** Mahnungsdatum. */
        public ?DateTimeImmutable $date = null,
        /** Tage bis zur Fälligkeit. */
        public ?int $dueDays = null,
        /** Fälligkeitsdatum. */
        public ?DateTimeImmutable $dueDate = null,
        /**
         * Komplette Mahnungsadresse.
         *
         * Laut Doku veraltet – Billomat verarbeitet das Feld nicht mehr, bitte contact_id oder client_id verwenden.
         */
        public ?string $address = null,
        /** Betreff. */
        public ?string $subject = null,
        /** Bezeichnung. */
        public ?string $label = null,
        /** Einleitungstext. */
        public ?string $intro = null,
        /** Anmerkungstext. */
        public ?string $note = null,
        /** Gesamtbetrag brutto. */
        public ?float $totalGross = null,
        /**
         * Gesamtbetrag netto.
         *
         * @deprecated since 3.1 – Billomat liefert `total_net` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.
         */
        public ?float $totalNet = null,
        /** Währung (ISO-Währungscode). */
        public ?string $currencyCode = null,
        /**
         * Umrechnungskurs.
         *
         * @deprecated since 3.1 – Billomat liefert `quote` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.
         */
        public ?float $quote = null,
        /** ID des Mahntextes (Mahnstufe). */
        public ?int $reminderTextId = null,
        /** ID der Vorlage, mit der die Mahnung abgeschlossen wird. */
        public ?int $templateId = null,
        /** Positionen der Mahnung (nur bei GET /reminders/{id}). */
        public array $items = [],
        /** Zeitpunkt der letzten Änderung. */
        public ?DateTimeImmutable $updated = null,
        /** Freies Zusatzfeld (siehe Custom-Meta-Attribute). */
        public ?string $customfield = null,
        /** Bereits bezahlter Betrag der Rechnung. */
        public ?float $paidAmount = null,
        /** Offener Betrag der Rechnung. */
        public ?float $openAmount = null,
        /** Mahnstufe. */
        public ?int $reminderLevel = null,
        /** Bezeichnung der Mahnstufe. */
        public ?string $reminderLevelName = null,
        /** Von Billomat geliefert, aber nicht dokumentiert. */
        public ?bool $isOld = null,
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
            invoiceId: ScalarCaster::toIntOrNull($data['invoice_id'] ?? null),
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            reminderNumber: ScalarCaster::toStringOrNull($data['reminder_number'] ?? null),
            number: ScalarCaster::toIntOrNull($data['number'] ?? null),
            numberPre: ScalarCaster::toStringOrNull($data['number_pre'] ?? null),
            numberLength: ScalarCaster::toIntOrNull($data['number_length'] ?? null),
            status: ReminderStatus::tryFrom(ScalarCaster::toStringOrNull($data['status'] ?? null) ?? ''),
            date: ScalarCaster::toDateTimeOrNull($data['date'] ?? null),
            dueDays: ScalarCaster::toIntOrNull($data['due_days'] ?? null),
            dueDate: ScalarCaster::toDateTimeOrNull($data['due_date'] ?? null),
            address: ScalarCaster::toStringOrNull($data['address'] ?? null),
            subject: ScalarCaster::toStringOrNull($data['subject'] ?? null),
            label: ScalarCaster::toStringOrNull($data['label'] ?? null),
            intro: ScalarCaster::toStringOrNull($data['intro'] ?? null),
            note: ScalarCaster::toStringOrNull($data['note'] ?? null),
            totalGross: ScalarCaster::toFloatOrNull($data['total_gross'] ?? null),
            totalNet: ScalarCaster::toFloatOrNull($data['total_net'] ?? null),
            currencyCode: ScalarCaster::toStringOrNull($data['currency_code'] ?? null),
            quote: ScalarCaster::toFloatOrNull($data['quote'] ?? null),
            reminderTextId: ScalarCaster::toIntOrNull($data['reminder_text_id'] ?? null),
            templateId: ScalarCaster::toIntOrNull($data['template_id'] ?? null),
            items: ListCaster::toList($data['reminder-items']['reminder-item'] ?? null, 'id', ReminderItem::fromArray(...)),
            updated: ScalarCaster::toDateTimeOrNull($data['updated'] ?? null),
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
            paidAmount: ScalarCaster::toFloatOrNull($data['paid_amount'] ?? null),
            openAmount: ScalarCaster::toFloatOrNull($data['open_amount'] ?? null),
            reminderLevel: ScalarCaster::toIntOrNull($data['reminder_level'] ?? null),
            reminderLevelName: ScalarCaster::toStringOrNull($data['reminder_level_name'] ?? null),
            isOld: ScalarCaster::toBoolOrNull($data['is_old'] ?? null),
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
            'invoice_id' => $this->invoiceId,
            'created' => $this->created?->format(DATE_ATOM),
            'reminder_number' => $this->reminderNumber,
            'number' => $this->number,
            'number_pre' => $this->numberPre,
            'number_length' => $this->numberLength,
            'status' => $this->status?->value,
            'date' => $this->date?->format('Y-m-d'),
            'due_days' => $this->dueDays,
            'due_date' => $this->dueDate?->format('Y-m-d'),
            'address' => $this->address,
            'subject' => $this->subject,
            'label' => $this->label,
            'intro' => $this->intro,
            'note' => $this->note,
            'total_gross' => $this->totalGross,
            'total_net' => $this->totalNet,
            'currency_code' => $this->currencyCode,
            'quote' => $this->quote,
            'reminder_text_id' => $this->reminderTextId,
            'template_id' => $this->templateId,
            'updated' => $this->updated?->format(DATE_ATOM),
            'customfield' => $this->customfield,
            'paid_amount' => $this->paidAmount,
            'open_amount' => $this->openAmount,
            'reminder_level' => $this->reminderLevel,
            'reminder_level_name' => $this->reminderLevelName,
            'is_old' => $this->isOld,
        ];

        if ([] !== $this->items) {
            $data['reminder-items'] = [
                'reminder-item' => array_map(static fn (ReminderItem $item): array => $item->toArray(), $this->items),
            ];
        }

        return $data;
    }
}
