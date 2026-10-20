<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Internal\ScalarCaster;

/**
 * Schlagwort/Tag an einem Artikel.
 */
final readonly class ArticleTag
{
    public function __construct(
        public ?int $id,
        public int $articleId,
        public string $name,
        public ?string $customfield = null,
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ScalarCaster::toIntOrNull($data['id'] ?? null),
            articleId: (int) ($data['article_id'] ?? 0),
            name: (string) ($data['name'] ?? ''),
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
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
            'customfield' => $this->customfield,
        ];
    }
}
