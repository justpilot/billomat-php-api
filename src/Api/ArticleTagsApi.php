<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\ArticleTag;
use Justpilot\Billomat\Model\ArticleTagCloudEntry;
use RuntimeException;

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
        $data = $this->getJson('/article-tags', ['article_id' => $articleId]);

        $root = $data['article-tags'] ?? null;
        if (!\is_array($root)) {
            return [];
        }

        $rows = $root['article-tag'] ?? [];
        if ([] === $rows || null === $rows) {
            return [];
        }

        if (isset($rows['id'])) {
            $rows = [$rows];
        }

        if (!\is_array($rows)) {
            return [];
        }

        /** @var list<ArticleTag> $tags */
        $tags = array_map(ArticleTag::fromArray(...), $rows);

        return $tags;
    }

    /**
     * @return list<ArticleTagCloudEntry>
     */
    public function cloud(): array
    {
        $data = $this->getJson('/article-tags');

        $root = $data['article-tags'] ?? null;
        if (!\is_array($root)) {
            return [];
        }

        $rows = $root['tag'] ?? [];
        if ([] === $rows || null === $rows) {
            return [];
        }

        if (isset($rows['name'])) {
            $rows = [$rows];
        }

        if (!\is_array($rows)) {
            return [];
        }

        /** @var list<ArticleTagCloudEntry> $tags */
        $tags = array_map(ArticleTagCloudEntry::fromArray(...), $rows);

        return $tags;
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

        $row = $data['article-tag'] ?? null;
        if (!\is_array($row)) {
            throw new RuntimeException('Unexpected response from Billomat when creating article tag.');
        }

        return ArticleTag::fromArray($row);
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/article-tags/{$id}");

        return true;
    }
}
