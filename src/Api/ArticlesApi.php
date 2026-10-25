<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Model\Article;
use Justpilot\Billomat\Model\ArticleProperty;
use Justpilot\Billomat\Model\ArticlePropertyValue;
use Justpilot\Billomat\Model\ArticleTag;
use Justpilot\Billomat\Model\ArticleTagCloudEntry;
use Justpilot\Billomat\Pagination\Page;
use NoDiscard;

/**
 * API-Wrapper für die Billomat-Articles-Ressource (Artikel).
 *
 * Doku: https://www.billomat.com/en/api/articles/
 *
 * Endpoints:
 *  - GET    /articles
 *  - GET    /articles/{id}
 *  - POST   /articles
 *  - PUT    /articles/{id}
 *  - DELETE /articles/{id}
 */
final class ArticlesApi extends AbstractApi
{
    /**
     * Schlagworte, siehe {@see TagsApi}.
     *
     * @var TagsApi<ArticleTag, ArticleTagCloudEntry, ArticleTagCreateOptions>
     */
    public private(set) TagsApi $tags {
        get => $this->tags ??= new TagsApi($this->http, 'article', ArticleTag::fromArray(...), ArticleTagCloudEntry::fromArray(...), ArticleTagCreateOptions::class);
    }

    /**
     * Attribut-Definitionen, siehe {@see PropertiesApi}.
     *
     * @var PropertiesApi<ArticleProperty>
     */
    public private(set) PropertiesApi $properties {
        get => $this->properties ??= new PropertiesApi($this->http, 'article', ArticleProperty::fromArray(...));
    }

    /**
     * Attributwerte, siehe {@see PropertyValuesApi}.
     *
     * @var PropertyValuesApi<ArticlePropertyValue, ArticlePropertyValueCreateOptions>
     */
    public private(set) PropertyValuesApi $propertyValues {
        get => $this->propertyValues ??= new PropertyValuesApi($this->http, 'article', ArticlePropertyValue::fromArray(...), ArticlePropertyValueCreateOptions::class);
    }

    /**
     * @param array<string, scalar|array|null> $filters
     *
     * @return list<Article>
     */
    #[NoDiscard]
    public function list(array $filters = []): array
    {
        return $this->listResource('/articles', 'articles', 'article', Article::fromArray(...), $filters);
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
     * @return Page<Article>
     */
    #[NoDiscard]
    public function listPage(array $filters = []): Page
    {
        return $this->listResourcePage('/articles', 'articles', 'article', Article::fromArray(...), $filters);
    }

    /**
     * Iteriert lazy durch alle Artikel und yieldet sie einzeln.
     *
     * Holt seitenweise pro {@code $pageSize}-Items und stoppt automatisch,
     * sobald die letzte Seite erreicht ist (analog `auto_paging_iter()` im
     * Stripe-SDK). Filter werden bei jeder Page-Anfrage mitgesendet; `page`
     * und `per_page` darin werden überschrieben.
     *
     * @param array<string, scalar|array|null> $filters
     *
     * @return Generator<int, Article>
     */
    #[NoDiscard]
    public function iterateAll(array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/articles', 'articles', 'article', Article::fromArray(...), $filters, $pageSize);
    }

    #[NoDiscard]
    public function get(int $id): ?Article
    {
        $data = $this->getJsonOrNull("/articles/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['article'] ?? null;

        if (!\is_array($row)) {
            return null;
        }

        return Article::fromArray($row);
    }

    /**
     * @throws ValidationException
     * @throws AuthenticationException
     * @throws HttpException
     */
    public function create(ArticleCreateOptions $options): Article
    {
        $payload = ['article' => $options->toArray()];

        $data = $this->postJson('/articles', $payload);

        return Article::fromArray($this->unwrapEnvelope($data, 'article', 'creating article'));
    }

    public function update(int $id, ArticleUpdateOptions $options): Article
    {
        $payload = ['article' => $options->toArray()];

        $data = $this->putJson("/articles/{$id}", $payload);

        return Article::fromArray($this->unwrapEnvelope($data, 'article', 'updating article'));
    }

    public function delete(int $id): void
    {
        $this->deleteVoid("/articles/{$id}");
    }
}
