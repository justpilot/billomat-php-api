<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Exception;

use Override;

/**
 * Ein eingehender Webhook-Request lässt sich nicht auswerten: unbekanntes Event,
 * fehlender Header, Body ohne JSON oder ohne erwarteten Envelope.
 *
 * Der Empfänger sollte dann mit 4xx antworten; Billomat versucht es später erneut.
 */
final class InvalidWebhookException extends BillomatException
{
    /**
     * Hinter der Exception steckt kein Request an Billomat, also wurde auch nichts verarbeitet.
     */
    #[Override]
    public function mayHaveBeenProcessed(): bool
    {
        return false;
    }
}
