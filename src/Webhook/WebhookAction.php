<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Webhook;

/**
 * Aktion eines Webhook-Events, der Teil hinter dem Punkt (`invoice.status` → `STATUS`).
 */
enum WebhookAction: string
{
    case CREATE = 'create';
    case UPDATE = 'update';
    case DELETE = 'delete';

    /** Statuswechsel eines Dokuments (abgeschlossen, bezahlt, storniert, …). */
    case STATUS = 'status';

    /** Dokument wurde versendet (E-Mail, Post, …). */
    case SEND = 'send';
}
