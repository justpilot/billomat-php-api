<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Pagination;

/**
 * Pagination-Metadaten aus einer Billomat-List-Response.
 *
 * Billomat liefert die Werte als `@page`/`@per_page`/`@total` neben dem
 * eigentlichen Listeneintrag im äußeren Envelope, jeweils als String. Diese
 * Klasse normalisiert sie zu Ints und stellt zwei abgeleitete Werte bereit.
 *
 * `$total` ist `null`, wenn der Endpunkt keine `@total`-Metadaten liefert.
 * In dem Fall ist die Gesamtzahl der Seiten unbekannt; {@see totalPages()}
 * liefert `null`, {@see hasNextPage()} optimistisch `true`. Der Iterator
 * in {@see \Justpilot\Billomat\Api\AbstractApi::iterateResource()} nutzt
 * zusätzlich das `count(items) < perPage`-Heuristik, um terminieren zu können.
 *
 * @see Page
 */
final readonly class PageInfo
{
    public function __construct(
        public int $page,
        public int $perPage,
        public ?int $total,
    ) {
    }

    /**
     * Liefert die Gesamtzahl der Seiten — oder `null`, wenn unbekannt.
     *
     * Bei `perPage <= 0` wird `1` zurückgegeben (Schutz gegen Division-by-zero
     * für Endpunkte, die keine Pagination-Metadaten liefern).
     */
    public function totalPages(): ?int
    {
        if (null === $this->total) {
            return null;
        }

        if ($this->perPage <= 0) {
            return 1;
        }

        return (int) ceil($this->total / $this->perPage);
    }

    /**
     * Gibt `true` zurück, wenn nach der aktuellen Seite noch weitere Seiten
     * existieren — oder wenn die Gesamtzahl unbekannt ist.
     */
    public function hasNextPage(): bool
    {
        $totalPages = $this->totalPages();

        if (null === $totalPages) {
            return true;
        }

        return $this->page < $totalPages;
    }
}
