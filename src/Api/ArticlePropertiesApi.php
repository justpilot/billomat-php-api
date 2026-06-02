<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\ArticleProperty;

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
