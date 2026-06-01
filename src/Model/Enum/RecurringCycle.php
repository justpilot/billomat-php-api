<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model\Enum;

/**
 * Wiederholungs-Intervall einer Abo-Rechnung.
 *
 * Wird zusammen mit `cycle_number` ausgewertet, z. B. cycle=MONTHLY, cycle_number=2
 * → alle 2 Monate.
 *
 * Doku: https://www.billomat.com/api/abo-rechnungen/
 */
enum RecurringCycle: string
{
    case DAILY = 'DAILY';
    case WEEKLY = 'WEEKLY';
    case MONTHLY = 'MONTHLY';
    case YEARLY = 'YEARLY';
}
