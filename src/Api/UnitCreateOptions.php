<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Typisierter Payload für POST /units gemäß Billomat-API.
 *
 * Doku:
 * https://www.billomat.com/en/api/settings/units/
 *
 * Generiert aus tools/codegen/definitions/units.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class UnitCreateOptions implements WriteOptions
{
    public function __construct(
        /**
         * Bezeichnung der Einheit.
         *
         * Billomat-Feld: name
         * Typ: ALNUM
         */
        public string $name,
        /**
         * Einheitencode nach UN/ECE Recommendation 20 für E-Rechnungen, z. B. "C62" (Stück) oder "HUR" (Stunde).
         *
         * Von Billomat geliefert und angenommen, aber nicht dokumentiert.
         *
         * Billomat-Feld: unece_code
         */
        public ?string $uneceCode = null,
    ) {
    }

    /** Kopie mit geändertem `name`. */
    public function withName(string $name): self
    {
        return clone($this, ['name' => $name]);
    }

    /** Kopie mit geändertem `uneceCode`. */
    public function withUneceCode(?string $uneceCode): self
    {
        return clone($this, ['uneceCode' => $uneceCode]);
    }

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'unece_code' => $this->uneceCode,
        ], static fn (mixed $v): bool => null !== $v);
    }
}
