<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Model\ArticlePropertyValue;
use Justpilot\Billomat\Pagination\Page;

/**
 * API-Wrapper für Werte von Artikel-Eigenschaften (Article Property Values).
 *
 * Endpoints:
 *  - GET    /article-property-values?article_id={id}
 *  - GET    /article-property-values/{id}
 *  - POST   /article-property-values
 *
 * Doku: https://www.billomat.com/en/api/articles/properties/
 */
final class ArticlePropertyValuesApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array|null> $filters
     *
     * @return list<ArticlePropertyValue>
     */
    public function list(array $filters = []): array
    {
        return $this->listResource('/article-property-values', 'article-property-values', 'article-property-value', ArticlePropertyValue::fromArray(...), $filters);
    }

    /**
     * Liefert eine einzelne Seite samt Pagination-Metadaten.
     *
     * Identisch zu {@see list()}, gibt aber zusätzlich `@page`/`@per_page`/
     * `@total` aus dem Response-Envelope als {@see PageInfo} zurück. Nützlich
     * für UI mit klassischer "Seite 1/12, 234 Treffer"-Anzeige.
     *
     * @param array<string, scalar|array|null> $filters
     *
     * @return Page<ArticlePropertyValue>
     */
    public function listPage(array $filters = []): Page
    {
        return $this->listResourcePage('/article-property-values', 'article-property-values', 'article-property-value', ArticlePropertyValue::fromArray(...), $filters);
    }

    /**
     * Iteriert lazy durch alle ArticlePropertyValue und yieldet sie einzeln.
     *
     * Holt seitenweise pro {@code $pageSize}-Items und stoppt automatisch,
     * sobald die letzte Seite erreicht ist (analog `auto_paging_iter()` im
     * Stripe-SDK). Filter werden bei jeder Page-Anfrage mitgesendet; `page`
     * und `per_page` darin werden überschrieben.
     *
     * @param array<string, scalar|array|null> $filters
     *
     * @return Generator<int, ArticlePropertyValue>
     */
    public function iterateAll(array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/article-property-values', 'article-property-values', 'article-property-value', ArticlePropertyValue::fromArray(...), $filters, $pageSize);
    }

    public function get(int $id): ?ArticlePropertyValue
    {
        $data = $this->getJsonOrNull("/article-property-values/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['article-property-value'] ?? null;
        if (!\is_array($row)) {
            return null;
        }

        return ArticlePropertyValue::fromArray($row);
    }

    public function create(ArticlePropertyValueCreateOptions $options): ArticlePropertyValue
    {
        $payload = ['article-property-value' => $options->toArray()];

        $data = $this->postJson('/article-property-values', $payload);

        return ArticlePropertyValue::fromArray($this->unwrapEnvelope($data, 'article-property-value', 'creating article property value'));
    }
}
