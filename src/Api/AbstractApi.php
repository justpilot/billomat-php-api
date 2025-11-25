<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Http\BillomatHttpClient;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

abstract class AbstractApi
{
    public function __construct(
        protected BillomatHttpClient $http,
    )
    {
    }

    /**
     * Führt einen GET-Request aus und gibt den JSON-Body als Array zurück.
     *
     * @param array<string, scalar|array|null> $query
     * @return array<string, mixed>
     */
    protected function getJson(string $path, array $query = []): array
    {
        $response = $this->http->request('GET', $path, $query);

        $content = $response->getContent(); // 2xx erwartet

        /** @var array<string, mixed> $decoded */
        $decoded = json_decode($content, true, flags: JSON_THROW_ON_ERROR);

        return $decoded;
    }

    /**
     * Wie getJson(), aber gibt bei 404 null zurück.
     *
     * @return array<string, mixed>|null
     */
    protected function getJsonOrNull(string $path): ?array
    {
        try {
            $response = $this->http->request('GET', $path);
            $content = $response->getContent(); // 2xx erwartet

            /** @var array<string, mixed> $decoded */
            $decoded = json_decode($content, true, flags: JSON_THROW_ON_ERROR);

            return $decoded;
        } catch (HttpExceptionInterface $e) {
            if ($e->getCode() === 404) {
                return null;
            }

            throw $e;
        }
    }

    /**
     * Führt einen POST-Request aus und gibt den JSON-Body als Array zurück.
     *
     * @param array<string,mixed> $body
     * @return array<string,mixed>
     */
    protected function postJson(string $path, array $body): array
    {
        // Wichtig: dritter Parameter ist query (leer), vierter ist json-body
        $response = $this->http->request('POST', $path, [], $body);

        $content = $response->getContent(); // 2xx erwartet

        /** @var array<string,mixed> $decoded */
        $decoded = json_decode($content, true, flags: JSON_THROW_ON_ERROR);

        return $decoded;
    }
}