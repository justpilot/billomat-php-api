<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Http\BillomatHttpClientInterface;
use Justpilot\Billomat\Model\ReminderItem;
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

    public function __construct(BillomatHttpClientInterface $http)
    {
        parent::__construct($http, 'reminder', ReminderItem::fromArray(...));
    }

    /**
     * Listet die Positionen einer Mahnung.
     *
     * @param array<string, scalar|array<mixed>|null> $query zusätzliche Filter (page, per_page, …)
     *
     * @return list<ReminderItem>
     */
    #[NoDiscard]
    public function list(int $reminderId, array $query = []): array
    {
        return $this->listWhere(['reminder_id' => $reminderId, ...$query]);
    }
}
