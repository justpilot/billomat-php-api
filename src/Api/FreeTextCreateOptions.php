<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\FreeTextType;

/**
 * Typisierter Payload für POST /free-texts gemäß Billomat-API.
 *
 * Doku:
 * https://www.billomat.com/en/api/settings/free-texts/
 *
 * Generiert aus tools/codegen/definitions/free-texts.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class FreeTextCreateOptions implements WriteOptions
{
    public function __construct(
        /**
         * Dokumenttyp, für den der Freitext gilt.
         *
         * Mögliche Werte: INVOICE, CORRECTION, OFFER, CONFIRMATION, CREDIT_NOTE, DELIVERY_NOTE, LETTER
         *
         * Billomat-Feld: type
         * Typ: ALNUM („INVOICE“, „CORRECTION“, „OFFER“, „CONFIRMATION“, „CREDIT_NOTE“, „DELIVERY_NOTE“, „LETTER“)
         */
        public FreeTextType $type,
        /**
         * Name der Textvorlage.
         *
         * Billomat-Feld: name
         * Typ: ALNUM
         */
        public string $name,
        /**
         * Überschrift bzw. Betreff.
         *
         * Billomat-Feld: title
         * Typ: ALNUM
         */
        public ?string $title = null,
        /**
         * Bezeichnung.
         *
         * Billomat-Feld: label
         * Typ: ALNUM
         */
        public ?string $label = null,
        /**
         * Einleitungstext bzw. Brieftext.
         *
         * Billomat-Feld: intro
         * Typ: ALNUM
         */
        public ?string $intro = null,
        /**
         * Anmerkungen.
         *
         * Billomat-Feld: note
         * Typ: ALNUM
         */
        public ?string $note = null,
        /**
         * Standard-Freitext für den Dokumenttyp?
         *
         * Billomat-Feld: is_default
         * Typ: BOOL
         * Default: nein
         */
        public ?bool $isDefault = null,
    ) {
    }

    /** Kopie mit geändertem `type`. */
    public function withType(FreeTextType $type): self
    {
        return clone($this, ['type' => $type]);
    }

    /** Kopie mit geändertem `name`. */
    public function withName(string $name): self
    {
        return clone($this, ['name' => $name]);
    }

    /** Kopie mit geändertem `title`. */
    public function withTitle(?string $title): self
    {
        return clone($this, ['title' => $title]);
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

    /** Kopie mit geändertem `isDefault`. */
    public function withIsDefault(?bool $isDefault): self
    {
        return clone($this, ['isDefault' => $isDefault]);
    }

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'title' => $this->title,
            'label' => $this->label,
            'intro' => $this->intro,
            'note' => $this->note,
            'type' => $this->type->value,
            'name' => $this->name,
            'is_default' => null === $this->isDefault ? null : ($this->isDefault ? 1 : 0),
        ], static fn (mixed $v): bool => null !== $v);
    }
}
