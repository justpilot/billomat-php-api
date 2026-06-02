<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Model\Incoming;
use RuntimeException;

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
        $data = $this->getJson('/incomings', $filters);

        $node = $data['incomings']['incoming'] ?? [];

        if (null === $node || [] === $node) {
            return [];
        }

        if (\is_array($node) && array_is_list($node)) {
            $rows = $node;
        } elseif (\is_array($node)) {
            $rows = [$node];
        } else {
            $rows = [];
        }

        /** @var list<Incoming> $models */
        $models = array_map(
            Incoming::fromArray(...),
            $rows,
        );

        return $models;
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

        $created = $data['incoming'] ?? null;

        if (!\is_array($created)) {
            throw new RuntimeException('Unexpected response from Billomat when creating incoming.');
        }

        return Incoming::fromArray($created);
    }

    public function update(int $id, IncomingUpdateOptions $options): Incoming
    {
        $payload = ['incoming' => $options->toArray()];

        $data = $this->putJson("/incomings/{$id}", $payload);

        $row = $data['incoming'] ?? null;
        if (!\is_array($row)) {
            throw new RuntimeException('Unexpected response from Billomat when updating incoming.');
        }

        return Incoming::fromArray($row);
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/incomings/{$id}");

        return true;
    }

    public function cancel(int $id): bool
    {
        $response = $this->putEmptyResponse("/incomings/{$id}/cancel");

        return 200 === $response->getStatusCode();
    }

    public function uncancel(int $id): bool
    {
        $response = $this->putEmptyResponse("/incomings/{$id}/uncancel");

        return 200 === $response->getStatusCode();
    }

    /**
     * Lädt ein PDF zur Eingangsrechnung hoch.
     */
    public function upload(int $id, string $base64Pdf): bool
    {
        $payload = ['upload' => ['base64file' => $base64Pdf]];

        $response = $this->putEmptyResponse("/incomings/{$id}/upload", $payload);

        return 200 === $response->getStatusCode();
    }
}
