<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Internal\ScalarCaster;

/**
 * Schlagwort (Tag) an einer Gutschrift.
 *
 * Dieses Model bildet die Struktur von GET /credit-note-tags?credit_note_id={id} bzw. GET /credit-note-tags/{id} ab.
 *
 * Dokumentation:
 * https://www.billomat.com/api/gutschriften/schlagworte/
 *
 * Generiert aus tools/codegen/definitions/credit-note-tags.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class CreditNoteTag
{
    /** Billomat-Felder, die das Model abbildet. */
    private const array FIELDS = [
        'id' => true,
        'credit_note_id' => true,
        'name' => true,
        'customfield' => true,
    ];

    /** @param array<string, mixed> $extra */
    public function __construct(
        /** Interne Billomat-ID des Schlagworts. */
        public int $id,
        /** ID des Datensatzes, an dem das Schlagwort hängt. */
        public int $creditNoteId = 0,
        /** Name des Schlagworts. */
        public string $name = '',
        /** Freies Zusatzfeld (siehe Custom-Meta-Attribute). */
        public ?string $customfield = null,
        /**
         * Felder, die Billomat liefert, das SDK aber (noch) nicht abbildet – roh, wie in der Response.
         *
         * Neue Billomat-Felder gehen so nicht verloren; toArray() gibt sie mit aus.
         */
        public array $extra = [],
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
            id: ScalarCaster::required(ScalarCaster::toIntOrNull($data['id'] ?? null), self::class, 'id'),
            creditNoteId: ScalarCaster::toIntOrNull($data['credit_note_id'] ?? null) ?? 0,
            name: ScalarCaster::toStringOrNull($data['name'] ?? null) ?? '',
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
            extra: array_diff_key($data, self::FIELDS),
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
            ...$this->extra,
            'id' => $this->id,
            'credit_note_id' => $this->creditNoteId,
            'name' => $this->name,
            'customfield' => $this->customfield,
        ];
    }
}
