<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\HttpException as BillomatHttpException;
use Justpilot\Billomat\Exception\NotFoundException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Http\BillomatHttpClientInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

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
        protected BillomatHttpClientInterface $http,
    )
    {
    }

    /**
     * Führt einen GET-Request aus und gibt den JSON-Body als Array zurück.
     *
     * @param array<string, scalar|array|null> $query
     * @return array<string,mixed>
     */
    protected function getJson(string $path, array $query = []): array
    {
        $response = $this->http->request('GET', $path, $query);

        $decoded = $this->decodeJsonResponse($response);

        return $decoded;
    }

    /**
     * Führt einen GET-Request aus und gibt bei 404 null zurück.
     *
     * @return array<string,mixed>|null
     */
    protected function getJsonOrNull(string $path): ?array
    {
        try {
            $response = $this->http->request('GET', $path);
            $decoded = $this->decodeJsonResponse($response);

            return $decoded;
        } catch (NotFoundException) {
            // gewolltes Verhalten: Ressource existiert nicht
            return null;
        }
    }

    /**
     * Führt einen POST-Request mit JSON-Body aus und gibt den JSON-Body als Array zurück.
     *
     * @param array<string,mixed> $body
     * @return array<string,mixed>
     */
    protected function postJson(string $path, array $body): array
    {
        $response = $this->http->request('POST', $path, [], $body);

        $decoded = $this->decodeJsonResponse($response);

        return $decoded;
    }

    /**
     * Führt einen PUT-Request mit JSON-Body aus und gibt den JSON-Body als Array zurück.
     *
     * Wird z. B. für PUT /invoices/{id}/complete verwendet.
     *
     * @param array<string,mixed> $body
     */
    protected function putJson(string $path, array $body): array
    {
        $response = $this->http->request('PUT', $path, [], $body);

        $decoded = $this->decodeJsonResponse($response);

        return $decoded;
    }

    protected function putEmptyResponse(string $path, array $body): ResponseInterface
    {
        return $this->http->request('PUT', $path, [], $body);
    }

    /**
     * Liest eine JSON-Response und mapped HTTP-Fehler auf SDK-Exceptions.
     *
     * @return array<string,mixed>
     */
    private function decodeJsonResponse(ResponseInterface $response): array
    {
        try {
            $content = $response->getContent(); // wir erwarten 2xx
        } catch (HttpExceptionInterface $e) {
            throw $this->mapHttpException($e);
        }

        /** @var array<string,mixed> $decoded */
        $decoded = json_decode($content, true, flags: JSON_THROW_ON_ERROR);

        return $decoded;
    }

    /**
     * Mappt eine Symfony-HttpException auf unsere eigenen Exception-Typen.
     */
    private function mapHttpException(HttpExceptionInterface $e): BillomatHttpException
    {
        $symfonyResponse = $e->getResponse();
        $statusCode = $symfonyResponse->getStatusCode();

        // Roh-Body, falls wir später Details extrahieren wollen
        $rawBody = null;
        try {
            $rawBody = $symfonyResponse->getContent(false);
        } catch (\Throwable) {
            // ignorieren
        }

        $message = $e->getMessage() !== ''
            ? $e->getMessage()
            : sprintf('HTTP error from Billomat (status %d)', $statusCode);

        // Grobes Mapping nach Status-Code
        return match (true) {
            $statusCode === Response::HTTP_UNAUTHORIZED || $statusCode === Response::HTTP_FORBIDDEN =>
            new AuthenticationException($message, $statusCode, $rawBody, $e),

            $statusCode === Response::HTTP_NOT_FOUND =>
            new NotFoundException($message, $statusCode, $rawBody, $e),

            $statusCode === Response::HTTP_BAD_REQUEST || $statusCode === Response::HTTP_UNPROCESSABLE_ENTITY =>
            new ValidationException($message, $statusCode, $rawBody, $e),

            default =>
            new BillomatHttpException($message, $statusCode, $rawBody, $e),
        };
    }
}