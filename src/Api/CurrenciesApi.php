<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Model\Currency;
use Justpilot\Billomat\Pagination\Page;
use NoDiscard;

/**
 * Read-only API-Wrapper für die Währungsliste.
 *
 * Doku: https://www.billomat.com/en/api/currencies/
 */
final class CurrenciesApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return list<Currency>
     */
    #[NoDiscard]
    public function list(array $filters = []): array
    {
        return $this->listResource('/currencies', 'currencies', 'currency', Currency::fromArray(...), $filters);
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
     * @return Page<Currency>
     */
    #[NoDiscard]
    public function listPage(array $filters = []): Page
    {
        return $this->listResourcePage('/currencies', 'currencies', 'currency', Currency::fromArray(...), $filters);
    }

    /**
     * Iteriert lazy durch alle Currency und yieldet sie einzeln.
     *
     * Holt seitenweise pro {@code $pageSize}-Items und stoppt automatisch,
     * sobald die letzte Seite erreicht ist (analog `auto_paging_iter()` im
     * Stripe-SDK). Filter werden bei jeder Page-Anfrage mitgesendet; `page`
     * und `per_page` darin werden überschrieben.
     *
     * @param array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Generator<int, Currency>
     */
    #[NoDiscard]
    public function iterateAll(array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/currencies', 'currencies', 'currency', Currency::fromArray(...), $filters, $pageSize);
    }
}
