<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\ArticleTag;
use Justpilot\Billomat\Model\ArticleTagCloudEntry;

/**
 * API-Wrapper für Article-Tags.
 */
final class ArticleTagsApi extends AbstractApi
{
    /**
     * @return list<ArticleTag>
     */
    public function listByArticle(int $articleId): array
    {
        return $this->listResource('/article-tags', 'article-tags', 'article-tag', ArticleTag::fromArray(...), ['article_id' => $articleId]);
    }

    /**
     * @return list<ArticleTagCloudEntry>
     */
    public function cloud(): array
    {
        return $this->listResource('/article-tags', 'article-tags', 'tag', ArticleTagCloudEntry::fromArray(...));
    }

    public function get(int $id): ?ArticleTag
    {
        $data = $this->getJsonOrNull("/article-tags/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['article-tag'] ?? null;
        if (!\is_array($row)) {
            return null;
        }

        return ArticleTag::fromArray($row);
    }

    public function create(ArticleTagCreateOptions $options): ArticleTag
    {
        $payload = ['article-tag' => $options->toArray()];

        $data = $this->postJson('/article-tags', $payload);

        return ArticleTag::fromArray($this->unwrapEnvelope($data, 'article-tag', 'creating article tag'));
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/article-tags/{$id}");

        return true;
    }
}
