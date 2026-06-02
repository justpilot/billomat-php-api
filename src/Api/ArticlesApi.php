<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Model\Article;

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
     * @param array<string, scalar|array|null> $filters
     *
     * @return list<Article>
     */
    public function list(array $filters = []): array
    {
        return $this->listResource('/articles', 'articles', 'article', Article::fromArray(...), $filters);
    }

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

    public function delete(int $id): bool
    {
        $this->deleteVoid("/articles/{$id}");

        return true;
    }
}
