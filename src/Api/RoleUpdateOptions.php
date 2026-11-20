<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use InvalidArgumentException;
use Justpilot\Billomat\Model\Enum\AccessLevel;
use Justpilot\Billomat\Model\Role;

/**
 * Payload für PUT /roles/{id}.
 *
 * Nur gesetzte Felder werden gesendet: ein `null`-Name bleibt unverändert, Bereiche
 * ohne Eintrag in `permissions` behalten ihre Rechte. Ein Bereich mit `null` verliert
 * seine Rechte (gesendet als leerer String).
 *
 * Dokumentation: https://www.billomat.com/api/einstellungen/rollen/
 */
final readonly class RoleUpdateOptions implements WriteOptions
{
    /**
     * @param string|null                     $name        Name der Rolle
     * @param array<string, AccessLevel|null> $permissions Bereich → neue Zugriffsstufe, `null` entzieht die Rechte
     *
     * @throws InvalidArgumentException bei einem unbekannten Bereich (Billomat würde ihn stillschweigend ignorieren)
     */
    public function __construct(
        public ?string $name = null,
        public array $permissions = [],
    ) {
        self::assertAreas($permissions);
    }

    /** Kopie mit geändertem `name`. */
    public function withName(?string $name): self
    {
        return clone($this, ['name' => $name]);
    }

    /**
     * Kopie mit ersetzter Rechte-Map.
     *
     * @param array<string, AccessLevel|null> $permissions
     *
     * @throws InvalidArgumentException bei einem unbekannten Bereich
     */
    public function withPermissions(array $permissions): self
    {
        self::assertAreas($permissions);

        return clone($this, ['permissions' => $permissions]);
    }

    /**
     * Kopie mit gesetzter Stufe für einen Bereich; `null` entzieht die Rechte.
     *
     * @throws InvalidArgumentException bei einem unbekannten Bereich
     */
    public function withPermission(string $area, ?AccessLevel $level): self
    {
        return $this->withPermissions([...$this->permissions, $area => $level]);
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            ...array_map(static fn (?AccessLevel $level): string => $level->value ?? '', $this->permissions),
        ], static fn (mixed $value): bool => null !== $value);
    }

    /**
     * @internal gemeinsame Prüfung für {@see RoleCreateOptions} und {@see RoleUpdateOptions}
     *
     * @param array<array-key, mixed> $permissions
     *
     * @throws InvalidArgumentException
     */
    public static function assertAreas(array $permissions): void
    {
        foreach (array_keys($permissions) as $area) {
            if (!Role::isArea((string) $area)) {
                throw new InvalidArgumentException(\sprintf('Unknown role permission area "%s"; known areas: %s and settings_*.', $area, implode(', ', Role::AREAS)));
            }
        }
    }
}
