<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Http\BillomatHttpClientInterface;
use Justpilot\Billomat\Model\RecurringEmailReceiver;
use Justpilot\Billomat\Pagination\Page;
use NoDiscard;

/**
 * E-Mail-Empfänger einer Abo-Rechnung.
 *
 * Erreichbar über `$billomat->recurrings->emailReceivers`.
 *
 * Endpoints:
 *  - GET    /recurring-email-receivers?recurring_id={id}
 *  - GET    /recurring-email-receivers/{id}
 *  - POST   /recurring-email-receivers
 *  - DELETE /recurring-email-receivers/{id}
 *
 * Doku: https://www.billomat.com/api/abo-rechnungen/email-empfaenger/
 *
 * @extends AbstractSubResourceApi<RecurringEmailReceiver>
 */
final class RecurringEmailReceiversApi extends AbstractSubResourceApi
{
    protected const string PLURAL = 'email-receivers';
    protected const string SINGULAR = 'email-receiver';

    /**
     * @internal Instanzen kommen über `$billomat->recurrings->emailReceivers`
     */
    public function __construct(BillomatHttpClientInterface $http)
    {
        parent::__construct($http, 'recurring', RecurringEmailReceiver::fromArray(...));
    }

    /**
     * Listet die E-Mail-Empfänger einer Abo-Rechnung.
     *
     * @param SubResourceFilter|array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return list<RecurringEmailReceiver>
     */
    #[NoDiscard]
    public function list(int $recurringId, SubResourceFilter|array $filters = []): array
    {
        return $this->listFor($recurringId, $filters);
    }

    /**
     * Liefert eine einzelne Seite samt Pagination-Metadaten.
     *
     * @param SubResourceFilter|array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Page<RecurringEmailReceiver>
     */
    #[NoDiscard]
    public function listPage(int $recurringId, SubResourceFilter|array $filters = []): Page
    {
        return $this->pageFor($recurringId, $filters);
    }

    /**
     * Iteriert lazy durch alle Treffer und yieldet sie einzeln.
     *
     * @param SubResourceFilter|array<string, scalar|array<scalar|null>|null> $filters `page`/`per_page` werden überschrieben
     *
     * @return Generator<int, RecurringEmailReceiver>
     */
    #[NoDiscard]
    public function iterateAll(int $recurringId, SubResourceFilter|array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateFor($recurringId, $filters, $pageSize);
    }

    /**
     * Fügt einer Abo-Rechnung einen E-Mail-Empfänger hinzu.
     */
    public function create(int $recurringId, RecurringEmailReceiverCreateOptions $options): RecurringEmailReceiver
    {
        return $this->createFor($recurringId, $options);
    }

    public function delete(int $id): void
    {
        $this->deleteById($id);
    }
}
