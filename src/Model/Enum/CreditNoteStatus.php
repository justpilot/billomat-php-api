<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model\Enum;

/**
 * Status einer Gutschrift (Credit Note).
 *
 * Doku: https://www.billomat.com/en/api/credit-notes/
 */
enum CreditNoteStatus: string
{
    use FromApi;

    case DRAFT = 'DRAFT';
    case OPEN = 'OPEN';
    case PAID = 'PAID';
    case CANCELED = 'CANCELED';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Entwurf',
            self::OPEN => 'Offen',
            self::PAID => 'Bezahlt',
            self::CANCELED => 'Storniert',
        };
    }
}
