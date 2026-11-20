<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Markiert ein Feld in Update-Options als „bewusst leeren“.
 *
 * In `*UpdateOptions` bedeutet `null`: Feld nicht senden, Billomat lässt den Wert unverändert.
 * `Clear::Field` sendet das Feld als JSON-`null`, und Billomat leert es.
 *
 * ```php
 * $billomat->clients->update($id, new ClientUpdateOptions(note: Clear::Field));
 * ```
 */
enum Clear
{
    case Field;
}
