<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use DateTimeImmutable;

/**
 * Typisierter Payload für POST /letters.
 *
 * Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren ignoriert.
 *
 * Doku:
 * https://www.billomat.com/en/api/letters/
 *
 * Generiert aus tools/codegen/definitions/letters.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class LetterCreateOptions implements WriteOptions
{
    public function __construct(
        /**
         * ID des Kunden.
         *
         * Billomat-Feld: client_id
         * Typ: INT
         */
        public int $clientId,
        /**
         * ID des Kontakts.
         *
         * Billomat-Feld: contact_id
         * Typ: INT
         */
        public ?int $contactId = null,
        /**
         * Laufende Nummer (ohne Präfix).
         *
         * Billomat-Feld: number
         */
        public ?int $number = null,
        /**
         * Präfix der Briefnummer.
         *
         * Billomat-Feld: number_pre
         */
        public ?string $numberPre = null,
        /**
         * Mindestlänge der Briefnummer.
         *
         * Billomat-Feld: number_length
         */
        public ?int $numberLength = null,
        /**
         * Briefdatum.
         *
         * Format: YYYY-MM-DD
         *
         * Billomat-Feld: date
         * Typ: DATE
         * Default: heute
         */
        public ?DateTimeImmutable $date = null,
        /**
         * Komplette Briefadresse.
         *
         * Laut Doku veraltet – Billomat verarbeitet das Feld nicht mehr, bitte contact_id oder client_id verwenden.
         *
         * Billomat-Feld: address
         * Typ: ALNUM
         * Default: Adresse des Kunden
         */
        public ?string $address = null,
        /**
         * Dokumentenüberschrift.
         *
         * Billomat-Feld: subject
         * Typ: ALNUM
         */
        public ?string $subject = null,
        /**
         * Bezeichnung.
         *
         * Billomat-Feld: label
         * Typ: ALNUM
         */
        public ?string $label = null,
        /**
         * Brieftext.
         *
         * Billomat-Feld: intro
         * Typ: ALNUM
         * Default: Wert aus Einstellungen
         */
        public ?string $intro = null,
        /**
         * Anmerkung.
         *
         * Billomat-Feld: note
         */
        public ?string $note = null,
        /**
         * ID der Vorlage, mit der der Brief abgeschlossen wird.
         *
         * Billomat-Feld: template_id
         * Typ: INT
         * Default: ID der Standardvorlage
         */
        public ?int $templateId = null,
        /**
         * ID des Lieferanten.
         *
         * Wird statt `client_id` verwendet, wenn der Brief an einen Lieferanten geht.
         *
         * Billomat-Feld: supplier_id
         * Typ: INT
         */
        public ?int $supplierId = null,
        /**
         * ID des Freitextes zur Belegung von subject, label und intro.
         *
         * Billomat-Feld: free_text_id
         * Typ: INT
         */
        public ?int $freeTextId = null,
    ) {
    }

    /** Kopie mit geändertem `clientId`. */
    public function withClientId(int $clientId): self
    {
        return clone($this, ['clientId' => $clientId]);
    }

    /** Kopie mit geändertem `contactId`. */
    public function withContactId(?int $contactId): self
    {
        return clone($this, ['contactId' => $contactId]);
    }

    /** Kopie mit geändertem `number`. */
    public function withNumber(?int $number): self
    {
        return clone($this, ['number' => $number]);
    }

    /** Kopie mit geändertem `numberPre`. */
    public function withNumberPre(?string $numberPre): self
    {
        return clone($this, ['numberPre' => $numberPre]);
    }

    /** Kopie mit geändertem `numberLength`. */
    public function withNumberLength(?int $numberLength): self
    {
        return clone($this, ['numberLength' => $numberLength]);
    }

    /** Kopie mit geändertem `date`. */
    public function withDate(?DateTimeImmutable $date): self
    {
        return clone($this, ['date' => $date]);
    }

    /** Kopie mit geändertem `address`. */
    public function withAddress(?string $address): self
    {
        return clone($this, ['address' => $address]);
    }

    /** Kopie mit geändertem `subject`. */
    public function withSubject(?string $subject): self
    {
        return clone($this, ['subject' => $subject]);
    }

    /** Kopie mit geändertem `label`. */
    public function withLabel(?string $label): self
    {
        return clone($this, ['label' => $label]);
    }

    /** Kopie mit geändertem `intro`. */
    public function withIntro(?string $intro): self
    {
        return clone($this, ['intro' => $intro]);
    }

    /** Kopie mit geändertem `note`. */
    public function withNote(?string $note): self
    {
        return clone($this, ['note' => $note]);
    }

    /** Kopie mit geändertem `templateId`. */
    public function withTemplateId(?int $templateId): self
    {
        return clone($this, ['templateId' => $templateId]);
    }

    /** Kopie mit geändertem `supplierId`. */
    public function withSupplierId(?int $supplierId): self
    {
        return clone($this, ['supplierId' => $supplierId]);
    }

    /** Kopie mit geändertem `freeTextId`. */
    public function withFreeTextId(?int $freeTextId): self
    {
        return clone($this, ['freeTextId' => $freeTextId]);
    }

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'client_id' => $this->clientId,
            'contact_id' => $this->contactId,
            'supplier_id' => $this->supplierId,
            'address' => $this->address,
            'number_pre' => $this->numberPre,
            'number' => $this->number,
            'number_length' => $this->numberLength,
            'date' => $this->date?->format('Y-m-d'),
            'subject' => $this->subject,
            'label' => $this->label,
            'intro' => $this->intro,
            'note' => $this->note,
            'free_text_id' => $this->freeTextId,
            'template_id' => $this->templateId,
        ], static fn (mixed $v): bool => null !== $v);
    }
}
