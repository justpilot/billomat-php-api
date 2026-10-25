<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Exception;

use Throwable;

/**
 * Billomat hat mit 429 geantwortet: das Kontingent des aktuellen 15-Minuten-Fensters ist verbraucht.
 *
 * Das SDK wartet selbst nur, wenn die Wartezeit innerhalb von
 * {@see \Justpilot\Billomat\Config\RetryPolicy::$maxDelayMs} liegt.
 * Sonst landet diese Exception beim Aufrufer, der mit {@see getRetryAfter()} planen kann.
 */
class RateLimitException extends HttpException
{
    public function __construct(
        string $message,
        int $statusCode = 429,
        ?string $responseBody = null,
        ?Throwable $previous = null,
        private readonly ?int $retryAfter = null,
    ) {
        parent::__construct($message, $statusCode, $responseBody, $previous);
    }

    /**
     * Sekunden bis zum nächsten Fenster, falls Billomat sie mitgeteilt hat.
     */
    public function getRetryAfter(): ?int
    {
        return $this->retryAfter;
    }
}
