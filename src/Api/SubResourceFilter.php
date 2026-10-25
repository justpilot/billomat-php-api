<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Sortierung und Pagination für Sub-Ressourcen ohne eigene Filterfelder.
 *
 * Gilt für Positionen, Kommentare, Schlagworte und E-Mail-Empfänger: Billomat filtert
 * diese Listen nur nach der Eltern-ID (eigenes Argument von `list()`), sortiert und
 * paginiert sie aber wie jede andere Liste.
 *
 * ```php
 * $billomat->invoices->items->list($invoiceId, new SubResourceFilter(orderBy: OrderBy::asc('position')));
 * ```
 *
 * Zahlungen und Attributwerte haben eigene Filter (`InvoicePaymentFilter`,
 * `ClientPropertyValueFilter`, …).
 *
 * Doku: https://www.billomat.com/en/api/basics/read-data/
 */
final readonly class SubResourceFilter implements ListFilter
{
    public function __construct(
        /** Sortierung, z. B. `OrderBy::desc('date')->thenAsc('name')`. */
        public ?OrderBy $orderBy = null,
        /**
         * Seite (ab 1); `iterateAll()` setzt sie selbst.
         *
         * Billomat-Filter: page
         */
        public ?int $page = null,
        /**
         * Einträge pro Seite (Billomat: Standard 100, maximal 1000).
         *
         * Billomat-Filter: per_page
         */
        public ?int $perPage = null,
        /**
         * Weitere Query-Parameter; überschreiben gleichnamige Felder.
         *
         * @var array<string, scalar|array<scalar>>
         */
        public array $extra = [],
    ) {
    }

    /** Kopie mit geändertem `orderBy`. */
    public function withOrderBy(?OrderBy $orderBy): self
    {
        return clone($this, ['orderBy' => $orderBy]);
    }

    /** Kopie mit geändertem `page`. */
    public function withPage(?int $page): self
    {
        return clone($this, ['page' => $page]);
    }

    /** Kopie mit geändertem `perPage`. */
    public function withPerPage(?int $perPage): self
    {
        return clone($this, ['perPage' => $perPage]);
    }

    /**
     * Kopie mit weiteren Query-Parametern.
     *
     * @param array<string, scalar|array<scalar>> $extra
     */
    public function withExtra(array $extra): self
    {
        return clone($this, ['extra' => $extra]);
    }

    public function toQuery(): array
    {
        return [
            ...array_filter([
                'order_by' => $this->orderBy?->toQuery(),
                'page' => $this->page,
                'per_page' => $this->perPage,
            ], static fn (mixed $v): bool => null !== $v),
            ...$this->extra,
        ];
    }
}
