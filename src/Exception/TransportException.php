<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Exception;

use Override;
use Psr\Http\Client\RequestExceptionInterface;

/**
 * Der Request hat Billomat nicht erreicht oder es kam keine Antwort zurück
 * (DNS, Verbindungsabbruch, Timeout, …).
 *
 * Die Exception des PSR-18-Clients steckt in {@see getPrevious()}.
 */
class TransportException extends BillomatException
{
    /**
     * `true`, außer der PSR-18-Client hat den Request als ungültig abgelehnt
     * ({@see RequestExceptionInterface}) – dann hilft kein weiterer Versuch.
     */
    #[Override]
    public function isTransient(): bool
    {
        return !$this->getPrevious() instanceof RequestExceptionInterface;
    }

    /**
     * `true`: Bei einem Timeout kann Billomat den Request schon verarbeitet haben.
     * Nur ein vom PSR-18-Client abgelehnter Request ({@see RequestExceptionInterface}) wurde nie gesendet.
     */
    #[Override]
    public function mayHaveBeenProcessed(): bool
    {
        return !$this->getPrevious() instanceof RequestExceptionInterface;
    }
}
