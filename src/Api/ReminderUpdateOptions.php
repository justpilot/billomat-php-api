<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use DateTimeImmutable;

/**
 * Typisierter Payload für PUT /reminders/{id}.
 *
 * Nur gesetzte (nicht-null) Felder werden serialisiert (Partial Update).
 *
 * Doku:
 * https://www.billomat.com/en/api/reminders/
 *
 * Generiert aus tools/codegen/definitions/reminders.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final class ReminderUpdateOptions implements WriteOptions
{
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
     */
    public ?DateTimeImmutable $date = null;

    /**
     * Tage bis zur Fälligkeit.
     *
     * Billomat-Feld: due_days
     * Typ: INT
     */
    public ?int $dueDays = null;

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
     * Komplette Mahnungsadresse.
     *
     * Laut Doku veraltet – Billomat verarbeitet das Feld nicht mehr, bitte contact_id oder client_id verwenden.
     *
     * Billomat-Feld: address
     * Typ: ALNUM
     */
    public ?string $address = null;

    /**
     * Betreff.
     *
     * Billomat-Feld: subject
     * Typ: ALNUM
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
