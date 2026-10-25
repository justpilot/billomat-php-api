<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Exception;

use Override;
use Throwable;

/**
 * HTTP-bezogener Fehler bei einem Request an die Billomat-API.
 */
class HttpException extends BillomatException
{
    public function __construct(
        string $message,
        private readonly int $statusCode,
        private readonly ?string $responseBody = null,
        ?Throwable $previous = null,
        private readonly ?int $retryAfter = null,
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getResponseBody(): ?string
    {
        return $this->responseBody;
    }

    /**
     * Sekunden bis zum nächsten Versuch, falls Billomat sie mitgeteilt hat.
     *
     * Gesetzt bei 429 (`Retry-After` bzw. `X-Rate-Limit-Reset`) und bei 502/503/504 (`Retry-After`).
     */
    public function getRetryAfter(): ?int
    {
        return $this->retryAfter;
    }

    /**
     * `true` bei 429, 502, 503 und 504.
     */
    #[Override]
    public function isTransient(): bool
    {
        return \in_array($this->statusCode, [429, 502, 503, 504], true);
    }

    /**
     * `false` bei 4xx (inkl. 429), `true` bei 5xx.
     */
    #[Override]
    public function mayHaveBeenProcessed(): bool
    {
        return $this->statusCode >= 500;
    }
}
