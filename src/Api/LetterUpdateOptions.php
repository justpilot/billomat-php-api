<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use DateTimeImmutable;

/**
 * Typisierter Payload für PUT /letters/{id}.
 *
 * Nur gesetzte (nicht-null) Felder werden serialisiert (Partial Update).
 * Ein Brief kann nur im Entwurfs-Status (DRAFT) bearbeitet werden.
 *
 * Doku:
 * https://www.billomat.com/en/api/letters/
 *
 * Generiert aus tools/codegen/definitions/letters.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class LetterUpdateOptions implements WriteOptions
{
    public function __construct(
        /**
         * ID des Kunden.
         *
         * Billomat-Feld: client_id
         * Typ: INT
         */
        public int|Clear|null $clientId = null,
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
         * Präfix der Briefnummer.
         *
         * Billomat-Feld: number_pre
         */
        public string|Clear|null $numberPre = null,
        /**
         * Mindestlänge der Briefnummer.
         *
         * Billomat-Feld: number_length
         */
        public int|Clear|null $numberLength = null,
        /**
         * Briefdatum.
         *
         * Format: YYYY-MM-DD
         *
         * Billomat-Feld: date
         * Typ: DATE
         */
        public DateTimeImmutable|Clear|null $date = null,
        /**
         * Komplette Briefadresse.
         *
         * Laut Doku veraltet – Billomat verarbeitet das Feld nicht mehr, bitte contact_id oder client_id verwenden.
         *
         * Billomat-Feld: address
         * Typ: ALNUM
         */
        public string|Clear|null $address = null,
        /**
         * Dokumentenüberschrift.
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
         * Brieftext.
         *
         * Billomat-Feld: intro
         * Typ: ALNUM
         */
        public string|Clear|null $intro = null,
        /**
         * Anmerkung.
         *
         * Billomat-Feld: note
         */
        public string|Clear|null $note = null,
        /**
         * ID der Vorlage, mit der der Brief abgeschlossen wird.
         *
         * Billomat-Feld: template_id
         * Typ: INT
         */
        public int|Clear|null $templateId = null,
        /**
         * ID des Lieferanten.
         *
         * Wird statt `client_id` verwendet, wenn der Brief an einen Lieferanten geht.
         *
         * Billomat-Feld: supplier_id
         * Typ: INT
         */
        public int|Clear|null $supplierId = null,
        /**
         * ID des Freitextes zur Belegung von subject, label und intro.
         *
         * Billomat-Feld: free_text_id
         * Typ: INT
         */
        public int|Clear|null $freeTextId = null,
    ) {
    }

    /** Kopie mit geändertem `clientId`. */
    public function withClientId(int|Clear|null $clientId): self
    {
        return clone($this, ['clientId' => $clientId]);
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

    /** Kopie mit geändertem `templateId`. */
    public function withTemplateId(int|Clear|null $templateId): self
    {
        return clone($this, ['templateId' => $templateId]);
    }

    /** Kopie mit geändertem `supplierId`. */
    public function withSupplierId(int|Clear|null $supplierId): self
    {
        return clone($this, ['supplierId' => $supplierId]);
    }

    /** Kopie mit geändertem `freeTextId`. */
    public function withFreeTextId(int|Clear|null $freeTextId): self
    {
        return clone($this, ['freeTextId' => $freeTextId]);
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
                'client_id' => $this->clientId,
                'contact_id' => $this->contactId,
                'supplier_id' => $this->supplierId,
                'address' => $this->address,
                'number_pre' => $this->numberPre,
                'number' => $this->number,
                'number_length' => $this->numberLength,
                'date' => $this->date instanceof DateTimeImmutable ? $this->date->format('Y-m-d') : $this->date,
                'subject' => $this->subject,
                'label' => $this->label,
                'intro' => $this->intro,
                'note' => $this->note,
                'free_text_id' => $this->freeTextId,
                'template_id' => $this->templateId,
            ], static fn (mixed $v): bool => null !== $v),
        );
    }
}
