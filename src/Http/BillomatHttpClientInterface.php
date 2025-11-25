<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Http;

use Symfony\Contracts\HttpClient\ResponseInterface;

interface BillomatHttpClientInterface
{
    /**
     * @param array<string, scalar|array|null> $query
     * @param array<string,mixed>|null $json
     */
    public function request(
        string $method,
        string $path,
        array  $query = [],
        ?array $json = null
    ): ResponseInterface;
}