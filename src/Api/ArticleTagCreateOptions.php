<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Typisierter Payload für POST /article-tags gemäß Billomat-API.
 *
 * Doku:
 * https://www.billomat.com/api/artikel/schlagworte/
 *
 * Generiert aus tools/codegen/definitions/article-tags.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class ArticleTagCreateOptions implements WriteOptions
{
    public function __construct(
        /**
         * ID des Datensatzes, an dem das Schlagwort hängt.
         *
         * Billomat-Feld: article_id
         * Typ: INT
         */
        public int $articleId,
        /**
         * Name des Schlagworts.
         *
         * Billomat-Feld: name
         * Typ: ALNUM
         */
        public string $name,
    ) {
    }

    /** Kopie mit geändertem `articleId`. */
    public function withArticleId(int $articleId): self
    {
        return clone($this, ['articleId' => $articleId]);
    }

    /** Kopie mit geändertem `name`. */
    public function withName(string $name): self
    {
        return clone($this, ['name' => $name]);
    }

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
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
