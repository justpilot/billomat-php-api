<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Payload für POST /article-property-values.
 *
 * Doku: https://www.billomat.com/en/api/articles/properties/
 */
final class ArticlePropertyValueCreateOptions
{
    public function __construct(
        public int $articleId,
        public int $articlePropertyId,
        public mixed $value,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'article_id' => $this->articleId,
            'article_property_id' => $this->articlePropertyId,
            'value' => $this->value,
        ];
    }
}
