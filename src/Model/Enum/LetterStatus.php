<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model\Enum;

/**
 * Status eines Briefs (Letter).
 *
 * Doku: https://www.billomat.com/en/api/letters/
 */
enum LetterStatus: string
{
    case DRAFT = 'DRAFT';
    case OPEN = 'OPEN';
    case CLEARED = 'CLEARED';
    case CANCELED = 'CANCELED';

    public static function fromApi(?string $status): ?self
    {
        return null === $status ? null : self::tryFrom($status);
    }

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Entwurf',
            self::OPEN => 'Offen',
            self::CLEARED => 'Erledigt',
            self::CANCELED => 'Storniert',
        };
    }
}
