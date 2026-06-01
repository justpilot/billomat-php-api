<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model\Enum;

/**
 * Kriterien zur Aggregation in GET /invoices?group_by=...
 *
 * Mehrere Werte können kombiniert werden (Komma-getrennt). Die Reihenfolge
 * bestimmt die Aggregationsreihenfolge ("client,year" gruppiert zuerst nach
 * Kunde und dann nach Jahr).
 *
 * Doku: https://www.billomat.com/api/rechnungen/ (Abschnitt "Rechnungen aggregiert auflisten")
 */
enum InvoiceGroupBy: string
{
    case CLIENT = 'client';
    case STATUS = 'status';
    case DAY = 'day';
    case WEEK = 'week';
    case MONTH = 'month';
    case YEAR = 'year';
}
