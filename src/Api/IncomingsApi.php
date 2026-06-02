<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Model\Incoming;

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
