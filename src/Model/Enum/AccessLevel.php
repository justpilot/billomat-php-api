<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model\Enum;

/**
 * Zugriffsstufe pro Bereich in Rollen (`Role::$permissions`) und Benutzerrechten (`User::$rights`).
 *
 * Die Stufen bauen aufeinander auf: `UPDATE` schließt `READ` ein, `DELETE` beide.
 * Ohne Zugriff liefert Billomat einen leeren String; der Bereich fehlt dann in der Map.
 *
 * Quelle:
 * https://www.billomat.com/api/einstellungen/rollen/
 */
enum AccessLevel: string
{
    use FromApi;

    case READ = 'READ';
    case UPDATE = 'UPDATE';
    case DELETE = 'DELETE';

    /**
     * Deutsche Labels für die UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::READ => 'Lesen',
            self::UPDATE => 'Bearbeiten',
            self::DELETE => 'Löschen',
        };
    }

    /**
     * Erlaubt diese Stufe mindestens `$required`? `DELETE` erlaubt auch `UPDATE` und `READ`.
     */
    public function allows(self $required): bool
    {
        return match ($required) {
            self::READ => true,
            self::UPDATE => self::READ !== $this,
            self::DELETE => self::DELETE === $this,
        };
    }
}
