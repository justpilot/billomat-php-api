<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Model\IncomingProperty;
use Justpilot\Billomat\Pagination\Page;

/**
 * API-Wrapper für Definitionen von Eigenschaften für Eingangsrechnungen.
 */
final class IncomingPropertiesApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array|null> $filters
     *
     * @return list<IncomingProperty>
     */
    public function list(array $filters = []): array
    {
        return $this->listResource('/incoming-properties', 'incoming-properties', 'incoming-property', IncomingProperty::fromArray(...), $filters);
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
     * @return Page<IncomingProperty>
     */
    public function listPage(array $filters = []): Page
    {
        return $this->listResourcePage('/incoming-properties', 'incoming-properties', 'incoming-property', IncomingProperty::fromArray(...), $filters);
    }

    /**
     * Iteriert lazy durch alle IncomingProperty und yieldet sie einzeln.
     *
     * Holt seitenweise pro {@code $pageSize}-Items und stoppt automatisch,
     * sobald die letzte Seite erreicht ist (analog `auto_paging_iter()` im
     * Stripe-SDK). Filter werden bei jeder Page-Anfrage mitgesendet; `page`
     * und `per_page` darin werden überschrieben.
     *
     * @param array<string, scalar|array|null> $filters
     *
     * @return Generator<int, IncomingProperty>
     */
    public function iterateAll(array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/incoming-properties', 'incoming-properties', 'incoming-property', IncomingProperty::fromArray(...), $filters, $pageSize);
    }

    public function get(int $id): ?IncomingProperty
    {
        $data = $this->getJsonOrNull("/incoming-properties/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['incoming-property'] ?? null;
        if (!\is_array($row)) {
            return null;
        }

        return IncomingProperty::fromArray($row);
    }

    public function create(PropertyCreateOptions $options): IncomingProperty
    {
        $payload = ['incoming-property' => $options->toArray()];

        $data = $this->postJson('/incoming-properties', $payload);

        return IncomingProperty::fromArray($this->unwrapEnvelope($data, 'incoming-property', 'creating incoming property'));
    }

    public function update(int $id, PropertyCreateOptions $options): IncomingProperty
    {
        $payload = ['incoming-property' => $options->toArray()];

        $data = $this->putJson("/incoming-properties/{$id}", $payload);

        return IncomingProperty::fromArray($this->unwrapEnvelope($data, 'incoming-property', 'updating incoming property'));
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/incoming-properties/{$id}");

        return true;
    }
}
