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
    case TO = 'to';
    case CC = 'cc';
    case BCC = 'bcc';
}
