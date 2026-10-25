<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Typisierter Payload für POST /article-property-values gemäß Billomat-API.
 *
 * Doku:
 * https://www.billomat.com/api/artikel/attribute/
 *
 * Generiert aus tools/codegen/definitions/article-property-values.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final class ArticlePropertyValueCreateOptions implements WriteOptions
{
    public function __construct(
        /**
         * ID des Datensatzes, zu dem der Wert gehört.
         *
         * Billomat-Feld: article_id
         * Typ: INT
         */
        public int $articleId,
        /**
         * ID der Eigenschaft.
         *
         * Billomat-Feld: article_property_id
         * Typ: INT
         */
        public int $articlePropertyId,
        /**
         * Wert der Eigenschaft.
         *
         * Billomat-Feld: value
         * Typ: ALNUM
         */
        public mixed $value,
    ) {
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
            'article_property_id' => $this->articlePropertyId,
            'value' => $this->value,
        ];
    }
}
