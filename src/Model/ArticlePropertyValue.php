<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

/**
 * Wert einer Artikel-Eigenschaft.
 *
 * Doku: https://www.billomat.com/en/api/articles/properties/
 */
final readonly class ArticlePropertyValue
{
    public function __construct(
        public ?int $id,
        public int $articleId,
        public int $articlePropertyId,
        public ?string $type,
        public ?string $name,
        public mixed $value,
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
            articlePropertyId: (int) ($data['article_property_id'] ?? 0),
            type: $data['type'] ?? null,
            name: $data['name'] ?? null,
            value: $data['value'] ?? null,
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
            'article_property_id' => $this->articlePropertyId,
            'type' => $this->type,
            'name' => $this->name,
            'value' => $this->value,
        ];
    }
}
