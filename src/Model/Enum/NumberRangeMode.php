<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model\Enum;

/**
 * Einfluss des Nummernpräfix auf die Nummerierung.
 *
 * - IGNORE_PREFIX: Nummerierung unabhängig vom Präfix
 * - CONSIDER_PREFIX: eigener Nummernkreis pro Präfix
 */
enum NumberRangeMode: string
{
    use FromApi;

    case IGNORE_PREFIX = 'IGNORE_PREFIX';
    case CONSIDER_PREFIX = 'CONSIDER_PREFIX';
}
