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
 * @template TFilter of ListFilter
 *
 * @extends AbstractSubResourceApi<TValue>
 */
final class PropertyValuesApi extends AbstractSubResourceApi
{
    protected const string PLURAL = 'property-values';
    protected const string SINGULAR = 'property-value';

    /**
     * @internal Instanzen kommen über die Eltern-API, z. B. `$billomat->clients->propertyValues`
     *
     * @param string                               $parent       Slug der Eltern-Ressource, z. B. "invoice" oder "credit-note"
     * @param Closure(array<string,mixed>): TValue $hydrate      Hydrator, z. B. {@see \Justpilot\Billomat\Model\ClientPropertyValue::fromArray()}
     * @param class-string<TCreate>                $optionsClass erwartete Create-Options
     * @param class-string<TFilter>                $filterClass  erwarteter Listen-Filter, z. B. {@see ClientPropertyValueFilter}
     */
    public function __construct(
        BillomatHttpClientInterface $http,
        string $parent,
        Closure $hydrate,
        private readonly string $optionsClass,
        private readonly string $filterClass,
    ) {
        parent::__construct($http, $parent, $hydrate);
    }

    /**
     * Listet Attributwerte, optional nur die eines Datensatzes.
     *
     * @param TFilter|array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return list<TValue>
     */
    #[NoDiscard]
    public function list(?int $parentId = null, ListFilter|array $filters = []): array
    {
        $this->assertFilter($filters, $this->filterClass);

        return $this->listFor($parentId, $filters);
    }

    /**
     * Liefert eine einzelne Seite samt Pagination-Metadaten.
     *
     * @param TFilter|array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Page<TValue>
     */
    #[NoDiscard]
    public function listPage(?int $parentId = null, ListFilter|array $filters = []): Page
    {
        $this->assertFilter($filters, $this->filterClass);

        return $this->pageFor($parentId, $filters);
    }

    /**
     * Iteriert lazy durch alle Treffer und yieldet sie einzeln.
     *
     * @param TFilter|array<string, scalar|array<scalar|null>|null> $filters `page`/`per_page` werden überschrieben
     *
     * @return Generator<int, TValue>
     */
    #[NoDiscard]
    public function iterateAll(?int $parentId = null, ListFilter|array $filters = [], int $pageSize = 100): Generator
    {
        $this->assertFilter($filters, $this->filterClass);

        yield from $this->iterateFor($parentId, $filters, $pageSize);
    }

    /**
     * Setzt einen Attributwert.
     *
     * @param TCreate $options
     *
     * @return TValue
     */
    public function create(int $parentId, WriteOptions $options): object
    {
        $this->assertOptions($options, $this->optionsClass);

        return $this->createFor($parentId, $options);
    }
}
