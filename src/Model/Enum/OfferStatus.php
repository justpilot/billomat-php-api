<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model\Enum;

/**
 * Status eines Angebots (Offer / Estimate).
 *
 * Doku: https://www.billomat.com/en/api/estimates/
 *
 * Lebenszyklus:
 *  - DRAFT  → über `complete()` → OPEN
 *  - OPEN   → über `win()`  → ACCEPTED
 *  - OPEN   → über `lose()` → REJECTED
 *  - OPEN   → über `clear()`→ CLEARED
 *  - OPEN/ACCEPTED → über `cancel()` → CANCELED
 */
enum OfferStatus: string
{
    /** Entwurf */
    case DRAFT = 'DRAFT';

    /** offen / versendet */
    case OPEN = 'OPEN';

    /** angenommen */
    case ACCEPTED = 'ACCEPTED';

    /** abgelehnt */
    case REJECTED = 'REJECTED';

    /** erledigt (z. B. in Rechnung übernommen) */
    case CLEARED = 'CLEARED';

    /** storniert */
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
            self::ACCEPTED => 'Angenommen',
            self::REJECTED => 'Abgelehnt',
            self::CLEARED => 'Erledigt',
            self::CANCELED => 'Storniert',
        };
    }
}
