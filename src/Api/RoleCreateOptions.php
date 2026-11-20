<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use InvalidArgumentException;
use Justpilot\Billomat\Model\Enum\AccessLevel;
use Justpilot\Billomat\Model\Role;

/**
 * Payload für POST /roles.
 *
 * Die Rechte stehen wie im Read-Model {@see Role} als Map Bereich → Stufe
 * (`['articles' => AccessLevel::DELETE]`) und gehen als einzelne Felder an Billomat.
 * Bereiche ohne Eintrag bekommen keine Rechte. Welche Stufen ein Bereich erlaubt,
 * prüft Billomat (die `settings_*`-Bereiche kennen nur `UPDATE`; sonst HTTP 400).
 *
 * Dokumentation: https://www.billomat.com/api/einstellungen/rollen/
 */
final readonly class RoleCreateOptions implements WriteOptions
{
    /**
     * @param string                     $name        Name der Rolle
     * @param array<string, AccessLevel> $permissions Bereich (z. B. `articles`) → Zugriffsstufe
     *
     * @throws InvalidArgumentException bei einem unbekannten Bereich (Billomat würde ihn stillschweigend ignorieren)
     */
    public function __construct(
        public string $name,
        public array $permissions = [],
    ) {
        RoleUpdateOptions::assertAreas($permissions);
    }

    /** Kopie mit geändertem `name`. */
    public function withName(string $name): self
    {
        return clone($this, ['name' => $name]);
    }

    /**
     * Kopie mit ersetzter Rechte-Map.
     *
     * @param array<string, AccessLevel> $permissions
     *
     * @throws InvalidArgumentException bei einem unbekannten Bereich
     */
    public function withPermissions(array $permissions): self
    {
        RoleUpdateOptions::assertAreas($permissions);

        return clone($this, ['permissions' => $permissions]);
    }

    /**
     * Kopie mit gesetzter Stufe für einen Bereich.
     *
     * @throws InvalidArgumentException bei einem unbekannten Bereich
     */
    public function withPermission(string $area, AccessLevel $level): self
    {
        return $this->withPermissions([...$this->permissions, $area => $level]);
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            ...array_map(static fn (AccessLevel $level): string => $level->value, $this->permissions),
        ];
    }
}
