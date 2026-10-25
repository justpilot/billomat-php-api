<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Exception;

use RuntimeException;

/**
 * Basis-Exception für alle Fehler im Billomat-SDK.
 *
 * {@see isTransient()} und {@see mayHaveBeenProcessed()} klassifizieren den Fehler,
 * ohne dass der Aufrufer Statuscodes oder die Exception des HTTP-Clients auswerten muss:
 *
 * ```php
 * try {
 *     $invoice = $billomat->invoices->create($options);
 * } catch (BillomatException $e) {
 *     if ($e->mayHaveBeenProcessed()) {
 *         // Vor einem erneuten POST prüfen, ob die Rechnung schon angelegt wurde.
 *     }
 *     if ($e->isTransient()) {
 *         $job->releaseIn($e instanceof HttpException ? $e->getRetryAfter() ?? 60 : 60);
 *     }
 * }
 * ```
 */
class BillomatException extends RuntimeException
{
    /**
     * Ob ein späterer Versuch Erfolg haben kann (Rate-Limit, Billomat überlastet, Netzwerkfehler).
     *
     * Die Retries aus {@see \Justpilot\Billomat\Config\RetryPolicy} sind dann schon ausgeschöpft.
     */
    public function isTransient(): bool
    {
        return false;
    }

    /**
     * Ob Billomat den Request trotz des Fehlers verarbeitet haben kann.
     *
     * `false` heißt: Billomat hat ihn sicher abgelehnt (4xx), ein erneuter Schreib-Request
     * legt nichts doppelt an. Bei `true` vorher per `get()`/`list()` prüfen.
     */
    public function mayHaveBeenProcessed(): bool
    {
        return true;
    }
}
