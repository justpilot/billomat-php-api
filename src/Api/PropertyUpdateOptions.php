<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\PropertyType;

/**
 * Geteilte Options-Klasse für PUT der Attribut-Definitionen
 * (article-properties, client-properties, supplier-properties,
 * incoming-properties, user-properties).
 *
 * Nur gesetzte (nicht-null) Felder werden serialisiert (Partial Update).
 *
 * Doku:
 * https://www.billomat.com/en/api/settings/client-properties/
 *
 * Generiert aus tools/codegen/definitions/client-properties.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class PropertyUpdateOptions implements WriteOptions
{
    public function __construct(
        /**
         * Name des Attributs.
         *
         * Billomat-Feld: name
         * Typ: ALNUM
         */
        public string|Clear|null $name = null,
        /**
         * Typ des Attributs.
         *
         * TEXTFIELD = einzeiliges Textfeld, TEXTAREA = mehrzeiliges Textfeld, CHECKBOX = Checkbox
         *
         * Billomat-Feld: type
         * Typ: ALNUM
         */
        public PropertyType|Clear|null $type = null,
        /**
         * Standardwert.
         *
         * Billomat-Feld: default_value
         * Typ: ALNUM
         */
        public string|Clear|null $defaultValue = null,
        /**
         * Sortierposition.
         *
         * Billomat-Feld: position
         */
        public int|Clear|null $position = null,
        /**
         * Standardwert verwenden, wenn kein anderer Wert gesetzt ist?
         *
         * Billomat-Feld: is_nvl
         * Typ: BOOL
         */
        public bool|Clear|null $isNvl = null,
    ) {
    }

    /** Kopie mit geändertem `name`. */
    public function withName(string|Clear|null $name): self
    {
        return clone($this, ['name' => $name]);
    }

    /** Kopie mit geändertem `type`. */
    public function withType(PropertyType|Clear|null $type): self
    {
        return clone($this, ['type' => $type]);
    }

    /** Kopie mit geändertem `defaultValue`. */
    public function withDefaultValue(string|Clear|null $defaultValue): self
    {
        return clone($this, ['defaultValue' => $defaultValue]);
    }

    /** Kopie mit geändertem `position`. */
    public function withPosition(int|Clear|null $position): self
    {
        return clone($this, ['position' => $position]);
    }

    /** Kopie mit geändertem `isNvl`. */
    public function withIsNvl(bool|Clear|null $isNvl): self
    {
        return clone($this, ['isNvl' => $isNvl]);
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
                'name' => $this->name,
                'type' => $this->type instanceof PropertyType ? $this->type->value : $this->type,
                'default_value' => $this->defaultValue,
                'position' => $this->position,
                'is_nvl' => \is_bool($this->isNvl) ? ($this->isNvl ? 1 : 0) : $this->isNvl,
            ], static fn (mixed $v): bool => null !== $v),
        );
    }
}
