<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Exception;

/**
 * Ein eingehender Webhook-Request lässt sich nicht auswerten: unbekanntes Event,
 * fehlender Header, Body ohne JSON oder ohne erwarteten Envelope.
 *
 * Der Empfänger sollte dann mit 4xx antworten; Billomat versucht es später erneut.
 */
class InvalidWebhookException extends BillomatException
{
}
