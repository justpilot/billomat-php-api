<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Http\BillomatHttpClient;

abstract class AbstractApi
{
    public function __construct(
        protected BillomatHttpClient $http,
    )
    {
    }

    /**
     * @param array<string, scalar|array|null> $query
     * @return array<string, mixed>
     */
    protected function get(string $path, array $query = []): array
    {
        $response = $this->http->request('GET', $path, $query);

        $content = $response->getContent(); // wir erwarten 2xx

        /** @var array<string, mixed> $decoded */
        $decoded = json_decode($content, true, flags: JSON_THROW_ON_ERROR);

        return $decoded;
    }
}