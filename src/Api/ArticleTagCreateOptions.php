<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Payload für POST /article-tags.
 */
final class ArticleTagCreateOptions
{
    public function __construct(
        public int $articleId,
        public string $name,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'article_id' => $this->articleId,
            'name' => $this->name,
        ];
    }
}
