<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;

use const DATE_ATOM;

/**
 * Freitext-Baustein (verwendbar als title/label/intro/note auf Dokumenten).
 *
 * Dieses Model bildet die Struktur von GET /free-texts bzw. GET /free-texts/{id} ab.
 *
 * Dokumentation:
 * https://www.billomat.com/en/api/settings/free-texts/
 *
 * Generiert aus tools/codegen/definitions/free-texts.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class FreeText
{
    public function __construct(
        /** Interne Billomat-ID des Freitexts. */
        public ?int $id,
        /** Überschrift bzw. Betreff. */
        public ?string $title,
        /** Bezeichnung. */
        public ?string $label = null,
        /** Einleitungstext bzw. Brieftext. */
        public ?string $intro = null,
        /** Anmerkungen. */
        public ?string $note = null,
        /** Erstellungszeitpunkt des Freitexts. */
        public ?DateTimeImmutable $created = null,
        /** Zeitpunkt der letzten Änderung. */
        public ?DateTimeImmutable $updated = null,
        /**
         * Dokumenttyp, für den der Freitext gilt.
         *
         * Mögliche Werte: INVOICE, CORRECTION, OFFER, CONFIRMATION, CREDIT_NOTE, DELIVERY_NOTE, LETTER
         */
        public ?string $type = null,
        /** Name der Textvorlage. */
        public ?string $name = null,
        /** Standard-Freitext für den Dokumenttyp? */
        public ?bool $isDefault = null,
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
            title: ScalarCaster::toStringOrNull($data['title'] ?? null),
            label: ScalarCaster::toStringOrNull($data['label'] ?? null),
            intro: ScalarCaster::toStringOrNull($data['intro'] ?? null),
            note: ScalarCaster::toStringOrNull($data['note'] ?? null),
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            updated: ScalarCaster::toDateTimeOrNull($data['updated'] ?? null),
            type: ScalarCaster::toStringOrNull($data['type'] ?? null),
            name: ScalarCaster::toStringOrNull($data['name'] ?? null),
            isDefault: ScalarCaster::toBoolOrNull($data['is_default'] ?? null),
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
            'title' => $this->title,
            'label' => $this->label,
            'intro' => $this->intro,
            'note' => $this->note,
            'created' => $this->created?->format(DATE_ATOM),
            'updated' => $this->updated?->format(DATE_ATOM),
            'type' => $this->type,
            'name' => $this->name,
            'is_default' => $this->isDefault,
        ];
    }
}
