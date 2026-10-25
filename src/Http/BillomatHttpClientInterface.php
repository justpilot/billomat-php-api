<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Http;

use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\TransportException;
use Psr\Http\Message\ResponseInterface;

interface BillomatHttpClientInterface
{
    /**
     * Sendet einen Request an die Billomat-API.
     *
     * Die zurückgegebene Response hat immer einen Status unter 400; Fehler kommen als Exception.
     *
     * @param array<string, scalar|array|null> $query
     * @param array<string,mixed>|null         $json
     *
     * @throws HttpException      bei 4xx/5xx (bzw. einer Unterklasse je nach Status)
     * @throws TransportException wenn keine Antwort kam
     */
    public function request(
        string $method,
        string $path,
        array $query = [],
        ?array $json = null
    ): ResponseInterface;
}
