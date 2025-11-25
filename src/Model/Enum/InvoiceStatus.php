<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model\Enum;

enum InvoiceStatus: string
{
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

    /**
     * Convert raw API string into enum.
     */
    public static function fromApi(?string $status): ?self
    {
        if ($status === null) {
            return null;
        }

        return self::tryFrom($status) ?? null;
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