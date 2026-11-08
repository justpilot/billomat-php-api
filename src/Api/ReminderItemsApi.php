<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Http\BillomatHttpClientInterface;
use Justpilot\Billomat\Model\ReminderItem;
use Justpilot\Billomat\Pagination\Page;
use NoDiscard;

/**
 * Positionen einer Mahnung.
 *
 * Erreichbar über `$billomat->reminders->items`. Die Positionen der offenen Rechnung legt
 * Billomat beim Anlegen der Mahnung selbst an; weitere (z. B. eine zusätzliche Gebühr)
 * lassen sich ergänzen, ändern und löschen.
 *
 * Endpoints:
 *  - GET /reminder-items?reminder_id={id}
 *  - GET /reminder-items/{id}
 *  - POST /reminder-items
 *  - PUT /reminder-items/{id}
 *  - DELETE /reminder-items/{id}
 *
 * Doku: https://www.billomat.com/api/mahnungen/positionen/
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
     * @param int                                                             $parentId ID der Mahnung (`reminder_id`)
     * @param SubResourceFilter|array<string, scalar|array<scalar|null>|null> $filters  Sortierung, Seite, …
     *
     * @return list<ReminderItem>
     */
    #[NoDiscard]
    public function list(int $parentId, SubResourceFilter|array $filters = []): array
    {
        return $this->listFor($parentId, $filters);
    }

    /**
     * Liefert eine einzelne Seite samt Pagination-Metadaten.
     *
     * @param int                                                             $parentId ID der Mahnung (`reminder_id`)
     * @param SubResourceFilter|array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Page<ReminderItem>
     */
    #[NoDiscard]
    public function listPage(int $parentId, SubResourceFilter|array $filters = []): Page
    {
        return $this->pageFor($parentId, $filters);
    }

    /**
     * Iteriert lazy durch alle Positionen einer Mahnung und yieldet sie einzeln.
     *
     * @param int                                                             $parentId ID der Mahnung (`reminder_id`)
     * @param SubResourceFilter|array<string, scalar|array<scalar|null>|null> $filters  `page`/`per_page` werden überschrieben
     *
     * @return Generator<int, ReminderItem>
     */
    #[NoDiscard]
    public function iterateAll(int $parentId, SubResourceFilter|array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateFor($parentId, $filters, $pageSize);
    }

    /**
     * Legt eine Position an einer Mahnung an.
     *
     * @param int $parentId ID der Mahnung (`reminder_id`)
     */
    public function create(int $parentId, ReminderItemCreateOptions $options): ReminderItem
    {
        return $this->createFor($parentId, $options);
    }

    /**
     * Ändert eine Position. Nur gesetzte Felder werden gesendet, die übrigen bleiben unverändert.
     */
    public function update(int $id, ReminderItemUpdateOptions $options): ReminderItem
    {
        return $this->updateFrom($id, $options->toArray());
    }

    public function delete(int $id): void
    {
        $this->deleteById($id);
    }
}
