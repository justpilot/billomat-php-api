<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Event;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Billomat hat geantwortet, mit jedem Status (auch 4xx/5xx, bevor daraus eine Exception wird).
 *
 * Der Request enthält den Header `X-BillomatApiKey` (und ggf. `X-AppSecret`) – nicht ungefiltert loggen.
 */
final readonly class ResponseReceived
{
    public function __construct(
        public RequestInterface $request,
        public ResponseInterface $response,
        /** Nummer des Versuchs, ab 1. */
        public int $attempt,
        /** Dauer des Versuchs in Millisekunden. */
        public float $durationMs,
    ) {
    }
}
