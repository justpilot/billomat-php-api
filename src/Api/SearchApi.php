<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\SearchResult;

/**
 * API-Wrapper für die Billomat-Volltextsuche.
 *
 * Sucht resourcenübergreifend nach einem Stichwort und liefert eine flache
 * Liste schlanker Treffer zurück. Wer die Details eines Treffers braucht,
 * holt sie über die jeweilige Ressource (`$billomat->invoices->get($id)`).
 *
 * Doku: https://www.billomat.com/api/suche/
 */
final class SearchApi extends AbstractApi
{
    /**
     * Sucht über alle relevanten Ressourcen.
     *
     * Entspricht GET /search?query={query}.
     *
     * @param array<string, scalar|array|null> $extraFilters zusätzliche Query-Parameter neben `query`
     *
     * @return list<SearchResult>
     */
    public function query(string $query, array $extraFilters = []): array
    {
        $filters = ['query' => $query, ...$extraFilters];

        return $this->listResource('/search', 'search', 'result', SearchResult::fromArray(...), $filters);
    }
}
