<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Model\ArticleProperty;
use Justpilot\Billomat\Pagination\Page;

/**
 * API-Wrapper für Definitionen von Artikel-Eigenschaften.
 *
 * Doku: https://www.billomat.com/en/api/settings/article-properties/
 *
 * Endpoints:
 *  - GET    /article-properties
 *  - GET    /article-properties/{id}
 *  - POST   /article-properties
 *  - PUT    /article-properties/{id}
 *  - DELETE /article-properties/{id}
 */
final class ArticlePropertiesApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array|null> $filters
     *
     * @return list<ArticleProperty>
     */
    public function list(array $filters = []): array
    {
        return $this->listResource('/article-properties', 'article-properties', 'article-property', ArticleProperty::fromArray(...), $filters);
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
     * @return Page<ArticleProperty>
     */
    public function listPage(array $filters = []): Page
    {
        return $this->listResourcePage('/article-properties', 'article-properties', 'article-property', ArticleProperty::fromArray(...), $filters);
    }

    /**
     * Iteriert lazy durch alle ArticleProperty und yieldet sie einzeln.
     *
     * Holt seitenweise pro {@code $pageSize}-Items und stoppt automatisch,
     * sobald die letzte Seite erreicht ist (analog `auto_paging_iter()` im
     * Stripe-SDK). Filter werden bei jeder Page-Anfrage mitgesendet; `page`
     * und `per_page` darin werden überschrieben.
     *
     * @param array<string, scalar|array|null> $filters
     *
     * @return Generator<int, ArticleProperty>
     */
    public function iterateAll(array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/article-properties', 'article-properties', 'article-property', ArticleProperty::fromArray(...), $filters, $pageSize);
    }

    public function get(int $id): ?ArticleProperty
    {
        $data = $this->getJsonOrNull("/article-properties/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['article-property'] ?? null;
        if (!\is_array($row)) {
            return null;
        }

        return ArticleProperty::fromArray($row);
    }

    public function create(PropertyCreateOptions $options): ArticleProperty
    {
        $payload = ['article-property' => $options->toArray()];

        $data = $this->postJson('/article-properties', $payload);

        return ArticleProperty::fromArray($this->unwrapEnvelope($data, 'article-property', 'creating article property'));
    }

    public function update(int $id, PropertyCreateOptions $options): ArticleProperty
    {
        $payload = ['article-property' => $options->toArray()];

        $data = $this->putJson("/article-properties/{$id}", $payload);

        return ArticleProperty::fromArray($this->unwrapEnvelope($data, 'article-property', 'updating article property'));
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/article-properties/{$id}");

        return true;
    }
}
