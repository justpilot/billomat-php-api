<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\FreeTextType;

/**
 * Typisierter Payload für PUT /free-texts/{id} gemäß Billomat-API.
 *
 * Nur gesetzte (nicht-null) Felder werden serialisiert (Partial Update).
 *
 * Doku:
 * https://www.billomat.com/en/api/settings/free-texts/
 *
 * Generiert aus tools/codegen/definitions/free-texts.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class FreeTextUpdateOptions implements WriteOptions
{
    public function __construct(
        /**
         * Überschrift bzw. Betreff.
         *
         * Billomat-Feld: title
         * Typ: ALNUM
         */
        public string|Clear|null $title = null,
        /**
         * Bezeichnung.
         *
         * Billomat-Feld: label
         * Typ: ALNUM
         */
        public string|Clear|null $label = null,
        /**
         * Einleitungstext bzw. Brieftext.
         *
         * Billomat-Feld: intro
         * Typ: ALNUM
         */
        public string|Clear|null $intro = null,
        /**
         * Anmerkungen.
         *
         * Billomat-Feld: note
         * Typ: ALNUM
         */
        public string|Clear|null $note = null,
        /**
         * Dokumenttyp, für den der Freitext gilt.
         *
         * Mögliche Werte: INVOICE, CORRECTION, OFFER, CONFIRMATION, CREDIT_NOTE, DELIVERY_NOTE, LETTER
         *
         * Billomat-Feld: type
         * Typ: ALNUM („INVOICE“, „CORRECTION“, „OFFER“, „CONFIRMATION“, „CREDIT_NOTE“, „DELIVERY_NOTE“, „LETTER“)
         */
        public FreeTextType|Clear|null $type = null,
        /**
         * Name der Textvorlage.
         *
         * Billomat-Feld: name
         * Typ: ALNUM
         */
        public string|Clear|null $name = null,
        /**
         * Standard-Freitext für den Dokumenttyp?
         *
         * Billomat-Feld: is_default
         * Typ: BOOL
         */
        public bool|Clear|null $isDefault = null,
    ) {
    }

    /** Kopie mit geändertem `title`. */
    public function withTitle(string|Clear|null $title): self
    {
        return clone($this, ['title' => $title]);
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

    /** Kopie mit geändertem `type`. */
    public function withType(FreeTextType|Clear|null $type): self
    {
        return clone($this, ['type' => $type]);
    }

    /** Kopie mit geändertem `name`. */
    public function withName(string|Clear|null $name): self
    {
        return clone($this, ['name' => $name]);
    }

    /** Kopie mit geändertem `isDefault`. */
    public function withIsDefault(bool|Clear|null $isDefault): self
    {
        return clone($this, ['isDefault' => $isDefault]);
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
                'title' => $this->title,
                'label' => $this->label,
                'intro' => $this->intro,
                'note' => $this->note,
                'type' => $this->type instanceof FreeTextType ? $this->type->value : $this->type,
                'name' => $this->name,
                'is_default' => \is_bool($this->isDefault) ? ($this->isDefault ? 1 : 0) : $this->isDefault,
            ], static fn (mixed $v): bool => null !== $v),
        );
    }
}
