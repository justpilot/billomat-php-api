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
 *  - OPEN   → über `win()`  → WON
 *  - OPEN   → über `lose()` → LOST
 *  - OPEN   → über `clear()`→ CLEARED
 *  - OPEN/WON → über `cancel()` → CANCELED
 */
enum OfferStatus: string
{
    use FromApi;

    /** Entwurf */
    case DRAFT = 'DRAFT';

    /** offen / versendet */
    case OPEN = 'OPEN';

    /** gewonnen (angenommen) */
    case WON = 'WON';

    /** verloren (abgelehnt) */
    case LOST = 'LOST';

    /** erledigt (z. B. in Rechnung übernommen) */
    case CLEARED = 'CLEARED';

    /** storniert */
    case CANCELED = 'CANCELED';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Entwurf',
            self::OPEN => 'Offen',
            self::WON => 'Gewonnen',
            self::LOST => 'Verloren',
            self::CLEARED => 'Erledigt',
            self::CANCELED => 'Storniert',
        };
    }
}
