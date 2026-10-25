<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Closure;
use Justpilot\Billomat\Http\BillomatHttpClientInterface;
use NoDiscard;

/**
 * Positionen eines Dokuments (`/{parent}-items`).
 *
 * Erreichbar über die Eltern-Ressource, z. B. `$billomat->invoices->items`.
 *
 * Endpoints (am Beispiel Rechnungen):
 *  - GET    /invoice-items?invoice_id={id}
 *  - GET    /invoice-items/{id}
 *  - POST   /invoice-items
 *  - PUT    /invoice-items/{id}
 *  - DELETE /invoice-items/{id}
 *
 * Doku: https://www.billomat.com/api/rechnungen/positionen/
 *
 * @template TItem of object
 * @template TCreate of WriteOptions
 * @template TUpdate of WriteOptions
 *
 * @extends AbstractSubResourceApi<TItem>
 */
final class ItemsApi extends AbstractSubResourceApi
{
    protected const string PLURAL = 'items';
    protected const string SINGULAR = 'item';

    /**
     * @param string                              $parent             Slug der Eltern-Ressource, z. B. "invoice" oder "credit-note"
     * @param Closure(array<string,mixed>): TItem $hydrate            Hydrator, z. B. {@see \Justpilot\Billomat\Model\InvoiceComment::fromArray()}
     * @param class-string<TCreate>               $optionsClass       erwartete Create-Options
     * @param class-string<TUpdate>               $updateOptionsClass erwartete Update-Options
     */
    public function __construct(
        BillomatHttpClientInterface $http,
        string $parent,
        Closure $hydrate,
        private readonly string $optionsClass,
        private readonly string $updateOptionsClass,
    ) {
        parent::__construct($http, $parent, $hydrate);
    }

    /**
     * Listet die Positionen eines Dokuments.
     *
     * @param array<string, scalar|array<scalar|null>|null> $query zusätzliche Filter (page, per_page, …)
     *
     * @return list<TItem>
     */
    #[NoDiscard]
    public function list(int $parentId, array $query = []): array
    {
        return $this->listWhere([$this->parentKey => $parentId, ...$query]);
    }

    /**
     * Legt eine Position an einem Dokument an.
     *
     * @param TCreate $options
     *
     * @return TItem
     */
    public function create(int $parentId, WriteOptions $options): object
    {
        $this->assertOptions($options, $this->optionsClass);

        return $this->createFrom([...$options->toArray(), $this->parentKey => $parentId]);
    }

    /**
     * Ändert eine Position. Nur gesetzte Felder werden gesendet, die übrigen bleiben unverändert.
     *
     * @param TUpdate $options
     *
     * @return TItem
     */
    public function update(int $id, WriteOptions $options): object
    {
        $this->assertOptions($options, $this->updateOptionsClass);

        return $this->updateFrom($id, $options->toArray());
    }

    public function delete(int $id): void
    {
        $this->deleteById($id);
    }
}
