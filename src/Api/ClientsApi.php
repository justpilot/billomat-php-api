<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Client;

final class ClientsApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array|null> $filters
     * @return list<Client>
     */
    public function list(array $filters = []): array
    {
        $data = $this->get('/clients', $filters);

        $clientsNode = $data['clients']['client'] ?? [];

        if ($clientsNode === null || $clientsNode === []) {
            return [];
        }

        // Normalisieren auf list<array>
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
}