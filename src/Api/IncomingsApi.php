<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Model\Incoming;
use Justpilot\Billomat\Pagination\Page;

/**
 * API-Wrapper für die Billomat-Incomings-Ressource (Eingangsrechnungen).
 *
 * Doku: https://www.billomat.com/en/api/incomings/
 *
 * Endpoints:
 *  - GET    /incomings
 *  - GET    /incomings/{id}
 *  - POST   /incomings
 *  - PUT    /incomings/{id}
 *  - DELETE /incomings/{id}
 *  - PUT    /incomings/{id}/cancel
 *  - PUT    /incomings/{id}/uncancel
 *  - PUT    /incomings/{id}/upload  (PDF des Belegs hochladen)
 */
final class IncomingsApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array|null> $filters
     *
     * @return list<Incoming>
     */
    public function list(array $filters = []): array
    {
        return $this->listResource('/incomings', 'incomings', 'incoming', Incoming::fromArray(...), $filters);
    }

    /**
     * Liefert eine einzelne Seite samt Pagination-Metadaten.
     *
     * Identisch zu {@see list()}, gibt aber zusätzlich `@page`/`@per_page`/
     * `@total` aus dem Response-Envelope als {@see PageInfo} zurück. Nützlich
     * für UI mit klassischer "Seite 1/12, 234 Treffer"-Anzeige.
     *
     * @param array<string, scalar|array|null> $filters
     *
     * @return Page<Incoming>
     */
    public function listPage(array $filters = []): Page
    {
        return $this->listResourcePage('/incomings', 'incomings', 'incoming', Incoming::fromArray(...), $filters);
    }

    /**
     * Iteriert lazy durch alle Eingangsrechnungen und yieldet sie einzeln.
     *
     * Holt seitenweise pro {@code $pageSize}-Items und stoppt automatisch,
     * sobald die letzte Seite erreicht ist (analog `auto_paging_iter()` im
     * Stripe-SDK). Filter werden bei jeder Page-Anfrage mitgesendet; `page`
     * und `per_page` darin werden überschrieben.
     *
     * @param array<string, scalar|array|null> $filters
     *
     * @return Generator<int, Incoming>
     */
    public function iterateAll(array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/incomings', 'incomings', 'incoming', Incoming::fromArray(...), $filters, $pageSize);
    }

    public function get(int $id): ?Incoming
    {
        $data = $this->getJsonOrNull("/incomings/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['incoming'] ?? null;

        if (!\is_array($row)) {
            return null;
        }

        return Incoming::fromArray($row);
    }

    /**
     * @throws ValidationException
     * @throws AuthenticationException
     * @throws HttpException
     */
    public function create(IncomingCreateOptions $options): Incoming
    {
        $payload = ['incoming' => $options->toArray()];

        $data = $this->postJson('/incomings', $payload);

        return Incoming::fromArray($this->unwrapEnvelope($data, 'incoming', 'creating incoming'));
    }

    public function update(int $id, IncomingUpdateOptions $options): Incoming
    {
        $payload = ['incoming' => $options->toArray()];

        $data = $this->putJson("/incomings/{$id}", $payload);

        return Incoming::fromArray($this->unwrapEnvelope($data, 'incoming', 'updating incoming'));
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/incomings/{$id}");

        return true;
    }

    public function cancel(int $id): bool
    {
        $this->putVoid("/incomings/{$id}/cancel");

        return true;
    }

    public function uncancel(int $id): bool
    {
        $this->putVoid("/incomings/{$id}/uncancel");

        return true;
    }

    /**
     * Lädt ein PDF zur Eingangsrechnung hoch.
     */
    public function upload(int $id, string $base64Pdf): bool
    {
        $payload = ['upload' => ['base64file' => $base64Pdf]];

        $this->putVoid("/incomings/{$id}/upload", $payload);

        return true;
    }
}
