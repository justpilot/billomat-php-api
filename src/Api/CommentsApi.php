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
     * Über `$actionKeys` lässt sich auf bestimmte ActionKey-Typen filtern.
     *
     * @param list<TActionKey>|null                                           $actionKeys
     * @param SubResourceFilter|array<string, scalar|array<scalar|null>|null> $filters    Sortierung, Seite, …
     *
     * @return list<TComment>
     */
    #[NoDiscard]
    public function list(int $parentId, ?array $actionKeys = null, SubResourceFilter|array $filters = []): array
    {
        return $this->listFor($parentId, $this->withActionKeys($actionKeys, $filters));
    }

    /**
     * Liefert eine einzelne Seite samt Pagination-Metadaten.
     *
     * @param list<TActionKey>|null                                           $actionKeys
     * @param SubResourceFilter|array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Page<TComment>
     */
    #[NoDiscard]
    public function listPage(int $parentId, ?array $actionKeys = null, SubResourceFilter|array $filters = []): Page
    {
        return $this->pageFor($parentId, $this->withActionKeys($actionKeys, $filters));
    }

    /**
     * Iteriert lazy durch alle Kommentare eines Dokuments und yieldet sie einzeln.
     *
     * @param list<TActionKey>|null                                           $actionKeys
     * @param SubResourceFilter|array<string, scalar|array<scalar|null>|null> $filters    `page`/`per_page` werden überschrieben
     *
     * @return Generator<int, TComment>
     */
    #[NoDiscard]
    public function iterateAll(int $parentId, ?array $actionKeys = null, SubResourceFilter|array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateFor($parentId, $this->withActionKeys($actionKeys, $filters), $pageSize);
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
     * Ergänzt den Query um `actionkey` (kommasepariert).
     *
     * @param list<TActionKey>|null                                           $actionKeys
     * @param SubResourceFilter|array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return array<string, scalar|array<scalar|null>|null>
     */
    private function withActionKeys(?array $actionKeys, SubResourceFilter|array $filters): array
    {
        $query = self::filterQuery($filters);

        if (null !== $actionKeys && [] !== $actionKeys) {
            $query['actionkey'] = implode(',', array_map(
                function (BackedEnum $a): string {
                    if (!$a instanceof $this->actionKeyEnum) {
                        throw new InvalidArgumentException(\sprintf('Expected %s, got %s.', $this->actionKeyEnum, $a::class));
                    }

                    return (string) $a->value;
                },
                $actionKeys,
            ));
        }

        return $query;
    }
}
