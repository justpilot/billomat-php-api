<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Model\ClientProperty;
use Justpilot\Billomat\Pagination\Page;

/**
 * API-Wrapper für Definitionen von Kunden-Eigenschaften.
 *
 * Doku: https://www.billomat.com/en/api/settings/client-properties/
 */
final class ClientPropertiesApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array|null> $filters
     *
     * @return list<ClientProperty>
     */
    public function list(array $filters = []): array
    {
        return $this->listResource('/client-properties', 'client-properties', 'client-property', ClientProperty::fromArray(...), $filters);
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
     * @return Page<ClientProperty>
     */
    public function listPage(array $filters = []): Page
    {
        return $this->listResourcePage('/client-properties', 'client-properties', 'client-property', ClientProperty::fromArray(...), $filters);
    }

    /**
     * Iteriert lazy durch alle ClientProperty und yieldet sie einzeln.
     *
     * Holt seitenweise pro {@code $pageSize}-Items und stoppt automatisch,
     * sobald die letzte Seite erreicht ist (analog `auto_paging_iter()` im
     * Stripe-SDK). Filter werden bei jeder Page-Anfrage mitgesendet; `page`
     * und `per_page` darin werden überschrieben.
     *
     * @param array<string, scalar|array|null> $filters
     *
     * @return Generator<int, ClientProperty>
     */
    public function iterateAll(array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/client-properties', 'client-properties', 'client-property', ClientProperty::fromArray(...), $filters, $pageSize);
    }

    public function get(int $id): ?ClientProperty
    {
        $data = $this->getJsonOrNull("/client-properties/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['client-property'] ?? null;
        if (!\is_array($row)) {
            return null;
        }

        return ClientProperty::fromArray($row);
    }

    public function create(PropertyCreateOptions $options): ClientProperty
    {
        $payload = ['client-property' => $options->toArray()];

        $data = $this->postJson('/client-properties', $payload);

        return ClientProperty::fromArray($this->unwrapEnvelope($data, 'client-property', 'creating client property'));
    }

    public function update(int $id, PropertyCreateOptions $options): ClientProperty
    {
        $payload = ['client-property' => $options->toArray()];

        $data = $this->putJson("/client-properties/{$id}", $payload);

        return ClientProperty::fromArray($this->unwrapEnvelope($data, 'client-property', 'updating client property'));
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/client-properties/{$id}");

        return true;
    }
}
