<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Typisierter Filter für GET /api/articles.
 *
 * Nicht gesetzte Felder (null bzw. leere Liste) fehlen im Query; Mehrfachwerte
 * werden kommasepariert gesendet und von Billomat ODER-verknüpft. Weitere
 * Query-Parameter (z. B. benutzerdefinierte Attribute) gehen über `extra`.
 *
 * Doku:
 * https://www.billomat.com/en/api/articles/
 *
 * Generiert aus tools/codegen/definitions/articles.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class ArticleFilter implements ListFilter
{
    public function __construct(
        /**
         * Artikelnummer.
         *
         * Billomat-Filter: article_number
         */
        public ?string $articleNumber = null,
        /**
         * Bezeichnung des Artikel.
         *
         * Billomat-Filter: title
         */
        public ?string $title = null,
        /**
         * Beschreibung des Artikel.
         *
         * Billomat-Filter: description
         */
        public ?string $description = null,
        /**
         * Währungscode nach ISO.
         *
         * Billomat-Filter: currency_code
         */
        public ?string $currencyCode = null,
        /**
         * ID der zugewiesenen Einheit.
         *
         * Billomat-Filter: unit_id
         */
        public ?int $unitId = null,
        /**
         * Kommaseparierte Liste der Schlagworte.
         *
         * Billomat-Filter: tags
         *
         * @var list<string>
         */
        public array $tags = [],
        /**
         * ID des zugewiesenen Lieferanten.
         *
         * Billomat-Filter: supplier_id
         */
        public ?int $supplierId = null,
        /** Sortierung, z. B. `OrderBy::desc('date')->thenAsc('name')`. */
        public ?OrderBy $orderBy = null,
        /**
         * Seite (ab 1); `iterateAll()` setzt sie selbst.
         *
         * Billomat-Filter: page
         */
        public ?int $page = null,
        /**
         * Einträge pro Seite (Billomat: Standard 100, maximal 1000).
         *
         * Billomat-Filter: per_page
         */
        public ?int $perPage = null,
        /**
         * Weitere Query-Parameter; überschreiben gleichnamige Felder.
         *
         * @var array<string, scalar|array<scalar>>
         */
        public array $extra = [],
    ) {
    }

    /** Kopie mit geändertem `articleNumber`. */
    public function withArticleNumber(?string $articleNumber): self
    {
        return clone($this, ['articleNumber' => $articleNumber]);
    }

    /** Kopie mit geändertem `title`. */
    public function withTitle(?string $title): self
    {
        return clone($this, ['title' => $title]);
    }

    /** Kopie mit geändertem `description`. */
    public function withDescription(?string $description): self
    {
        return clone($this, ['description' => $description]);
    }

    /** Kopie mit geändertem `currencyCode`. */
    public function withCurrencyCode(?string $currencyCode): self
    {
        return clone($this, ['currencyCode' => $currencyCode]);
    }

    /** Kopie mit geändertem `unitId`. */
    public function withUnitId(?int $unitId): self
    {
        return clone($this, ['unitId' => $unitId]);
    }

    /** Kopie mit geändertem `tags`. */
    public function withTags(string ...$tags): self
    {
        return clone($this, ['tags' => array_values($tags)]);
    }

    /** Kopie mit geändertem `supplierId`. */
    public function withSupplierId(?int $supplierId): self
    {
        return clone($this, ['supplierId' => $supplierId]);
    }

    /** Kopie mit geändertem `orderBy`. */
    public function withOrderBy(?OrderBy $orderBy): self
    {
        return clone($this, ['orderBy' => $orderBy]);
    }

    /** Kopie mit geändertem `page`. */
    public function withPage(?int $page): self
    {
        return clone($this, ['page' => $page]);
    }

    /** Kopie mit geändertem `perPage`. */
    public function withPerPage(?int $perPage): self
    {
        return clone($this, ['perPage' => $perPage]);
    }

    /**
     * Kopie mit weiteren Query-Parametern.
     *
     * @param array<string, scalar|array<scalar>> $extra
     */
    public function withExtra(array $extra): self
    {
        return clone($this, ['extra' => $extra]);
    }

    public function toQuery(): array
    {
        return [
            ...array_filter([
                'article_number' => $this->articleNumber,
                'title' => $this->title,
                'description' => $this->description,
                'currency_code' => $this->currencyCode,
                'unit_id' => $this->unitId,
                'tags' => [] === $this->tags ? null : implode(',', $this->tags),
                'supplier_id' => $this->supplierId,
                'order_by' => $this->orderBy?->toQuery(),
                'page' => $this->page,
                'per_page' => $this->perPage,
            ], static fn (mixed $v): bool => null !== $v),
            ...$this->extra,
        ];
    }
}
