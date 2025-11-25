<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Http\BillomatHttpClient;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

/**
 * Basis-Klasse für alle Billomat API-Wrapper.
 *
 * Stellt Hilfsmethoden für typische JSON-Requests bereit:
 *  - GET mit JSON-Response
 *  - GET, der bei 404 null zurückgibt
 *  - POST mit JSON-Body und JSON-Response
 */
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
     * Wird für Endpunkte wie GET /clients oder GET /invoices verwendet.
     *
     * @param array<string, scalar|array|null> $query
     * @return array<string,mixed>
     */
    protected function getJson(string $path, array $query = []): array
    {
        $response = $this->http->request('GET', $path, $query);

        $content = $response->getContent(); // wir erwarten 2xx

        /** @var array<string,mixed> $decoded */
        $decoded = json_decode($content, true, flags: JSON_THROW_ON_ERROR);

        return $decoded;
    }

    /**
     * Führt einen GET-Request aus und gibt bei 404 null zurück.
     *
     * Praktisch für GET /resource/{id}, wenn Nicht-Vorhandensein kein Fehler ist.
     *
     * @return array<string,mixed>|null
     */
    protected function getJsonOrNull(string $path): ?array
    {
        try {
            $response = $this->http->request('GET', $path);
            $content = $response->getContent(); // 2xx erwartet

            /** @var array<string,mixed> $decoded */
            $decoded = json_decode($content, true, flags: JSON_THROW_ON_ERROR);

            return $decoded;
        } catch (HttpExceptionInterface $e) {
            // 404 => Ressource existiert nicht
            if ($e->getCode() === 404) {
                return null;
            }

            // andere HTTP-Fehler durchreichen
            throw $e;
        }
    }

    /**
     * Führt einen POST-Request mit JSON-Body aus und gibt den JSON-Body als Array zurück.
     *
     * Wird z. B. für POST /clients verwendet.
     *
     * @param array<string,mixed> $body
     * @return array<string,mixed>
     */
    protected function postJson(string $path, array $body): array
    {
        // Signatur von BillomatHttpClient::request:
        // request(string $method, string $path, array $query = [], ?array $json = null)
        $response = $this->http->request('POST', $path, [], $body);

        $content = $response->getContent(); // wir erwarten 2xx/201

        /** @var array<string,mixed> $decoded */
        $decoded = json_decode($content, true, flags: JSON_THROW_ON_ERROR);

        return $decoded;
    }
}