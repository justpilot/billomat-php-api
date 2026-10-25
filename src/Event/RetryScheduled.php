<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Event;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Ein Versuch ist fehlgeschlagen und wird nach `$delayMs` wiederholt (siehe {@see \Justpilot\Billomat\Config\RetryPolicy}).
 *
 * Grund ist entweder eine Response (429, 502, 503, 504) oder ein Netzwerkfehler.
 */
final readonly class RetryScheduled
{
    public function __construct(
        public RequestInterface $request,
        /** Nummer des fehlgeschlagenen Versuchs, ab 1. */
        public int $attempt,
        /** Wartezeit bis zum nächsten Versuch in Millisekunden. */
        public int $delayMs,
        public ?ResponseInterface $response = null,
        public ?ClientExceptionInterface $error = null,
    ) {
    }
}
