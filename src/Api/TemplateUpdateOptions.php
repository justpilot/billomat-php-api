<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Typisierter Payload für PUT /templates/{id} gemäß Billomat-API.
 *
 * Nur gesetzte (nicht-null) Felder werden serialisiert (Partial Update).
 *
 * Doku:
 * https://www.billomat.com/api/einstellungen/vorlagen/
 *
 * Generiert aus tools/codegen/definitions/templates.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class TemplateUpdateOptions implements WriteOptions
{
    public function __construct(
        /**
         * Bezeichnung der Vorlage.
         *
         * Billomat-Feld: name
         * Typ: ALNUM
         */
        public string|Clear|null $name = null,
        /**
         * Standard-Vorlage für den Dokumenttyp?
         *
         * Billomat-Feld: is_default
         * Typ: BOOL
         */
        public bool|Clear|null $isDefault = null,
    ) {
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
                'name' => $this->name,
                'is_default' => \is_bool($this->isDefault) ? ($this->isDefault ? 1 : 0) : $this->isDefault,
            ], static fn (mixed $v): bool => null !== $v),
        );
    }
}
