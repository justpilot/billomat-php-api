<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model\Enum;

enum InvoiceStatus: string
{
    use FromApi;

    /** Entwurf */
    case DRAFT = 'DRAFT';

    /** offen */
    case OPEN = 'OPEN';

    /** überfällig */
    case OVERDUE = 'OVERDUE';

    /** bezahlt */
    case PAID = 'PAID';

    /** storniert */
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
