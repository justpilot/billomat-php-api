<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Exception;

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
}
