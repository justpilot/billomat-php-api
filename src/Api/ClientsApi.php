<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

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
}