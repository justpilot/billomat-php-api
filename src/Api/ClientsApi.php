<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Model\Client;

/**
 * API-Wrapper für die Billomat-Clients-Ressource.
 *
 * Kapselt Zugriffe auf:
 *  - GET  /clients
 *  - GET  /clients/{id}
 *  - POST /clients
 */
final class ClientsApi extends AbstractApi
{
    public function getMyself(): Client
    {
        $data = $this->getJson('/clients/myself');

        $clientData = $data['client'] ?? null;

        if (!is_array($clientData)) {
            throw new \RuntimeException('Unexpected response from Billomat when fetching own account via /clients/myself.');
        }

        return Client::fromArray($clientData);
    }

    /**
     * Listet Clients mit optionalen Filtern.
     *
     * Entspricht GET /clients
     *
     * @param array<string, scalar|array|null> $filters
     * @return list<Client>
     */
    public function list(array $filters = []): array
    {
        $data = $this->getJson('/clients', $filters);

        $clientsNode = $data['clients']['client'] ?? [];

        if ($clientsNode === null || $clientsNode === []) {
            return [];
        }

        if (is_array($clientsNode) && array_is_list($clientsNode)) {
            $rows = $clientsNode;
        } elseif (is_array($clientsNode)) {
            $rows = [$clientsNode];
        } else {
            $rows = [];
        }

        /** @var list<Client> $models */
        $models = array_map(
            static fn(array $row): Client => Client::fromArray($row),
            $rows
        );

        return $models;
    }

    /**
     * Holt einen einzelnen Client, oder null wenn nicht gefunden.
     *
     * Entspricht GET /clients/{id}
     */
    public function get(int $id): ?Client
    {
        $data = $this->getJsonOrNull("/clients/{$id}");

        if ($data === null) {
            return null;
        }

        $clientData = $data['client'] ?? null;

        if (!is_array($clientData)) {
            return null;
        }

        return Client::fromArray($clientData);
    }

    /**
     * Legt einen neuen Client an.
     *
     * Entspricht POST /clients
     *
     * @throws \RuntimeException Wenn die Response-Struktur unerwartet ist
     */
    public function create(ClientCreateOptions $options): Client
    {
        $payload = [
            'client' => $options->toArray(),
        ];

        $data = $this->postJson('/clients', $payload);

        $created = $data['client'] ?? null;

        if (!is_array($created)) {
            throw new \RuntimeException('Unexpected response from Billomat when creating client.');
        }

        return Client::fromArray($created);
    }

    /**
     * Aktualisiert einen bestehenden Kunden in Billomat.
     *
     * Entspricht:
     * PUT /api/clients/{id}
     *
     * ⚠️ Einschränkungen laut Billomat:
     * - Ein Kunde kann nur aktualisiert werden, wenn er nicht archiviert ist.
     * - Es werden ausschließlich die im Payload gesetzten Felder geändert
     *   (Partial Update).
     * - Nicht unterstützte oder leere Felder werden ignoriert.
     *
     * Der Request wird mit einem "client"-Wrapper gesendet, wie von der
     * Billomat-API erwartet:
     *
     * {
     *   "client": { ... }
     * }
     *
     * @param int $id
     *   Die interne Billomat-ID des Kunden.
     *
     * @param ClientUpdateOptions $options
     *   Die zu ändernden Kundendaten. Nur gesetzte Felder werden übertragen.
     *
     * @return Client
     *   Das aktualisierte Client-Objekt, wie von der Billomat-API zurückgegeben.
     *
     * @throws ValidationException
     *   Bei ungültigen oder nicht erlaubten Änderungen (HTTP 400/422).
     *
     * @throws AuthenticationException
     *   Bei ungültigen API-Zugangsdaten (HTTP 401/403).
     *
     * @throws HttpException
     *   Bei allen sonstigen HTTP-Fehlern.
     *
     * @throws \RuntimeException
     *   Wenn die API ein unerwartetes oder unvollständiges Response-Format liefert.
     */
    public function update(int $id, ClientUpdateOptions $options): Client
    {
        $payload = [
            'client' => $options->toArray(),
        ];

        $data = $this->putJson("/clients/{$id}", $payload);

        /** @var array<string,mixed>|null $clientData */
        $clientData = $data['client'] ?? null;

        if (!is_array($clientData)) {
            throw new \RuntimeException('Unexpected response from Billomat when updating client.');
        }

        return Client::fromArray($clientData);
    }
}