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
    use FromApi;

    case DRAFT = 'DRAFT';
    case OPEN = 'OPEN';
    case OVERDUE = 'OVERDUE';
    case PAID = 'PAID';
    case CANCELED = 'CANCELED';

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
