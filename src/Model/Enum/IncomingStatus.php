<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model\Enum;

/**
 * Status einer Eingangsrechnung (Incoming).
 *
 * Doku: https://www.billomat.com/en/api/incomings/
 */
enum IncomingStatus: string
{
    case DRAFT = 'DRAFT';
    case OPEN = 'OPEN';
    case OVERDUE = 'OVERDUE';
    case PAID = 'PAID';
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
            self::OVERDUE => 'Überfällig',
            self::PAID => 'Bezahlt',
            self::CANCELED => 'Storniert',
        };
    }
}
