<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model\Enum;

/**
 * Status eines Briefs (Letter).
 *
 * Doku: https://www.billomat.com/en/api/letters/
 *
 * Nach `complete()` liefert Billomat COMPLETED (Stand 2026-10).
 */
enum LetterStatus: string
{
    use FromApi;

    case DRAFT = 'DRAFT';
    case OPEN = 'OPEN';
    case COMPLETED = 'COMPLETED';
    case CLEARED = 'CLEARED';
    case CANCELED = 'CANCELED';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Entwurf',
            self::OPEN => 'Offen',
            self::COMPLETED => 'Abgeschlossen',
            self::CLEARED => 'Erledigt',
            self::CANCELED => 'Storniert',
        };
    }
}
