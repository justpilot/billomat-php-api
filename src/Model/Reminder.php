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
    /** Billomat-Felder, die das Model abbildet. */
    private const array FIELDS = [
        'id' => true,
        'client_id' => true,
        'contact_id' => true,
        'invoice_id' => true,
        'created' => true,
        'status' => true,
        'date' => true,
        'due_days' => true,
        'due_date' => true,
        'address' => true,
        'subject' => true,
        'label' => true,
        'intro' => true,
        'note' => true,
        'total_gross' => true,
        'currency_code' => true,
        'reminder_text_id' => true,
        'template_id' => true,
        'reminder-items' => true,
        'updated' => true,
        'customfield' => true,
        'paid_amount' => true,
        'open_amount' => true,
        'reminder_level' => true,
        'reminder_level_name' => true,
        'is_old' => true,
    ];

    /**
     * @param list<ReminderItem>   $items
     * @param array<string, mixed> $extra
     */
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
        public ?Money $totalGross = null,
        /** Währung (ISO-Währungscode). */
        public ?string $currencyCode = null,
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
        public ?Money $paidAmount = null,
        /** Offener Betrag der Rechnung. */
        public ?Money $openAmount = null,
        /** Mahnstufe. */
        public ?int $reminderLevel = null,
        /** Bezeichnung der Mahnstufe. */
        public ?string $reminderLevelName = null,
        /** Von Billomat geliefert, aber nicht dokumentiert. */
        public ?bool $isOld = null,
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
            clientId: ScalarCaster::toIntOrNull($data['client_id'] ?? null) ?? 0,
            contactId: ScalarCaster::toIntOrNull($data['contact_id'] ?? null),
            invoiceId: ScalarCaster::toIntOrNull($data['invoice_id'] ?? null),
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            status: ReminderStatus::tryFrom(ScalarCaster::toStringOrNull($data['status'] ?? null) ?? ''),
            date: ScalarCaster::toDateTimeOrNull($data['date'] ?? null),
            dueDays: ScalarCaster::toIntOrNull($data['due_days'] ?? null),
            dueDate: ScalarCaster::toDateTimeOrNull($data['due_date'] ?? null),
            address: ScalarCaster::toStringOrNull($data['address'] ?? null),
            subject: ScalarCaster::toStringOrNull($data['subject'] ?? null),
            label: ScalarCaster::toStringOrNull($data['label'] ?? null),
            intro: ScalarCaster::toStringOrNull($data['intro'] ?? null),
            note: ScalarCaster::toStringOrNull($data['note'] ?? null),
            totalGross: ScalarCaster::toMoneyOrNull($data['total_gross'] ?? null),
            currencyCode: ScalarCaster::toStringOrNull($data['currency_code'] ?? null),
            reminderTextId: ScalarCaster::toIntOrNull($data['reminder_text_id'] ?? null),
            templateId: ScalarCaster::toIntOrNull($data['template_id'] ?? null),
            items: ListCaster::toList($data['reminder-items']['reminder-item'] ?? null, 'id', ReminderItem::fromArray(...)),
            updated: ScalarCaster::toDateTimeOrNull($data['updated'] ?? null),
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
            paidAmount: ScalarCaster::toMoneyOrNull($data['paid_amount'] ?? null),
            openAmount: ScalarCaster::toMoneyOrNull($data['open_amount'] ?? null),
            reminderLevel: ScalarCaster::toIntOrNull($data['reminder_level'] ?? null),
            reminderLevelName: ScalarCaster::toStringOrNull($data['reminder_level_name'] ?? null),
            isOld: ScalarCaster::toBoolOrNull($data['is_old'] ?? null),
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
        $data = [
            ...$this->extra,
            'id' => $this->id,
            'client_id' => $this->clientId,
            'contact_id' => $this->contactId,
            'invoice_id' => $this->invoiceId,
            'created' => $this->created?->format(DATE_ATOM),
            'status' => $this->status?->value,
            'date' => $this->date?->format('Y-m-d'),
            'due_days' => $this->dueDays,
            'due_date' => $this->dueDate?->format('Y-m-d'),
            'address' => $this->address,
            'subject' => $this->subject,
            'label' => $this->label,
            'intro' => $this->intro,
            'note' => $this->note,
            'total_gross' => $this->totalGross?->toString(),
            'currency_code' => $this->currencyCode,
            'reminder_text_id' => $this->reminderTextId,
            'template_id' => $this->templateId,
            'updated' => $this->updated?->format(DATE_ATOM),
            'customfield' => $this->customfield,
            'paid_amount' => $this->paidAmount?->toString(),
            'open_amount' => $this->openAmount?->toString(),
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
