<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\ArticlePropertyValue;
use RuntimeException;

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
        $data = $this->getJson('/article-property-values', $filters);

        $root = $data['article-property-values'] ?? null;
        if (!\is_array($root)) {
            return [];
        }

        $rows = $root['article-property-value'] ?? [];

        if ([] === $rows || null === $rows) {
            return [];
        }

        if (isset($rows['id'])) {
            $rows = [$rows];
        }

        if (!\is_array($rows)) {
            return [];
        }

        /** @var list<ArticlePropertyValue> $values */
        $values = array_map(
            ArticlePropertyValue::fromArray(...),
            $rows,
        );

        return $values;
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

        $row = $data['article-property-value'] ?? null;
        if (!\is_array($row)) {
            throw new RuntimeException('Unexpected response from Billomat when creating article property value.');
        }

        return ArticlePropertyValue::fromArray($row);
    }
}
