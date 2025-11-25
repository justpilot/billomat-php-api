<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model\Enum;

enum InvoiceStatus: string
{
    case DRAFT = 'DRAFT';
    case OPEN = 'OPEN';
    case OVERDUE = 'OVERDUE';
    case PAID = 'PAID';
    case VOID = 'VOID';
    case CANCELLED = 'CANCELLED';

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
}