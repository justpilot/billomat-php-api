<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Internal\ScalarCaster;

/**
 * Schlagwort (Tag) an einem Artikel.
 *
 * Dieses Model bildet die Struktur von GET /article-tags?article_id={id} bzw. GET /article-tags/{id} ab.
 *
 * Dokumentation:
 * https://www.billomat.com/api/artikel/schlagworte/
 *
 * Generiert aus tools/codegen/definitions/article-tags.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class ArticleTag
{
    public function __construct(
        /** Interne Billomat-ID des Schlagworts. */
        public ?int $id,
        /** ID des Datensatzes, an dem das Schlagwort hängt. */
        public int $articleId,
        /** Name des Schlagworts. */
        public string $name,
        /** Freies Zusatzfeld (siehe Custom-Meta-Attribute). */
        public ?string $customfield = null,
    ) {
    }

    /**
     * Hydriert das Model aus einem Billomat-Response-Array.
     *
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ScalarCaster::toIntOrNull($data['id'] ?? null),
            articleId: ScalarCaster::toIntOrNull($data['article_id'] ?? null) ?? 0,
            name: ScalarCaster::toStringOrNull($data['name'] ?? null) ?? '',
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
        );
    }

    /**
     * Exportiert das Model als Array mit Billomat-Feldnamen.
     *
     * Praktisch für Debugging oder Log-Ausgaben.
     *
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
