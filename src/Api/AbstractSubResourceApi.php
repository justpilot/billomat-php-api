<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Closure;
use InvalidArgumentException;
use Justpilot\Billomat\Http\BillomatHttpClientInterface;
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
        $data = $this->getJsonOrNull("{$this->path}/{$id}");

        if (null === $data) {
            return null;
        }

        return ($this->hydrate)($this->unwrapEnvelope($data, $this->key, 'fetching '.$this->label()));
    }

    /**
     * @param array<string, scalar|array<mixed>|null> $filters
     *
     * @return list<TModel>
     */
    protected function listWhere(array $filters): array
    {
        return $this->listResource($this->path, $this->listKey, $this->key, $this->hydrate, $filters);
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

    protected function deleteById(int $id): void
    {
        $this->deleteVoid("{$this->path}/{$id}");
    }

    /**
     * Bezeichnung für Exception-Messages, z. B. "invoice comment".
     */
    private function label(): string
    {
        return str_replace('-', ' ', $this->key);
    }
}
