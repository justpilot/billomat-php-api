<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Exception;

use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * HTTP-bezogener Fehler bei einem Request an die Billomat-API.
 */
class HttpException extends BillomatException
{
    public function __construct(
        string                   $message,
        private readonly int     $statusCode,
        private readonly ?string $responseBody = null,
        ?\Throwable              $previous = null,
    )
    {
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
     * Convenience-Helfer um aus einer Symfony-Response eine HttpException zu bauen.
     */
    public static function fromResponse(ResponseInterface $response, string $fallbackMessage = 'HTTP error from Billomat'): self
    {
        $statusCode = $response->getStatusCode();

        $body = null;
        try {
            $body = $response->getContent(false); // Inhalt auch bei 4xx/5xx holen
        } catch (\Throwable) {
            // Body konnte nicht gelesen werden, ignorieren
        }

        $message = $fallbackMessage . sprintf(' (status %d)', $statusCode);

        return new self($message, $statusCode, $body);
    }
}