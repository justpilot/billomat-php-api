<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Exception\NotFoundException;
use Justpilot\Billomat\Model\Unit;
use Justpilot\Billomat\Pagination\Page;
use NoDiscard;

/**
 * API-Wrapper für Einheiten.
 */
final class UnitsApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return list<Unit>
     */
    #[NoDiscard]
    public function list(array $filters = []): array
    {
        return $this->listResource('/units', 'units', 'unit', Unit::fromArray(...), $filters);
    }

    /**
     * Liefert eine einzelne Seite samt Pagination-Metadaten.
     *
     * Identisch zu {@see list()}, gibt aber zusätzlich `@page`/`@per_page`/
     * `@total` aus dem Response-Envelope als {@see PageInfo} zurück. Nützlich
     * für UI mit klassischer "Seite 1/12, 234 Treffer"-Anzeige.
     *
     * @param array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Page<Unit>
     */
    #[NoDiscard]
    public function listPage(array $filters = []): Page
    {
        return $this->listResourcePage('/units', 'units', 'unit', Unit::fromArray(...), $filters);
    }

    /**
     * Iteriert lazy durch alle Unit und yieldet sie einzeln.
     *
     * Holt seitenweise pro {@code $pageSize}-Items und stoppt automatisch,
     * sobald die letzte Seite erreicht ist (analog `auto_paging_iter()` im
     * Stripe-SDK). Filter werden bei jeder Page-Anfrage mitgesendet; `page`
     * und `per_page` darin werden überschrieben.
     *
     * @param array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Generator<int, Unit>
     */
    #[NoDiscard]
    public function iterateAll(array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/units', 'units', 'unit', Unit::fromArray(...), $filters, $pageSize);
    }

    #[NoDiscard]
    public function get(int $id): ?Unit
    {
        return $this->getResource("/units/{$id}", 'unit', Unit::fromArray(...), 'fetching unit');
    }

    /**
     * Wie {@see get()}, wirft aber eine {@see NotFoundException} statt null zurückzugeben.
     *
     * @throws NotFoundException wenn es den Datensatz nicht gibt (404)
     */
    #[NoDiscard]
    public function getOrFail(int $id): Unit
    {
        return $this->get($id) ?? throw $this->notFound("/units/{$id}");
    }

    /**
     * Legt eine neue Einheit an.
     */
    public function create(UnitCreateOptions $options): Unit
    {
        $data = $this->postJson('/units', ['unit' => $options->toArray()]);

        return Unit::fromArray($this->unwrapEnvelope($data, 'unit', 'creating unit'));
    }

    /**
     * Ändert eine Einheit (PUT /units/{id}).
     *
     * Nur die in den Options gesetzten Felder werden gesendet.
     */
    public function update(int $id, UnitUpdateOptions $options): Unit
    {
        $data = $this->putJson("/units/{$id}", ['unit' => $options->toArray()]);

        return Unit::fromArray($this->unwrapEnvelope($data, 'unit', 'updating unit'));
    }

    /**
     * Löscht eine Einheit.
     */
    public function delete(int $id): void
    {
        $this->deleteVoid("/units/{$id}");
    }
}
