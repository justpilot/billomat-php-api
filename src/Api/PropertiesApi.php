<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Exception\NotFoundException;
use Justpilot\Billomat\Pagination\Page;
use NoDiscard;

/**
 * Attribut-Definitionen einer Ressource (`/{parent}-properties`).
 *
 * Erreichbar über die Eltern-Ressource, z. B. `$billomat->clients->properties`.
 *
 * Endpoints (am Beispiel Kunden):
 *  - GET    /client-properties
 *  - GET    /client-properties/{id}
 *  - POST   /client-properties
 *  - PUT    /client-properties/{id}
 *  - DELETE /client-properties/{id}
 *
 * Doku: https://www.billomat.com/en/api/settings/client-properties/
 *
 * @template TProperty of object
 *
 * @extends AbstractSubResourceApi<TProperty>
 */
final class PropertiesApi extends AbstractSubResourceApi
{
    protected const string PLURAL = 'properties';
    protected const string SINGULAR = 'property';

    /**
     * @param SubResourceFilter|array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return list<TProperty>
     */
    #[NoDiscard]
    public function list(SubResourceFilter|array $filters = []): array
    {
        return $this->listFor(null, $filters);
    }

    /**
     * Liefert eine einzelne Seite samt Pagination-Metadaten.
     *
     * @param SubResourceFilter|array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Page<TProperty>
     */
    #[NoDiscard]
    public function listPage(SubResourceFilter|array $filters = []): Page
    {
        return $this->pageFor(null, $filters);
    }

    /**
     * Iteriert lazy durch alle Attribut-Definitionen und yieldet sie einzeln.
     *
     * @param SubResourceFilter|array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Generator<int, TProperty>
     */
    #[NoDiscard]
    public function iterateAll(SubResourceFilter|array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateFor(null, $filters, $pageSize);
    }

    /**
     * @return TProperty
     */
    public function create(PropertyCreateOptions $options): object
    {
        return $this->createFrom($options->toArray());
    }

    /**
     * @return TProperty
     */
    public function update(int $id, PropertyUpdateOptions $options): object
    {
        return $this->updateFrom($id, $options->toArray());
    }

    public function delete(int $id): void
    {
        $this->deleteById($id);
    }

    /**
     * Liest das eigene Metadaten-Feld `customfield` (z. B. die ID im eigenen System); null, wenn es leer ist.
     *
     * Entspricht GET /{resource}/{id}/customfield. Doku: https://www.billomat.com/api/grundlagen/eigene-meta-daten/
     *
     * @throws NotFoundException wenn es den Datensatz nicht gibt (404)
     */
    #[NoDiscard]
    public function getCustomField(int $id): ?string
    {
        return $this->customFieldOf($id);
    }

    /**
     * Setzt das eigene Metadaten-Feld `customfield`; ein leerer String leert es.
     *
     * Entspricht PUT /{resource}/{id}/customfield. Über die Update-Options lässt sich das Feld nicht setzen.
     */
    public function setCustomField(int $id, string $value): void
    {
        $this->storeCustomFieldOf($id, $value);
    }
}
