<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Http\BillomatHttpClientInterface;
use Justpilot\Billomat\Model\ReminderItem;
use Justpilot\Billomat\Pagination\Page;
use NoDiscard;

/**
 * Positionen einer Mahnung (read-only – Mahnungspositionen erzeugt Billomat selbst).
 *
 * Erreichbar über `$billomat->reminders->items`.
 *
 * Endpoints:
 *  - GET /reminder-items?reminder_id={id}
 *  - GET /reminder-items/{id}
 *
 * Doku: https://www.billomat.com/en/api/reminders/items/
 *
 * @extends AbstractSubResourceApi<ReminderItem>
 */
final class ReminderItemsApi extends AbstractSubResourceApi
{
    protected const string PLURAL = 'items';
    protected const string SINGULAR = 'item';

    /**
     * @internal Instanzen kommen über `$billomat->reminders->items`
     */
    public function __construct(BillomatHttpClientInterface $http)
    {
        parent::__construct($http, 'reminder', ReminderItem::fromArray(...));
    }

    /**
     * Listet die Positionen einer Mahnung.
     *
     * @param SubResourceFilter|array<string, scalar|array<scalar|null>|null> $filters Sortierung, Seite, …
     *
     * @return list<ReminderItem>
     */
    #[NoDiscard]
    public function list(int $reminderId, SubResourceFilter|array $filters = []): array
    {
        return $this->listFor($reminderId, $filters);
    }

    /**
     * Liefert eine einzelne Seite samt Pagination-Metadaten.
     *
     * @param SubResourceFilter|array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Page<ReminderItem>
     */
    #[NoDiscard]
    public function listPage(int $reminderId, SubResourceFilter|array $filters = []): Page
    {
        return $this->pageFor($reminderId, $filters);
    }

    /**
     * Iteriert lazy durch alle Positionen einer Mahnung und yieldet sie einzeln.
     *
     * @param SubResourceFilter|array<string, scalar|array<scalar|null>|null> $filters `page`/`per_page` werden überschrieben
     *
     * @return Generator<int, ReminderItem>
     */
    #[NoDiscard]
    public function iterateAll(int $reminderId, SubResourceFilter|array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateFor($reminderId, $filters, $pageSize);
    }
}
