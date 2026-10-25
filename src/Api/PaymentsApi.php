<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Closure;
use Justpilot\Billomat\Http\BillomatHttpClientInterface;
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
 *
 * @extends AbstractSubResourceApi<TPayment>
 */
final class PaymentsApi extends AbstractSubResourceApi
{
    protected const string PLURAL = 'payments';
    protected const string SINGULAR = 'payment';

    /**
     * @param string                                 $parent       Slug der Eltern-Ressource, z. B. "invoice" oder "credit-note"
     * @param Closure(array<string,mixed>): TPayment $hydrate      Hydrator, z. B. {@see \Justpilot\Billomat\Model\InvoiceComment::fromArray()}
     * @param class-string<TCreate>                  $optionsClass erwartete Create-Options
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
     * Listet Zahlungen, optional nur die eines Dokuments.
     *
     * Weitere Filter laut Doku: from, to (YYYY-MM-DD), type (Komma-separiert), user_id.
     *
     * @param array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return list<TPayment>
     */
    #[NoDiscard]
    public function list(?int $parentId = null, array $filters = []): array
    {
        if (null !== $parentId) {
            $filters = [$this->parentKey => $parentId] + $filters;
        }

        return $this->listWhere($filters);
    }

    /**
     * @param TCreate $options
     *
     * @return TPayment
     */
    public function create(WriteOptions $options): object
    {
        $this->assertOptions($options, $this->optionsClass);

        return $this->createFrom($options->toArray());
    }

    public function delete(int $id): void
    {
        $this->deleteById($id);
    }
}
