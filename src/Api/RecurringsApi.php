<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Exception\NotFoundException;
use Justpilot\Billomat\Model\Recurring;
use Justpilot\Billomat\Model\RecurringItem;
use Justpilot\Billomat\Model\RecurringTag;
use Justpilot\Billomat\Model\RecurringTagCloudEntry;
use Justpilot\Billomat\Pagination\Page;
use NoDiscard;

/**
 * API-Wrapper für Abo-Rechnungen (Recurrings).
 *
 * Endpoints:
 *  - GET    /recurrings
 *  - GET    /recurrings/{id}
 *  - POST   /recurrings
 *  - PUT    /recurrings/{id}
 *  - DELETE /recurrings/{id}
 *
 * Doku: https://www.billomat.com/api/abo-rechnungen/
 */
final class RecurringsApi extends AbstractApi
{
    /**
     * Positionen, siehe {@see ItemsApi}.
     *
     * @var ItemsApi<RecurringItem, RecurringItemCreateOptions, RecurringItemUpdateOptions>
     */
    public private(set) ItemsApi $items {
        get => $this->items ??= new ItemsApi($this->http, 'recurring', RecurringItem::fromArray(...), RecurringItemCreateOptions::class, RecurringItemUpdateOptions::class);
    }

    /**
     * Schlagworte, siehe {@see TagsApi}.
     *
     * @var TagsApi<RecurringTag, RecurringTagCloudEntry, RecurringTagCreateOptions>
     */
    public private(set) TagsApi $tags {
        get => $this->tags ??= new TagsApi($this->http, 'recurring', RecurringTag::fromArray(...), RecurringTagCloudEntry::fromArray(...), RecurringTagCreateOptions::class);
    }

    /**
     * E-Mail-Empfänger, siehe {@see RecurringEmailReceiversApi}.
     */
    public private(set) RecurringEmailReceiversApi $emailReceivers {
        get => $this->emailReceivers ??= new RecurringEmailReceiversApi($this->http);
    }

    /**
     * Listet Abo-Rechnungen mit optionalen Filtern.
     *
     * Unterstützte Filter laut Doku: client_id, contact_id, name, payment_type,
     * cycle_number, cycle, label, intro, note, tags, article_id.
     *
     * @param RecurringFilter|array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return list<Recurring>
     */
    #[NoDiscard]
    public function list(RecurringFilter|array $filters = []): array
    {
        return $this->listResource('/recurrings', 'recurrings', 'recurring', Recurring::fromArray(...), $filters);
    }

    /**
     * Liefert eine einzelne Seite samt Pagination-Metadaten.
     *
     * Identisch zu {@see list()}, gibt aber zusätzlich `@page`/`@per_page`/
     * `@total` aus dem Response-Envelope als {@see PageInfo} zurück. Nützlich
     * für UI mit klassischer "Seite 1/12, 234 Treffer"-Anzeige.
     *
     * @param RecurringFilter|array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Page<Recurring>
     */
    #[NoDiscard]
    public function listPage(RecurringFilter|array $filters = []): Page
    {
        return $this->listResourcePage('/recurrings', 'recurrings', 'recurring', Recurring::fromArray(...), $filters);
    }

    /**
     * Iteriert lazy durch alle Abo-Rechnungen und yieldet sie einzeln.
     *
     * Holt seitenweise pro {@code $pageSize}-Items und stoppt automatisch,
     * sobald die letzte Seite erreicht ist (analog `auto_paging_iter()` im
     * Stripe-SDK). Filter werden bei jeder Page-Anfrage mitgesendet; `page`
     * und `per_page` darin werden überschrieben.
     *
     * @param RecurringFilter|array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Generator<int, Recurring>
     */
    #[NoDiscard]
    public function iterateAll(RecurringFilter|array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/recurrings', 'recurrings', 'recurring', Recurring::fromArray(...), $filters, $pageSize);
    }

    #[NoDiscard]
    public function get(int $id): ?Recurring
    {
        return $this->getResource("/recurrings/{$id}", 'recurring', Recurring::fromArray(...), 'fetching recurring');
    }

    /**
     * Wie {@see get()}, wirft aber eine {@see NotFoundException} statt null zurückzugeben.
     *
     * @throws NotFoundException wenn es den Datensatz nicht gibt (404)
     */
    #[NoDiscard]
    public function getOrFail(int $id): Recurring
    {
        return $this->get($id) ?? throw $this->notFound("/recurrings/{$id}");
    }

    public function create(RecurringCreateOptions $options): Recurring
    {
        $payload = ['recurring' => $options->toArray()];

        $data = $this->postJson('/recurrings', $payload);

        return Recurring::fromArray($this->unwrapEnvelope($data, 'recurring', 'creating recurring'));
    }

    public function update(int $id, RecurringUpdateOptions $options): Recurring
    {
        $payload = ['recurring' => $options->toArray()];

        $data = $this->putJson("/recurrings/{$id}", $payload);

        return Recurring::fromArray($this->unwrapEnvelope($data, 'recurring', 'updating recurring'));
    }

    public function delete(int $id): void
    {
        $this->deleteVoid("/recurrings/{$id}");
    }

    /**
     * Stoppt die Abo-Rechnung: Billomat setzt `next_creation_date` auf null und erzeugt keine
     * weiteren Rechnungen. Die Abo-Rechnung selbst bleibt erhalten.
     *
     * Entspricht PUT /recurrings/{id}/stop.
     */
    public function stop(int $id): void
    {
        $this->putVoid("/recurrings/{$id}/stop");
    }
}
