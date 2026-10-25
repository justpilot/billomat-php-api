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
final readonly class ReminderUpdateOptions implements WriteOptions
{
    public function __construct(
        /**
         * ID des Kontakts.
         *
         * Billomat-Feld: contact_id
         * Typ: INT
         */
        public int|Clear|null $contactId = null,
        /**
         * Laufende Nummer (ohne Präfix).
         *
         * Billomat-Feld: number
         */
        public int|Clear|null $number = null,
        /**
         * Präfix der Mahnungsnummer.
         *
         * Billomat-Feld: number_pre
         */
        public string|Clear|null $numberPre = null,
        /**
         * Mindestlänge der Mahnungsnummer.
         *
         * Billomat-Feld: number_length
         */
        public int|Clear|null $numberLength = null,
        /**
         * Mahnungsdatum.
         *
         * Format: YYYY-MM-DD
         *
         * Billomat-Feld: date
         * Typ: DATE
         */
        public DateTimeImmutable|Clear|null $date = null,
        /**
         * Tage bis zur Fälligkeit.
         *
         * Billomat-Feld: due_days
         * Typ: INT
         */
        public int|Clear|null $dueDays = null,
        /**
         * Fälligkeitsdatum.
         *
         * Format: YYYY-MM-DD
         *
         * Billomat-Feld: due_date
         * Typ: DATE
         */
        public DateTimeImmutable|Clear|null $dueDate = null,
        /**
         * Komplette Mahnungsadresse.
         *
         * Laut Doku veraltet – Billomat verarbeitet das Feld nicht mehr, bitte contact_id oder client_id verwenden.
         *
         * Billomat-Feld: address
         * Typ: ALNUM
         */
        public string|Clear|null $address = null,
        /**
         * Betreff.
         *
         * Billomat-Feld: subject
         * Typ: ALNUM
         */
        public string|Clear|null $subject = null,
        /**
         * Bezeichnung.
         *
         * Billomat-Feld: label
         * Typ: ALNUM
         */
        public string|Clear|null $label = null,
        /**
         * Einleitungstext.
         *
         * Billomat-Feld: intro
         * Typ: ALNUM
         */
        public string|Clear|null $intro = null,
        /**
         * Anmerkungstext.
         *
         * Billomat-Feld: note
         * Typ: ALNUM
         */
        public string|Clear|null $note = null,
        /**
         * ID des Mahntextes (Mahnstufe).
         *
         * Billomat-Feld: reminder_text_id
         */
        public int|Clear|null $reminderTextId = null,
        /**
         * ID der Vorlage, mit der die Mahnung abgeschlossen wird.
         *
         * Billomat-Feld: template_id
         * Typ: INT
         */
        public int|Clear|null $templateId = null,
    ) {
    }

    /** Kopie mit geändertem `contactId`. */
    public function withContactId(int|Clear|null $contactId): self
    {
        return clone($this, ['contactId' => $contactId]);
    }

    /** Kopie mit geändertem `number`. */
    public function withNumber(int|Clear|null $number): self
    {
        return clone($this, ['number' => $number]);
    }

    /** Kopie mit geändertem `numberPre`. */
    public function withNumberPre(string|Clear|null $numberPre): self
    {
        return clone($this, ['numberPre' => $numberPre]);
    }

    /** Kopie mit geändertem `numberLength`. */
    public function withNumberLength(int|Clear|null $numberLength): self
    {
        return clone($this, ['numberLength' => $numberLength]);
    }

    /** Kopie mit geändertem `date`. */
    public function withDate(DateTimeImmutable|Clear|null $date): self
    {
        return clone($this, ['date' => $date]);
    }

    /** Kopie mit geändertem `dueDays`. */
    public function withDueDays(int|Clear|null $dueDays): self
    {
        return clone($this, ['dueDays' => $dueDays]);
    }

    /** Kopie mit geändertem `dueDate`. */
    public function withDueDate(DateTimeImmutable|Clear|null $dueDate): self
    {
        return clone($this, ['dueDate' => $dueDate]);
    }

    /** Kopie mit geändertem `address`. */
    public function withAddress(string|Clear|null $address): self
    {
        return clone($this, ['address' => $address]);
    }

    /** Kopie mit geändertem `subject`. */
    public function withSubject(string|Clear|null $subject): self
    {
        return clone($this, ['subject' => $subject]);
    }

    /** Kopie mit geändertem `label`. */
    public function withLabel(string|Clear|null $label): self
    {
        return clone($this, ['label' => $label]);
    }

    /** Kopie mit geändertem `intro`. */
    public function withIntro(string|Clear|null $intro): self
    {
        return clone($this, ['intro' => $intro]);
    }

    /** Kopie mit geändertem `note`. */
    public function withNote(string|Clear|null $note): self
    {
        return clone($this, ['note' => $note]);
    }

    /** Kopie mit geändertem `reminderTextId`. */
    public function withReminderTextId(int|Clear|null $reminderTextId): self
    {
        return clone($this, ['reminderTextId' => $reminderTextId]);
    }

    /** Kopie mit geändertem `templateId`. */
    public function withTemplateId(int|Clear|null $templateId): self
    {
        return clone($this, ['templateId' => $templateId]);
    }

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
     * Nicht gesetzte Felder (null) fehlen im Payload, {@see Clear::Field} wird als null gesendet und leert das Feld.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return array_map(
            static fn (mixed $v): mixed => $v instanceof Clear ? null : $v,
            array_filter([
                'contact_id' => $this->contactId,
                'address' => $this->address,
                'number_pre' => $this->numberPre,
                'number' => $this->number,
                'number_length' => $this->numberLength,
                'date' => $this->date instanceof DateTimeImmutable ? $this->date->format('Y-m-d') : $this->date,
                'due_days' => $this->dueDays,
                'due_date' => $this->dueDate instanceof DateTimeImmutable ? $this->dueDate->format('Y-m-d') : $this->dueDate,
                'subject' => $this->subject,
                'label' => $this->label,
                'intro' => $this->intro,
                'note' => $this->note,
                'reminder_text_id' => $this->reminderTextId,
                'template_id' => $this->templateId,
            ], static fn (mixed $v): bool => null !== $v),
        );
    }
}
