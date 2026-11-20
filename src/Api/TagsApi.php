<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Closure;
use Generator;
use Justpilot\Billomat\Exception\NotFoundException;
use Justpilot\Billomat\Http\BillomatHttpClientInterface;
use Justpilot\Billomat\Pagination\Page;
use NoDiscard;

/**
 * Schlagworte einer Ressource (`/{parent}-tags`).
 *
 * Erreichbar über die Eltern-Ressource, z. B. `$billomat->invoices->tags`.
 *
 * Endpoints (am Beispiel Rechnungen):
 *  - GET    /invoice-tags?invoice_id={id} (Tags einer Rechnung)
 *  - GET    /invoice-tags                 (Tag-Cloud, aggregiert)
 *  - GET    /invoice-tags/{id}
 *  - POST   /invoice-tags
 *  - DELETE /invoice-tags/{id}
 *
 * Doku: https://www.billomat.com/api/rechnungen/schlagworte/
 *
 * @template TTag of object
 * @template TCloudEntry of object
 * @template TCreate of WriteOptions
 *
 * @extends AbstractSubResourceApi<TTag>
 */
final class TagsApi extends AbstractSubResourceApi
{
    protected const string PLURAL = 'tags';
    protected const string SINGULAR = 'tag';

    /**
     * @internal Instanzen kommen über die Eltern-API, z. B. `$billomat->invoices->tags`
     *
     * @param Closure(array<string,mixed>): TTag        $hydrate           Hydrator für einzelne Tags
     * @param Closure(array<string,mixed>): TCloudEntry $hydrateCloudEntry Hydrator für Einträge der Tag-Cloud
     * @param class-string<TCreate>                     $optionsClass      erwartete Create-Options
     */
    public function __construct(
        BillomatHttpClientInterface $http,
        string $parent,
        Closure $hydrate,
        private readonly Closure $hydrateCloudEntry,
        private readonly string $optionsClass,
    ) {
        parent::__construct($http, $parent, $hydrate);
    }

    /**
     * Listet die Schlagworte eines Datensatzes.
     *
     * @param SubResourceFilter|array<string, scalar|array<scalar|null>|null> $filters Sortierung, Seite, …
     *
     * @return list<TTag>
     */
    #[NoDiscard]
    public function list(int $parentId, SubResourceFilter|array $filters = []): array
    {
        return $this->listFor($parentId, $filters);
    }

    /**
     * Liefert eine einzelne Seite samt Pagination-Metadaten.
     *
     * @param SubResourceFilter|array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Page<TTag>
     */
    #[NoDiscard]
    public function listPage(int $parentId, SubResourceFilter|array $filters = []): Page
    {
        return $this->pageFor($parentId, $filters);
    }

    /**
     * Iteriert lazy durch alle Schlagworte eines Datensatzes und yieldet sie einzeln.
     *
     * @param SubResourceFilter|array<string, scalar|array<scalar|null>|null> $filters `page`/`per_page` werden überschrieben
     *
     * @return Generator<int, TTag>
     */
    #[NoDiscard]
    public function iterateAll(int $parentId, SubResourceFilter|array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateFor($parentId, $filters, $pageSize);
    }

    /**
     * Tag-Cloud: alle Tags aggregiert mit Häufigkeit.
     *
     * Antwort-Pfad: {parent}-tags.tag
     *
     * @return list<TCloudEntry>
     */
    #[NoDiscard]
    public function cloud(): array
    {
        return $this->listResource($this->path, $this->listKey, 'tag', $this->hydrateCloudEntry);
    }

    /**
     * Hängt ein Schlagwort an einen Datensatz.
     *
     * @param TCreate $options
     *
     * @return TTag
     */
    public function create(int $parentId, WriteOptions $options): object
    {
        $this->assertOptions($options, $this->optionsClass);

        return $this->createFor($parentId, $options);
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
