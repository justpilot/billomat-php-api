<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Closure;
use Deprecated;
use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\HttpException as BillomatHttpException;
use Justpilot\Billomat\Exception\NotFoundException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Http\BillomatHttpClientInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Throwable;

use const JSON_THROW_ON_ERROR;

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
        protected readonly BillomatHttpClientInterface $http,
    ) {
    }

    /**
     * Führt einen GET-Request aus und gibt den JSON-Body als Array zurück.
     *
     * @param array<string, scalar|array|null> $query
     *
     * @return array<string,mixed>
     */
    protected function getJson(string $path, array $query = []): array
    {
        $response = $this->http->request('GET', $path, $query);

        return $this->decodeJsonResponse($response);
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

            return $this->decodeJsonResponse($response);
        } catch (NotFoundException) {
            // gewolltes Verhalten: Ressource existiert nicht
            return null;
        }
    }

    /**
     * Führt einen POST-Request mit JSON-Body aus und gibt den JSON-Body als Array zurück.
     *
     * @param array<string,mixed> $body
     *
     * @return array<string,mixed>
     */
    protected function postJson(string $path, array $body): array
    {
        $response = $this->http->request('POST', $path, [], $body);

        return $this->decodeJsonResponse($response);
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

        return $this->decodeJsonResponse($response);
    }

    /**
     * Führt einen PUT-Request aus und löst HTTP-Fehler (4xx/5xx) in SDK-Exceptions auf.
     *
     * Wird für Endpunkte verwendet, deren Body uns nicht interessiert
     * (z. B. PUT /invoices/{id}/complete, /cancel, /uncancel, …). Wichtig:
     * {@see ResponseInterface::getStatusCode()}
     * wirft NICHT bei 4xx/5xx – nur ein Aufruf wie hier (über getContent()
     * im {@see handleErrors()}-Pfad) materialisiert den HTTP-Fehler und mapped
     * ihn auf die passende {@see \Justpilot\Billomat\Exception\BillomatException}.
     *
     * @param array<string,mixed> $body
     */
    protected function putVoid(string $path, array $body = []): void
    {
        $response = $this->http->request('PUT', $path, [], $body);
        $this->handleErrors($response);
    }

    /**
     * @param array<string,mixed> $body
     */
    #[Deprecated(message: <<<'TXT'
        since 2.1 use {@see putVoid()} stattdessen. `putEmptyResponse()`
                     materialisiert die Response NICHT – HTTP-Fehler 4xx/5xx wurden
                     daher stillschweigend verschluckt, weil
                     {@see ResponseInterface::getStatusCode()}
                     nur bei Transport-Fehlern wirft. Wird in 3.0 entfernt.
        TXT)]
    protected function putEmptyResponse(string $path, array $body = []): ResponseInterface
    {
        return $this->http->request('PUT', $path, [], $body);
    }

    protected function deleteVoid(string $path): void
    {
        $response = $this->http->request('DELETE', $path);
        $this->handleErrors($response);
    }

    /**
     * Hydriert eine Liste aus einer Billomat-Envelope-Response.
     *
     * Billomat liefert Listen in zwei Varianten desselben Wrappers:
     *  - mehrere Treffer:  {"clients": {"client": [ {...}, {...} ] }}
     *  - genau ein Treffer: {"clients": {"client": {...} }}
     *  - keine Treffer:     {"clients": ""} oder {"clients": {"client": []}}
     *
     * Diese Methode kapselt den Envelope-Lookup, die "Single-Object-zu-Liste"-
     * Normalisierung (via {@see array_is_list()}) und das anschließende
     * {@see array_map()} über den übergebenen Hydrator.
     *
     * @template T of object
     *
     * @param string                           $path     Endpunkt, z. B. "/clients"
     * @param string                           $outerKey äußerer Wrapper-Key, z. B. "clients"
     * @param string                           $innerKey innerer Wrapper-Key, z. B. "client"
     * @param Closure(array<string,mixed>): T  $hydrate  Hydrator, z. B. Client::fromArray(...)
     * @param array<string, scalar|array|null> $filters  optionale Query-Filter
     *
     * @return list<T>
     */
    protected function listResource(string $path, string $outerKey, string $innerKey, Closure $hydrate, array $filters = []): array
    {
        $data = $this->getJson($path, $filters);

        $node = $data[$outerKey][$innerKey] ?? null;

        if (null === $node || [] === $node || '' === $node) {
            return [];
        }

        if (!\is_array($node)) {
            return [];
        }

        /** @var list<array<string,mixed>> $rows */
        $rows = array_is_list($node) ? $node : [$node];

        return array_map($hydrate, $rows);
    }

    /**
     * Liest aus einer Billomat-Envelope-Response den eingewickelten Datensatz.
     *
     * Billomat antwortet bei {@code get()}/{@code create()}/{@code update()} mit
     * {@code {"client": { ... }}} — diese Methode liefert das innere Array oder
     * wirft eine {@see RuntimeException} mit konsistenter Fehlermeldung, wenn
     * die Hülle nicht so aussieht, wie sie sollte.
     *
     * @param array<string,mixed> $payload bereits dekodierter JSON-Body
     * @param string              $key     erwarteter Wrapper-Key, z. B. "client"
     * @param string              $context beschreibt den Aufrufkontext für die Exception-Message
     *
     * @return array<string,mixed>
     */
    protected function unwrapEnvelope(array $payload, string $key, string $context): array
    {
        $node = $payload[$key] ?? null;

        if (!\is_array($node)) {
            throw new RuntimeException(\sprintf('Unexpected response from Billomat when %s.', $context));
        }

        /** @var array<string,mixed> $node */
        return $node;
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
    protected function mapHttpException(HttpExceptionInterface $e): BillomatHttpException
    {
        $symfonyResponse = $e->getResponse();
        $statusCode = $symfonyResponse->getStatusCode();

        // Roh-Body, falls wir später Details extrahieren wollen
        $rawBody = null;
        try {
            $rawBody = $symfonyResponse->getContent(false);
        } catch (Throwable) {
            // ignorieren
        }

        $message = '' !== $e->getMessage()
            ? $e->getMessage()
            : \sprintf('HTTP error from Billomat (status %d)', $statusCode);

        // Grobes Mapping nach Status-Code
        return match (true) {
            Response::HTTP_UNAUTHORIZED === $statusCode || Response::HTTP_FORBIDDEN === $statusCode => new AuthenticationException($message, $statusCode, $rawBody, $e),

            Response::HTTP_NOT_FOUND === $statusCode => new NotFoundException($message, $statusCode, $rawBody, $e),

            Response::HTTP_BAD_REQUEST === $statusCode || Response::HTTP_UNPROCESSABLE_ENTITY === $statusCode => new ValidationException($message, $statusCode, $rawBody, $e),

            default => new BillomatHttpException($message, $statusCode, $rawBody, $e),
        };
    }

    /**
     * Führt nur die Fehlerbehandlung aus, ohne JSON zu dekodieren.
     *
     * Wird z.B. von deleteVoid() verwendet, wenn wir keinen JSON-Body erwarten.
     */
    private function handleErrors(ResponseInterface $response): void
    {
        try {
            $response->getContent();
        } catch (HttpExceptionInterface $e) {
            throw $this->mapHttpException($e);
        }
    }

    /**
     * Führt einen GET-Request aus und gibt den Response-Body roh zurück (z. B. für Binary wie /thumb).
     *
     * @param array<string, scalar|array|null> $query
     */
    protected function getRaw(string $path, array $query = []): string
    {
        $response = $this->http->request('GET', $path, $query);

        try {
            return $response->getContent(); // 2xx erwartet
        } catch (HttpExceptionInterface $e) {
            throw $this->mapHttpException($e);
        }
    }
}
