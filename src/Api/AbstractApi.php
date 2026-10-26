<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Closure;
use Generator;
use JsonException;
use Justpilot\Billomat\Exception\NotFoundException;
use Justpilot\Billomat\Exception\UnexpectedResponseException;
use Justpilot\Billomat\Http\BillomatHttpClientInterface;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\PdfType;
use Justpilot\Billomat\Pagination\Page;
use Justpilot\Billomat\Pagination\PageInfo;
use Psr\Http\Message\ResponseInterface;

use const JSON_THROW_ON_ERROR;

/**
 * Basis-Klasse für alle Billomat API-Wrapper.
 *
 * Stellt Hilfsmethoden für typische JSON-Requests bereit:
 *  - GET mit JSON-Response
 *  - GET, der bei 404 null zurückgibt
 *  - POST mit JSON-Body und JSON-Response
 *
 * @internal Basis der SDK-eigenen APIs, kein Erweiterungspunkt; die Methoden fallen nicht unter die
 *           BC-Zusage. Endpunkte ohne SDK-Methode gehen über {@see \Justpilot\Billomat\BillomatClient::getHttpClient()}.
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
     * @param array<string, scalar|array<scalar|null>|null> $query
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
     * Holt einen Einzeldatensatz: null bei 404, sonst das hydrierte Model.
     *
     * Eine 2xx-Antwort ohne die erwartete Hülle ist kein „nicht gefunden“, sondern eine
     * {@see UnexpectedResponseException}.
     *
     * @template T of object
     *
     * @param string                           $key     erwarteter Wrapper-Key, z. B. "client"
     * @param Closure(array<string, mixed>): T $hydrate
     * @param string                           $context beschreibt den Aufrufkontext für die Exception-Message
     *
     * @return T|null
     *
     * @throws UnexpectedResponseException
     */
    protected function getResource(string $path, string $key, Closure $hydrate, string $context): ?object
    {
        $data = $this->getJsonOrNull($path);

        return null === $data ? null : $hydrate($this->unwrapEnvelope($data, $key, $context));
    }

    /**
     * Exception für {@see getOrFail()}, wenn {@see getJsonOrNull()} null geliefert hat.
     */
    protected function notFound(string $path): NotFoundException
    {
        return new NotFoundException(\sprintf('Billomat responded with HTTP 404 to GET /%s.', ltrim($path, '/')), 404);
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
     *
     * @return array<string,mixed>
     */
    protected function putJson(string $path, array $body): array
    {
        $response = $this->http->request('PUT', $path, [], $body);

        return $this->decodeJsonResponse($response);
    }

    /**
     * Führt einen PUT-Request aus, dessen Response-Body uns nicht interessiert
     * (z. B. PUT /invoices/{id}/complete, /cancel, /uncancel, …).
     *
     * HTTP-Fehler wirft bereits {@see BillomatHttpClientInterface::request()}.
     *
     * @param array<string,mixed> $body
     */
    protected function putVoid(string $path, array $body = []): void
    {
        $this->http->request('PUT', $path, [], $body);
    }

    protected function deleteVoid(string $path): void
    {
        $this->http->request('DELETE', $path);
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
     * @param string                                                   $path     Endpunkt, z. B. "/clients"
     * @param string                                                   $outerKey äußerer Wrapper-Key, z. B. "clients"
     * @param string                                                   $innerKey innerer Wrapper-Key, z. B. "client"
     * @param Closure(array<string,mixed>): T                          $hydrate  Hydrator, z. B. Client::fromArray(...)
     * @param ListFilter|array<string, scalar|array<scalar|null>|null> $filters  optionale Query-Filter
     *
     * @return list<T>
     */
    protected function listResource(string $path, string $outerKey, string $innerKey, Closure $hydrate, ListFilter|array $filters = []): array
    {
        $data = $this->getJson($path, self::filterQuery($filters));

        return array_map($hydrate, self::envelopeRows($data[$outerKey] ?? null, $innerKey));
    }

    /**
     * Wie {@see listResource()}, gibt aber die einzelne Seite samt Pagination-
     * Metadaten zurück. Die Werte `@page`/`@per_page`/`@total` aus dem
     * äußeren Envelope werden in {@see PageInfo} normalisiert (von String zu
     * Int). Liefert der Endpunkt keine dieser Felder, werden sie aus
     * `$filters['page']`/`$filters['per_page']` bzw. der tatsächlichen
     * Item-Anzahl abgeleitet (-> einzelne Seite, `hasNextPage() === false`).
     *
     * @template T of object
     *
     * @param string                                                   $path     Endpunkt, z. B. "/clients"
     * @param string                                                   $outerKey äußerer Wrapper-Key, z. B. "clients"
     * @param string                                                   $innerKey innerer Wrapper-Key, z. B. "client"
     * @param Closure(array<string,mixed>): T                          $hydrate  Hydrator, z. B. Client::fromArray(...)
     * @param ListFilter|array<string, scalar|array<scalar|null>|null> $filters  optionale Query-Filter
     *
     * @return Page<T>
     */
    protected function listResourcePage(string $path, string $outerKey, string $innerKey, Closure $hydrate, ListFilter|array $filters = []): Page
    {
        $filters = self::filterQuery($filters);
        $data = $this->getJson($path, $filters);

        $envelope = $data[$outerKey] ?? null;
        if (!\is_array($envelope)) {
            $envelope = [];
        }

        $items = array_map($hydrate, self::envelopeRows($envelope, $innerKey));

        $info = new PageInfo(
            page: ScalarCaster::toIntOrNull($envelope['@page'] ?? $filters['page'] ?? null) ?? 1,
            perPage: ScalarCaster::toIntOrNull($envelope['@per_page'] ?? $filters['per_page'] ?? null) ?? \count($items),
            total: ScalarCaster::toIntOrNull($envelope['@total'] ?? null),
        );

        return new Page($items, $info);
    }

    /**
     * Erzeugt einen Lazy-Generator, der seitenweise durch eine Billomat-Liste
     * iteriert. Jede Iteration des äußeren Loops materialisiert eine Seite und
     * yieldet ihre Items; sobald {@see PageInfo::hasNextPage()} `false` ist,
     * stoppt der Generator.
     *
     * Sicherheitsnetz gegen Endlos-Loops: liefert eine Seite weniger Items als
     * `per_page` erwartet (typischer Indikator für die letzte Seite, auch wenn
     * `@total` fehlt), wird ebenfalls abgebrochen.
     *
     * @template T of object
     *
     * @param string                                                   $path     Endpunkt, z. B. "/clients"
     * @param string                                                   $outerKey äußerer Wrapper-Key, z. B. "clients"
     * @param string                                                   $innerKey innerer Wrapper-Key, z. B. "client"
     * @param Closure(array<string,mixed>): T                          $hydrate  Hydrator, z. B. Client::fromArray(...)
     * @param ListFilter|array<string, scalar|array<scalar|null>|null> $filters  optionale Query-Filter; `page`/`per_page` werden überschrieben
     * @param int                                                      $pageSize Items pro Anfrage; 1-1000 (Billomat-Limit)
     *
     * @return Generator<int, T>
     */
    protected function iterateResource(string $path, string $outerKey, string $innerKey, Closure $hydrate, ListFilter|array $filters = [], int $pageSize = 100): Generator
    {
        $filters = self::filterQuery($filters);
        $pageSize = max(1, $pageSize);
        $page = 1;

        while (true) {
            $pageData = $this->listResourcePage(
                $path,
                $outerKey,
                $innerKey,
                $hydrate,
                [...$filters, 'page' => $page, 'per_page' => $pageSize],
            );

            foreach ($pageData->items as $item) {
                yield $item;
            }

            if (!$pageData->info->hasNextPage() || \count($pageData->items) < $pageSize) {
                return;
            }

            ++$page;
        }
    }

    /**
     * Normalisiert den inneren Knoten eines Listen-Envelopes auf eine Liste von Datensätzen.
     *
     * Ein einzelner Treffer kommt als Objekt statt als Liste, „keine Treffer“ als leerer String
     * oder leeres Array; Einträge, die keine Datensätze sind, fallen heraus.
     *
     * @return list<array<string,mixed>>
     */
    protected static function envelopeRows(mixed $envelope, string $innerKey): array
    {
        if (!\is_array($envelope)) {
            return [];
        }

        $node = $envelope[$innerKey] ?? null;
        if (!\is_array($node) || [] === $node) {
            return [];
        }

        $rows = [];
        foreach (array_is_list($node) ? $node : [$node] as $row) {
            if (self::isRecord($row)) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * Prüft, dass ein dekodierter JSON-Wert ein Objekt (assoziatives Array mit String-Schlüsseln) ist.
     *
     * @phpstan-assert-if-true array<string,mixed> $value
     */
    protected static function isRecord(mixed $value): bool
    {
        return \is_array($value) && array_all($value, static fn (mixed $_, int|string $key): bool => \is_string($key));
    }

    /**
     * Liest aus einer Billomat-Envelope-Response den eingewickelten Datensatz.
     *
     * Billomat antwortet bei {@code get()}/{@code create()}/{@code update()} mit
     * {@code {"client": { ... }}} — diese Methode liefert das innere Array oder
     * wirft eine {@see UnexpectedResponseException} mit konsistenter Fehlermeldung, wenn
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

        if (!self::isRecord($node)) {
            throw new UnexpectedResponseException(\sprintf('Unexpected response from Billomat when %s.', $context));
        }

        return $node;
    }

    /**
     * Dekodiert den JSON-Body einer erfolgreichen Response.
     *
     * @return array<string,mixed>
     *
     * @throws UnexpectedResponseException wenn der Body kein JSON-Objekt ist
     */
    private function decodeJsonResponse(ResponseInterface $response): array
    {
        try {
            $decoded = json_decode((string) $response->getBody(), true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new UnexpectedResponseException('Unexpected response from Billomat: the body is not valid JSON.', 0, $e);
        }

        if (!self::isRecord($decoded)) {
            throw new UnexpectedResponseException('Unexpected response from Billomat: expected a JSON object.');
        }

        return $decoded;
    }

    /**
     * Führt einen GET-Request aus und gibt den Response-Body roh zurück (z. B. für Binary wie /thumb).
     *
     * @param array<string, scalar|array<scalar|null>|null> $query
     */
    protected function getRaw(string $path, array $query = []): string
    {
        return (string) $this->http->request('GET', $path, $query)->getBody();
    }

    /**
     * Wandelt einen typisierten Filter in Query-Parameter um; Arrays bleiben unverändert.
     *
     * @param ListFilter|array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return array<string, scalar|array<scalar|null>|null>
     */
    protected static function filterQuery(ListFilter|array $filters): array
    {
        return $filters instanceof ListFilter ? $filters->toQuery() : $filters;
    }

    /**
     * Query für GET /{dokumente}/{id}/pdf: `type=signed|print`, falls gesetzt.
     *
     * @return array<string, string>
     */
    protected static function pdfQuery(?PdfType $type): array
    {
        return $type instanceof PdfType ? ['type' => $type->value] : [];
    }

    /**
     * Wie {@see getRaw()}, prüft aber, dass der Body ein PDF ist.
     *
     * Billomat antwortet gelegentlich mit 200 und leerem Body oder einer HTML-/JSON-Seite;
     * ohne Prüfung landet das unbemerkt als „PDF“ beim Kunden.
     *
     * @param array<string, scalar|array<scalar|null>|null> $query
     *
     * @throws UnexpectedResponseException bei leerem Body oder fehlendem `%PDF-`-Header
     */
    protected function getPdf(string $path, array $query = []): string
    {
        $body = $this->getRaw($path, $query);

        if (!str_starts_with($body, '%PDF-')) {
            throw new UnexpectedResponseException(\sprintf('Unexpected response from Billomat for GET /%s: %s.', ltrim($path, '/'), '' === $body ? 'empty body instead of a PDF' : 'the body is not a PDF'));
        }

        return $body;
    }
}
