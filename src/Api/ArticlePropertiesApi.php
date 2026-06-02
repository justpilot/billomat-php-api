<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\ArticleProperty;
use RuntimeException;

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
        $data = $this->getJson('/article-properties', $filters);

        $node = $data['article-properties']['article-property'] ?? [];

        if (null === $node || [] === $node) {
            return [];
        }

        if (\is_array($node) && array_is_list($node)) {
            $rows = $node;
        } elseif (\is_array($node)) {
            $rows = [$node];
        } else {
            $rows = [];
        }

        /** @var list<ArticleProperty> $models */
        $models = array_map(ArticleProperty::fromArray(...), $rows);

        return $models;
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

        $row = $data['article-property'] ?? null;
        if (!\is_array($row)) {
            throw new RuntimeException('Unexpected response from Billomat when creating article property.');
        }

        return ArticleProperty::fromArray($row);
    }

    public function update(int $id, PropertyCreateOptions $options): ArticleProperty
    {
        $payload = ['article-property' => $options->toArray()];

        $data = $this->putJson("/article-properties/{$id}", $payload);

        $row = $data['article-property'] ?? null;
        if (!\is_array($row)) {
            throw new RuntimeException('Unexpected response from Billomat when updating article property.');
        }

        return ArticleProperty::fromArray($row);
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/article-properties/{$id}");

        return true;
    }
}
