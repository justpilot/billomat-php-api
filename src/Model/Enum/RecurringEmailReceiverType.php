<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model\Enum;

/**
 * Typ eines E-Mail-Empfängers bei einer Abo-Rechnung.
 *
 * Doku: https://www.billomat.com/api/abo-rechnungen/email-empfaenger/
 */
enum RecurringEmailReceiverType: string
{
    case TO = 'To';
    case CC = 'Cc';
    case BCC = 'Bcc';

    /**
     * Toleriert Klein- und Großschreibung beim Hydrieren aus API-Responses.
     * Billomat liefert "To"/"Cc"/"Bcc"; alte Daten/Doku verwendeten teilweise
     * Kleinbuchstaben.
     */
    public static function fromApi(string $value): self
    {
        return self::tryFrom($value)
            ?? self::tryFrom(ucfirst(strtolower($value)))
            ?? self::TO;
    }
}
