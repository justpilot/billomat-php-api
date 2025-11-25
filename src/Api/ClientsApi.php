<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

final class ClientsApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array|null> $filters
     * @return list<array<string, mixed>>
     */
    public function list(array $filters = []): array
    {
        $data = $this->get('/clients', $filters);

        $clients = $data['clients']['client'] ?? [];

        if (!is_array($clients)) {
            return [];
        }

        // Wir geben vorerst einfache Arrays zurück; später können wir hier auf Modelle (Client-Objekte) umbauen
        /** @var list<array<string, mixed>> $clients */
        return $clients;
    }
}