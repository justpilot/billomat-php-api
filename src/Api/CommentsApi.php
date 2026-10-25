<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use BackedEnum;
use Closure;
use InvalidArgumentException;
use Justpilot\Billomat\Http\BillomatHttpClientInterface;
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
     * @param list<TActionKey>|null $actionKeys
     *
     * @return list<TComment>
     */
    #[NoDiscard]
    public function list(int $parentId, ?array $actionKeys = null): array
    {
        $query = [$this->parentKey => $parentId];

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

        return $this->listWhere($query);
    }

    /**
     * @param TCreate $options
     *
     * @return TComment
     */
    public function create(WriteOptions $options): object
    {
        $this->assertOptions($options, $this->optionsClass);

        return $this->createFrom($options->toArray());
    }

    public function delete(int $id): void
    {
        $this->deleteById($id);
    }
}
