<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model\Enum;

/**
 * Status eines Lieferscheins (Delivery Note).
 *
 * Doku: https://www.billomat.com/en/api/delivery-notes/
 *
 * Nach `complete()` liefert Billomat CREATED (Stand 2026-10).
 */
enum DeliveryNoteStatus: string
{
    case DRAFT = 'DRAFT';
    case OPEN = 'OPEN';
    case CREATED = 'CREATED';
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
            self::CREATED => 'Erstellt',
            self::CLEARED => 'Erledigt',
            self::CANCELED => 'Storniert',
        };
    }
}
