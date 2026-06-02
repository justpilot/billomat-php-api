<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Model\Country;
use Justpilot\Billomat\Pagination\Page;

/**
 * Read-only API-Wrapper für die Länderliste.
 *
 * Doku: https://www.billomat.com/en/api/countries/
 */
final class CountriesApi extends AbstractApi
{
    /**
     * @return list<Country>
     */
    public function list(): array
    {
        return $this->listResource('/countries', 'countries', 'country', Country::fromArray(...));
    }

    /**
     * Liefert eine einzelne Seite samt Pagination-Metadaten.
     *
     * Identisch zu {@see list()}, gibt aber zusätzlich `@page`/`@per_page`/
     * `@total` aus dem Response-Envelope als {@see PageInfo} zurück. Nützlich
     * für UI mit klassischer "Seite 1/12, 234 Treffer"-Anzeige.
     *
     * @param array<string, scalar|array|null> $filters
     *
     * @return Page<Country>
     */
    public function listPage(array $filters = []): Page
    {
        return $this->listResourcePage('/countries', 'countries', 'country', Country::fromArray(...), $filters);
    }

    /**
     * Iteriert lazy durch alle Country und yieldet sie einzeln.
     *
     * Holt seitenweise pro {@code $pageSize}-Items und stoppt automatisch,
     * sobald die letzte Seite erreicht ist (analog `auto_paging_iter()` im
     * Stripe-SDK). Filter werden bei jeder Page-Anfrage mitgesendet; `page`
     * und `per_page` darin werden überschrieben.
     *
     * @param array<string, scalar|array|null> $filters
     *
     * @return Generator<int, Country>
     */
    public function iterateAll(array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/countries', 'countries', 'country', Country::fromArray(...), $filters, $pageSize);
    }
}
