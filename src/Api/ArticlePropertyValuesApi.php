<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\ArticlePropertyValue;

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
