<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use DateTimeImmutable;

/**
 * Typisierter Payload für POST /reminders.
 *
 * Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren ignoriert.
 *
 * Doku:
 * https://www.billomat.com/en/api/reminders/
 *
 * Generiert aus tools/codegen/definitions/reminders.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final class ReminderCreateOptions
{
    public function __construct(
        /**
         * ID der überfälligen Rechnung.
         *
         * Billomat-Feld: invoice_id
         * Typ: INT
         */
        public int $invoiceId,
    ) {
    }

    /**
     * ID des Kontakts.
     *
     * Billomat-Feld: contact_id
     * Typ: INT
     */
    public ?int $contactId = null;

    /**
     * Laufende Nummer (ohne Präfix).
     *
     * Billomat-Feld: number
     */
    public ?int $number = null;

    /**
     * Präfix der Mahnungsnummer.
     *
     * Billomat-Feld: number_pre
     */
    public ?string $numberPre = null;

    /**
     * Mindestlänge der Mahnungsnummer.
     *
     * Billomat-Feld: number_length
     */
    public ?int $numberLength = null;

    /**
     * Mahnungsdatum.
     *
     * Format: YYYY-MM-DD
     *
     * Billomat-Feld: date
     * Typ: DATE
     * Default: heute
     */
    public ?DateTimeImmutable $date = null;

    /**
     * Tage bis zur Fälligkeit.
     *
     * Billomat-Feld: due_days
     * Typ: INT
     * Default: Fälligkeit in Tagen aus Einstellungen
     */
    public ?int $dueDays = null;

    /**
     * Fälligkeitsdatum.
     *
     * Format: YYYY-MM-DD
     *
     * Billomat-Feld: due_date
     * Typ: DATE
     * Default: date + due_days
     */
    public ?DateTimeImmutable $dueDate = null;

    /**
     * Komplette Mahnungsadresse.
     *
     * Laut Doku veraltet – Billomat verarbeitet das Feld nicht mehr, bitte contact_id oder client_id verwenden.
     *
     * Billomat-Feld: address
     * Typ: ALNUM
     * Default: Adresse aus der Rechnung
     */
    public ?string $address = null;

    /**
     * Betreff.
     *
     * Billomat-Feld: subject
     * Typ: ALNUM
     * Default: Betreff der nächsten Mahnstufe (falls vorhanden)
     */
    public ?string $subject = null;

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
     * Typ: ALNUM
     * Default: Einleitungstext der nächsten Mahnstufe (falls vorhanden)
     */
    public ?string $intro = null;

    /**
     * Anmerkungstext.
     *
     * Billomat-Feld: note
     * Typ: ALNUM
     * Default: Anmerkungstext der nächsten Mahnstufe (falls vorhanden)
     */
    public ?string $note = null;

    /**
     * ID des Mahntextes (Mahnstufe).
     *
     * Billomat-Feld: reminder_text_id
     */
    public ?int $reminderTextId = null;

    /**
     * ID der Vorlage, mit der die Mahnung abgeschlossen wird.
     *
     * Billomat-Feld: template_id
     * Typ: INT
     * Default: ID der Standardvorlage
     */
    public ?int $templateId = null;

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [
            'invoice_id' => $this->invoiceId,
            'contact_id' => $this->contactId,
            'address' => $this->address,
            'number_pre' => $this->numberPre,
            'number' => $this->number,
            'number_length' => $this->numberLength,
            'date' => $this->date?->format('Y-m-d'),
            'due_days' => $this->dueDays,
            'due_date' => $this->dueDate?->format('Y-m-d'),
            'subject' => $this->subject,
            'label' => $this->label,
            'intro' => $this->intro,
            'note' => $this->note,
            'reminder_text_id' => $this->reminderTextId,
            'template_id' => $this->templateId,
        ];

        // Null-Felder entfernen
        return array_filter($data, static fn (mixed $v): bool => null !== $v);
    }
}
