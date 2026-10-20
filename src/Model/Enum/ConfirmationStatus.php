<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model\Enum;

/**
 * Status einer Auftragsbestätigung (Confirmation).
 *
 * Doku: https://www.billomat.com/en/api/confirmations/
 *
 * Lebenszyklus:
 *  - DRAFT  → über `complete()` → OPEN bzw. COMPLETED (Billomat liefert
 *    nach `complete()` inzwischen COMPLETED, Stand 2026-10)
 *  - OPEN   → über `clear()`    → CLEARED (z.B. in Rechnung übernommen)
 *  - OPEN/CLEARED → über `cancel()` → CANCELED
 */
enum ConfirmationStatus: string
{
    case DRAFT = 'DRAFT';
    case OPEN = 'OPEN';
    case COMPLETED = 'COMPLETED';
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
            self::COMPLETED => 'Abgeschlossen',
            self::CLEARED => 'Erledigt',
            self::CANCELED => 'Storniert',
        };
    }
}
