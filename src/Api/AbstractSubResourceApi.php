<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Closure;
use Generator;
use InvalidArgumentException;
use Justpilot\Billomat\Exception\NotFoundException;
use Justpilot\Billomat\Http\BillomatHttpClientInterface;
use Justpilot\Billomat\Pagination\Page;
use NoDiscard;

/**
 * Gemeinsame Basis der Sub-Ressourcen (Kommentare, Schlagworte, Positionen, …).
 *
 * Billomat legt Sub-Ressourcen flach unter `/{parent}-{name}` ab und filtert über
 * `{parent}_id`, z. B. `GET /invoice-comments?invoice_id=1`. Pfad, Envelope-Keys und
 * Filterfeld leiten sich aus dem Slug der Eltern-Ressource (`invoice`, `credit-note`, …)
 * und den Konstanten {@see self::PLURAL}/{@see self::SINGULAR} ab.
 *
 * @template TModel of object
 *
 * @internal Basis der SDK-eigenen Sub-Ressourcen, kein Erweiterungspunkt
 */
abstract class AbstractSubResourceApi extends AbstractApi
{
    /** Ressourcenname im Plural, z. B. "comments". */
    protected const string PLURAL = '';

    /** Ressourcenname im Singular, z. B. "comment". */
    protected const string SINGULAR = '';

    /** Endpunkt, z. B. "/invoice-comments". */
    protected readonly string $path;

    /** Äußerer Envelope-Key der Liste, z. B. "invoice-comments". */
    protected readonly string $listKey;

    /** Envelope-Key eines Datensatzes, z. B. "invoice-comment". */
    protected readonly string $key;

    /** Filterfeld der Eltern-ID, z. B. "invoice_id". */
    protected readonly string $parentKey;

    /**
     * @param string                               $parent  Slug der Eltern-Ressource, z. B. "invoice" oder "credit-note"
     * @param Closure(array<string,mixed>): TModel $hydrate Hydrator, z. B. InvoiceComment::fromArray(...)
     */
    public function __construct(
        BillomatHttpClientInterface $http,
        string $parent,
        protected readonly Closure $hydrate,
    ) {
        parent::__construct($http);

        $this->listKey = $parent.'-'.static::PLURAL;
        $this->path = '/'.$this->listKey;
        $this->key = $parent.'-'.static::SINGULAR;
        $this->parentKey = str_replace('-', '_', $parent).'_id';
    }

    /**
     * Holt einen einzelnen Datensatz.
     *
     * @return TModel|null null, wenn nicht gefunden (404)
     */
    #[NoDiscard]
    public function get(int $id): ?object
    {
        return $this->getResource("{$this->path}/{$id}", $this->key, $this->hydrate, 'fetching '.$this->label());
    }

    /**
     * Wie {@see get()}, wirft aber eine {@see NotFoundException} statt null zurückzugeben.
     *
     * @return TModel
     *
     * @throws NotFoundException wenn es den Datensatz nicht gibt (404)
     */
    #[NoDiscard]
    public function getOrFail(int $id): object
    {
        return $this->get($id) ?? throw $this->notFound("{$this->path}/{$id}");
    }

    /**
     * Listet Datensätze, optional nur die eines Eltern-Datensatzes.
     *
     * @param ListFilter|array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return list<TModel>
     */
    protected function listFor(?int $parentId, ListFilter|array $filters = []): array
    {
        return $this->listResource($this->path, $this->listKey, $this->key, $this->hydrate, $this->parentQuery($parentId, $filters));
    }

    /**
     * Wie {@see listFor()}, liefert aber eine Seite samt Pagination-Metadaten.
     *
     * @param ListFilter|array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Page<TModel>
     */
    protected function pageFor(?int $parentId, ListFilter|array $filters = []): Page
    {
        return $this->listResourcePage($this->path, $this->listKey, $this->key, $this->hydrate, $this->parentQuery($parentId, $filters));
    }

    /**
     * Wie {@see listFor()}, iteriert aber lazy über alle Seiten.
     *
     * @param ListFilter|array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Generator<int, TModel>
     */
    protected function iterateFor(?int $parentId, ListFilter|array $filters, int $pageSize): Generator
    {
        yield from $this->iterateResource($this->path, $this->listKey, $this->key, $this->hydrate, $this->parentQuery($parentId, $filters), $pageSize);
    }

    /**
     * Legt einen Datensatz an.
     *
     * @param array<string,mixed> $body
     *
     * @return TModel
     */
    protected function createFrom(array $body): object
    {
        $data = $this->postJson($this->path, [$this->key => $body]);

        return ($this->hydrate)($this->unwrapEnvelope($data, $this->key, 'creating '.$this->label()));
    }

    /**
     * Legt einen Datensatz an einem Eltern-Datensatz an; die Eltern-ID ergänzt den Payload.
     *
     * @return TModel
     */
    protected function createFor(int $parentId, WriteOptions $options): object
    {
        return $this->createFrom([...$options->toArray(), $this->parentKey => $parentId]);
    }

    /**
     * Aktualisiert einen Datensatz.
     *
     * @param array<string,mixed> $body
     *
     * @return TModel
     */
    protected function updateFrom(int $id, array $body): object
    {
        $data = $this->putJson("{$this->path}/{$id}", [$this->key => $body]);

        return ($this->hydrate)($this->unwrapEnvelope($data, $this->key, 'updating '.$this->label()));
    }

    /**
     * Stellt sicher, dass die Options zur Ressource passen (z. B. keine OfferCommentCreateOptions
     * an `$billomat->invoices->comments`).
     *
     * @param class-string<WriteOptions> $expected
     *
     * @throws InvalidArgumentException
     */
    protected function assertOptions(WriteOptions $options, string $expected): void
    {
        if (!$options instanceof $expected) {
            throw new InvalidArgumentException(\sprintf('Expected %s, got %s.', $expected, $options::class));
        }
    }

    /**
     * Stellt sicher, dass ein typisierter Filter zur Ressource passt (z. B. kein
     * CreditNotePaymentFilter an `$billomat->invoices->payments`); Arrays gehen durch.
     *
     * @param ListFilter|array<string, scalar|array<scalar|null>|null> $filters
     * @param class-string<ListFilter>                                 $expected
     *
     * @throws InvalidArgumentException
     */
    protected function assertFilter(ListFilter|array $filters, string $expected): void
    {
        if ($filters instanceof ListFilter && !$filters instanceof $expected) {
            throw new InvalidArgumentException(\sprintf('Expected %s, got %s.', $expected, $filters::class));
        }
    }

    protected function deleteById(int $id): void
    {
        $this->deleteVoid("{$this->path}/{$id}");
    }

    /**
     * Query aus Filter und Eltern-ID; die Eltern-ID hat Vorrang vor einem gleichnamigen Filter.
     *
     * @param ListFilter|array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return array<string, scalar|array<scalar|null>|null>
     */
    private function parentQuery(?int $parentId, ListFilter|array $filters): array
    {
        $query = self::filterQuery($filters);

        return null === $parentId ? $query : [$this->parentKey => $parentId] + $query;
    }

    /**
     * Bezeichnung für Exception-Messages, z. B. "invoice comment".
     */
    private function label(): string
    {
        return str_replace('-', ' ', $this->key);
    }
}
