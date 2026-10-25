<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Event;

use Psr\Http\Message\RequestInterface;

/**
 * Ein Request wird gleich gesendet; bei Retries einmal pro Versuch.
 *
 * Der Request enthält den Header `X-BillomatApiKey` (und ggf. `X-AppSecret`) – nicht ungefiltert loggen.
 */
final readonly class RequestStarted
{
    public function __construct(
        public RequestInterface $request,
        /** Nummer des Versuchs, ab 1. */
        public int $attempt,
    ) {
    }
}
