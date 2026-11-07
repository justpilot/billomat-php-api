<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use BackedEnum;

/**
 * Filter, Sortierung und Pagination für die Kommentare eines Dokuments.
 *
 * Billomat filtert Kommentare außer nach der Eltern-ID (eigenes Argument von `list()`) nur nach
 * dem ActionKey. Die ActionKeys müssen zur Ressource passen, z. B. `InvoiceCommentActionKey` für
 * `$billomat->invoices->comments`; `CommentsApi` prüft das beim Aufruf.
 *
 * ```php
 * $billomat->invoices->comments->list($invoiceId, new CommentFilter(
 *     actionKeys: [InvoiceCommentActionKey::PAYMENT, InvoiceCommentActionKey::MAIL],
 * ));
 * ```
 *
 * Doku: https://www.billomat.com/api/rechnungen/kommentare/
 *
 * @template TActionKey of BackedEnum
 */
final readonly class CommentFilter implements ListFilter
{
    public function __construct(
        /**
         * Nur Kommentare mit einem dieser ActionKeys; leer heißt alle.
         *
         * Billomat-Filter: actionkey (kommasepariert)
         *
         * @var list<TActionKey>
         */
        public array $actionKeys = [],
        /** Sortierung, z. B. `OrderBy::desc('created')`. */
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

    /**
     * Kopie mit geänderten ActionKeys.
     *
     * @param TActionKey ...$actionKeys
     *
     * @return self<TActionKey>
     */
    public function withActionKeys(BackedEnum ...$actionKeys): self
    {
        return clone($this, ['actionKeys' => array_values($actionKeys)]);
    }

    /**
     * Kopie mit geändertem `orderBy`.
     *
     * @return self<TActionKey>
     */
    public function withOrderBy(?OrderBy $orderBy): self
    {
        return clone($this, ['orderBy' => $orderBy]);
    }

    /**
     * Kopie mit geändertem `page`.
     *
     * @return self<TActionKey>
     */
    public function withPage(?int $page): self
    {
        return clone($this, ['page' => $page]);
    }

    /**
     * Kopie mit geändertem `perPage`.
     *
     * @return self<TActionKey>
     */
    public function withPerPage(?int $perPage): self
    {
        return clone($this, ['perPage' => $perPage]);
    }

    /**
     * Kopie mit weiteren Query-Parametern.
     *
     * @param array<string, scalar|array<scalar>> $extra
     *
     * @return self<TActionKey>
     */
    public function withExtra(array $extra): self
    {
        return clone($this, ['extra' => $extra]);
    }

    public function toQuery(): array
    {
        return [
            ...array_filter([
                'actionkey' => [] === $this->actionKeys
                    ? null
                    : implode(',', array_map(static fn (BackedEnum $key): string => (string) $key->value, $this->actionKeys)),
                'order_by' => $this->orderBy?->toQuery(),
                'page' => $this->page,
                'per_page' => $this->perPage,
            ], static fn (mixed $v): bool => null !== $v),
            ...$this->extra,
        ];
    }
}
