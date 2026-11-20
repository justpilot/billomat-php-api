<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model\Enum;

/**
 * Kriterien zur Aggregation in GET /incomings?group_by=...
 *
 * Mehrere Werte können kombiniert werden (Komma-getrennt). Die Reihenfolge
 * bestimmt die Aggregationsreihenfolge ("supplier,year" gruppiert zuerst nach
 * Lieferant und dann nach Jahr).
 *
 * Doku: https://www.billomat.com/api/eingangsrechnungen/ (Abschnitt "Eingangsrechnungen aggregiert auflisten")
 */
enum IncomingGroupBy: string
{
    use FromApi;

    case SUPPLIER = 'supplier';
    case STATUS = 'status';
    case DAY = 'day';
    case WEEK = 'week';
    case MONTH = 'month';
    case YEAR = 'year';
}
