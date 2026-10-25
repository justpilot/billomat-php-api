<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Closure;
use Generator;
use Justpilot\Billomat\Http\BillomatHttpClientInterface;
use Justpilot\Billomat\Pagination\Page;
use NoDiscard;

/**
 * Attributwerte einer Ressource (`/{parent}-property-values`).
 *
 * Erreichbar über die Eltern-Ressource, z. B. `$billomat->clients->propertyValues`.
 *
 * Endpoints (am Beispiel Kunden):
 *  - GET  /client-property-values?client_id={id}
 *  - GET  /client-property-values/{id}
 *  - POST /client-property-values
 *
 * Doku: https://www.billomat.com/api/kunden/attribute/
 *
 * @template TValue of object
 * @template TCreate of WriteOptions
 *
 * @extends AbstractSubResourceApi<TValue>
 */
final class PropertyValuesApi extends AbstractSubResourceApi
{
    protected const string PLURAL = 'property-values';
    protected const string SINGULAR = 'property-value';

    /**
     * @param string                               $parent       Slug der Eltern-Ressource, z. B. "invoice" oder "credit-note"
     * @param Closure(array<string,mixed>): TValue $hydrate      Hydrator, z. B. {@see \Justpilot\Billomat\Model\InvoiceComment::fromArray()}
     * @param class-string<TCreate>                $optionsClass erwartete Create-Options
     */
    public function __construct(
        BillomatHttpClientInterface $http,
        string $parent,
        Closure $hydrate,
        private readonly string $optionsClass,
    ) {
        parent::__construct($http, $parent, $hydrate);
    }

    /**
     * Listet Attributwerte, optional nur die eines Datensatzes.
     *
     * Weitere Filter laut Doku: `{parent}_property_id`, `value`.
     *
     * @param array<string, scalar|array<mixed>|null> $filters
     *
     * @return list<TValue>
     */
    #[NoDiscard]
    public function list(?int $parentId = null, array $filters = []): array
    {
        return $this->listWhere($this->withParent($parentId, $filters));
    }

    /**
     * Liefert eine einzelne Seite samt Pagination-Metadaten.
     *
     * @param array<string, scalar|array<mixed>|null> $filters
     *
     * @return Page<TValue>
     */
    #[NoDiscard]
    public function listPage(?int $parentId = null, array $filters = []): Page
    {
        return $this->listResourcePage($this->path, $this->listKey, $this->key, $this->hydrate, $this->withParent($parentId, $filters));
    }

    /**
     * Iteriert lazy durch alle Attributwerte und yieldet sie einzeln.
     *
     * @param array<string, scalar|array<mixed>|null> $filters
     *
     * @return Generator<int, TValue>
     */
    #[NoDiscard]
    public function iterateAll(?int $parentId = null, array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource($this->path, $this->listKey, $this->key, $this->hydrate, $this->withParent($parentId, $filters), $pageSize);
    }

    /**
     * Setzt einen Attributwert.
     *
     * @param TCreate $options
     *
     * @return TValue
     */
    public function create(WriteOptions $options): object
    {
        $this->assertOptions($options, $this->optionsClass);

        return $this->createFrom($options->toArray());
    }

    /**
     * @param array<string, scalar|array<mixed>|null> $filters
     *
     * @return array<string, scalar|array<mixed>|null>
     */
    private function withParent(?int $parentId, array $filters): array
    {
        return null === $parentId ? $filters : [$this->parentKey => $parentId] + $filters;
    }
}
