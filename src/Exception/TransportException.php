<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Exception;

/**
 * Der Request hat Billomat nicht erreicht oder es kam keine Antwort zurück
 * (DNS, Verbindungsabbruch, Timeout, …).
 *
 * Die Exception des PSR-18-Clients steckt in {@see getPrevious()}.
 */
class TransportException extends BillomatException
{
}
