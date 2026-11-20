<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Typisierter Payload für PUT /units/{id} gemäß Billomat-API.
 *
 * Nur gesetzte (nicht-null) Felder werden serialisiert (Partial Update).
 *
 * Doku:
 * https://www.billomat.com/en/api/settings/units/
 *
 * Generiert aus tools/codegen/definitions/units.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class UnitUpdateOptions implements WriteOptions
{
    public function __construct(
        /**
         * Bezeichnung der Einheit.
         *
         * Billomat-Feld: name
         * Typ: ALNUM
         */
        public string|Clear|null $name = null,
    ) {
    }

    /** Kopie mit geändertem `name`. */
    public function withName(string|Clear|null $name): self
    {
        return clone($this, ['name' => $name]);
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
            ], static fn (mixed $v): bool => null !== $v),
        );
    }
}
