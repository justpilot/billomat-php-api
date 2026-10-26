<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Closure;
use Generator;
use Justpilot\Billomat\Http\BillomatHttpClientInterface;
use Justpilot\Billomat\Pagination\Page;
use NoDiscard;

/**
 * Zahlungen eines Dokuments (`/{parent}-payments`).
 *
 * Erreichbar über die Eltern-Ressource, z. B. `$billomat->invoices->payments`.
 *
 * Endpoints (am Beispiel Rechnungen):
 *  - GET    /invoice-payments
 *  - GET    /invoice-payments/{id}
 *  - POST   /invoice-payments
 *  - DELETE /invoice-payments/{id} (setzt die Rechnung wieder auf OPEN bzw. OVERDUE)
 *
 * Doku: https://www.billomat.com/api/rechnungen/zahlungen/
 *
 * @template TPayment of object
 * @template TCreate of WriteOptions
 * @template TFilter of ListFilter
 *
 * @extends AbstractSubResourceApi<TPayment>
 */
final class PaymentsApi extends AbstractSubResourceApi
{
    protected const string PLURAL = 'payments';
    protected const string SINGULAR = 'payment';

    /**
     * @internal Instanzen kommen über die Eltern-API, z. B. `$billomat->invoices->payments`
     *
     * @param string                                 $parent       Slug der Eltern-Ressource, z. B. "invoice" oder "credit-note"
     * @param Closure(array<string,mixed>): TPayment $hydrate      Hydrator, z. B. {@see \Justpilot\Billomat\Model\InvoicePayment::fromArray()}
     * @param class-string<TCreate>                  $optionsClass erwartete Create-Options
     * @param class-string<TFilter>                  $filterClass  erwarteter Listen-Filter, z. B. {@see InvoicePaymentFilter}
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
     * Listet Zahlungen, optional nur die eines Dokuments.
     *
     * @param TFilter|array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return list<TPayment>
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
     * @return Page<TPayment>
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
     * @return Generator<int, TPayment>
     */
    #[NoDiscard]
    public function iterateAll(?int $parentId = null, ListFilter|array $filters = [], int $pageSize = 100): Generator
    {
        $this->assertFilter($filters, $this->filterClass);

        yield from $this->iterateFor($parentId, $filters, $pageSize);
    }

    /**
     * Erfasst eine Zahlung zu einem Dokument.
     *
     * @param TCreate $options
     *
     * @return TPayment
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
}
