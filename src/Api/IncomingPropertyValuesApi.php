<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Model\IncomingPropertyValue;
use Justpilot\Billomat\Pagination\Page;

/**
 * API-Wrapper für Werte von Eingangsrechnungs-Eigenschaften.
 */
final class IncomingPropertyValuesApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array|null> $filters
     *
     * @return list<IncomingPropertyValue>
     */
    public function list(array $filters = []): array
    {
        return $this->listResource('/incoming-property-values', 'incoming-property-values', 'incoming-property-value', IncomingPropertyValue::fromArray(...), $filters);
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
     * @return Page<IncomingPropertyValue>
     */
    public function listPage(array $filters = []): Page
    {
        return $this->listResourcePage('/incoming-property-values', 'incoming-property-values', 'incoming-property-value', IncomingPropertyValue::fromArray(...), $filters);
    }

    /**
     * Iteriert lazy durch alle IncomingPropertyValue und yieldet sie einzeln.
     *
     * Holt seitenweise pro {@code $pageSize}-Items und stoppt automatisch,
     * sobald die letzte Seite erreicht ist (analog `auto_paging_iter()` im
     * Stripe-SDK). Filter werden bei jeder Page-Anfrage mitgesendet; `page`
     * und `per_page` darin werden überschrieben.
     *
     * @param array<string, scalar|array|null> $filters
     *
     * @return Generator<int, IncomingPropertyValue>
     */
    public function iterateAll(array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/incoming-property-values', 'incoming-property-values', 'incoming-property-value', IncomingPropertyValue::fromArray(...), $filters, $pageSize);
    }

    public function get(int $id): ?IncomingPropertyValue
    {
        $data = $this->getJsonOrNull("/incoming-property-values/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['incoming-property-value'] ?? null;
        if (!\is_array($row)) {
            return null;
        }

        return IncomingPropertyValue::fromArray($row);
    }

    public function create(IncomingPropertyValueCreateOptions $options): IncomingPropertyValue
    {
        $payload = ['incoming-property-value' => $options->toArray()];

        $data = $this->postJson('/incoming-property-values', $payload);

        return IncomingPropertyValue::fromArray($this->unwrapEnvelope($data, 'incoming-property-value', 'creating incoming property value'));
    }
}
