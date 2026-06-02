<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

/**
 * Schlagwort/Tag an einem Artikel.
 */
final readonly class ArticleTag
{
    public function __construct(
        public ?int $id,
        public int $articleId,
        public string $name,
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            articleId: (int) ($data['article_id'] ?? 0),
            name: (string) ($data['name'] ?? ''),
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'article_id' => $this->articleId,
            'name' => $this->name,
        ];
    }
}
