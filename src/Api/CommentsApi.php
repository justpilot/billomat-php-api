<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use BackedEnum;
use Closure;
use Generator;
use InvalidArgumentException;
use Justpilot\Billomat\Http\BillomatHttpClientInterface;
use Justpilot\Billomat\Pagination\Page;
use NoDiscard;

/**
 * Kommentare eines Dokuments (`/{parent}-comments`).
 *
 * Erreichbar über die Eltern-Ressource, z. B. `$billomat->invoices->comments`.
 *
 * Endpoints (am Beispiel Rechnungen):
 *  - GET    /invoice-comments?invoice_id={id}
 *  - GET    /invoice-comments/{id}
 *  - POST   /invoice-comments
 *  - DELETE /invoice-comments/{id}
 *
 * Doku: https://www.billomat.com/api/rechnungen/kommentare/
 *
 * @template TComment of object
 * @template TCreate of WriteOptions
 * @template TActionKey of BackedEnum
 *
 * @extends AbstractSubResourceApi<TComment>
 */
final class CommentsApi extends AbstractSubResourceApi
{
    protected const string PLURAL = 'comments';
    protected const string SINGULAR = 'comment';

    /**
     * @internal Instanzen kommen über die Eltern-API, z. B. `$billomat->invoices->comments`
     *
     * @param string                                 $parent        Slug der Eltern-Ressource, z. B. "invoice" oder "credit-note"
     * @param Closure(array<string,mixed>): TComment $hydrate       Hydrator, z. B. {@see \Justpilot\Billomat\Model\InvoiceComment::fromArray()}
     * @param class-string<TCreate>                  $optionsClass  erwartete Create-Options
     * @param class-string<TActionKey>               $actionKeyEnum ActionKey-Enum der Ressource
     */
    public function __construct(
        BillomatHttpClientInterface $http,
        string $parent,
        Closure $hydrate,
        private readonly string $optionsClass,
        private readonly string $actionKeyEnum,
    ) {
        parent::__construct($http, $parent, $hydrate);
    }

    /**
     * Listet die Kommentare eines Dokuments.
     *
     * Auf bestimmte ActionKeys filtert {@see CommentFilter}: `new CommentFilter(actionKeys: [InvoiceCommentActionKey::PAYMENT])`.
     *
     * @param CommentFilter<TActionKey>|array<string, scalar|array<scalar|null>|null> $filters ActionKeys, Sortierung, Seite, …
     *
     * @return list<TComment>
     *
     * @throws InvalidArgumentException wenn ein ActionKey nicht zur Ressource passt
     */
    #[NoDiscard]
    public function list(int $parentId, CommentFilter|array $filters = []): array
    {
        return $this->listFor($parentId, $this->checkedFilters($filters));
    }

    /**
     * Liefert eine einzelne Seite samt Pagination-Metadaten.
     *
     * @param CommentFilter<TActionKey>|array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Page<TComment>
     *
     * @throws InvalidArgumentException wenn ein ActionKey nicht zur Ressource passt
     */
    #[NoDiscard]
    public function listPage(int $parentId, CommentFilter|array $filters = []): Page
    {
        return $this->pageFor($parentId, $this->checkedFilters($filters));
    }

    /**
     * Iteriert lazy durch alle Kommentare eines Dokuments und yieldet sie einzeln.
     *
     * @param CommentFilter<TActionKey>|array<string, scalar|array<scalar|null>|null> $filters `page`/`per_page` werden überschrieben
     *
     * @return Generator<int, TComment>
     *
     * @throws InvalidArgumentException wenn ein ActionKey nicht zur Ressource passt
     */
    #[NoDiscard]
    public function iterateAll(int $parentId, CommentFilter|array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateFor($parentId, $this->checkedFilters($filters), $pageSize);
    }

    /**
     * Legt einen Kommentar an einem Dokument an.
     *
     * @param TCreate $options
     *
     * @return TComment
     */
    public function create(int $parentId, WriteOptions $options): object
    {
        $this->assertOptions($options, $this->optionsClass);

        return $this->createFor($parentId, $options);
    }

    public function delete(int $id): void
    {
        $this->deleteById($id);
    }

    /**
     * Prüft, dass die ActionKeys des Filters zur Ressource passen.
     *
     * @param CommentFilter<TActionKey>|array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return CommentFilter<TActionKey>|array<string, scalar|array<scalar|null>|null>
     */
    private function checkedFilters(CommentFilter|array $filters): CommentFilter|array
    {
        if ($filters instanceof CommentFilter) {
            foreach ($filters->actionKeys as $actionKey) {
                if (!$actionKey instanceof $this->actionKeyEnum) {
                    throw new InvalidArgumentException(\sprintf('Expected %s, got %s.', $this->actionKeyEnum, $actionKey::class));
                }
            }
        }

        return $filters;
    }
}
