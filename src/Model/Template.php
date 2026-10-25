<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\TemplateDocumentType;
use Justpilot\Billomat\Model\Enum\TemplateFormat;
use Justpilot\Billomat\Model\Enum\TemplateType;

use const DATE_ATOM;

/**
 * Repräsentiert eine Vorlage (Template) aus der Billomat-API.
 *
 * Dieses Model bildet die Struktur von GET /templates bzw. GET /templates/{id} ab.
 *
 * Hinweis laut Doku: `format` und `base64file` kommen nur bei GET /templates/{id}
 * und nur, wenn `template_type` = UPLOADED ist.
 *
 * Dokumentation:
 * https://www.billomat.com/api/einstellungen/vorlagen/
 *
 * Generiert aus tools/codegen/definitions/templates.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class Template
{
    /** Billomat-Felder, die das Model abbildet. */
    private const array FIELDS = [
        'id' => true,
        'created' => true,
        'type' => true,
        'template_type' => true,
        'name' => true,
        'format' => true,
        'base64file' => true,
        'is_default' => true,
        'updated' => true,
        'customfield' => true,
        'is_background_available' => true,
    ];

    /** @param array<string, mixed> $extra */
    public function __construct(
        /** Interne Billomat-ID der Vorlage. */
        public int $id,
        /** Erstellungszeitpunkt der Vorlage. */
        public ?DateTimeImmutable $created = null,
        /** Dokumenttyp, für den die Vorlage gilt. */
        public ?TemplateDocumentType $type = null,
        /**
         * Art der Vorlage.
         *
         * Billomat vergibt UPLOADED, wenn eine Datei hochgeladen wurde, sonst DEFINED.
         */
        public ?TemplateType $templateType = null,
        /** Bezeichnung der Vorlage. */
        public ?string $name = null,
        /**
         * Dateiformat der hochgeladenen Vorlage.
         *
         * Im Read-Model nur bei UPLOADED und GET /templates/{id} verfügbar.
         */
        public ?TemplateFormat $format = null,
        /**
         * Base64-kodierte Vorlagendatei.
         *
         * Im Read-Model nur bei UPLOADED und GET /templates/{id} verfügbar.
         */
        public ?string $base64file = null,
        /** Standard-Vorlage für den Dokumenttyp? */
        public bool $isDefault = false,
        /** Zeitpunkt der letzten Änderung. */
        public ?DateTimeImmutable $updated = null,
        /** Freies Zusatzfeld (siehe Custom-Meta-Attribute). */
        public ?string $customfield = null,
        /** Ist ein Hintergrundbild für die Vorlage hinterlegt? */
        public ?bool $isBackgroundAvailable = null,
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
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            type: TemplateDocumentType::tryFrom(ScalarCaster::toStringOrNull($data['type'] ?? null) ?? ''),
            templateType: TemplateType::tryFrom(ScalarCaster::toStringOrNull($data['template_type'] ?? null) ?? ''),
            name: ScalarCaster::toStringOrNull($data['name'] ?? null),
            format: TemplateFormat::tryFrom(ScalarCaster::toStringOrNull($data['format'] ?? null) ?? ''),
            base64file: ScalarCaster::toStringOrNull($data['base64file'] ?? null),
            isDefault: ScalarCaster::toBoolOrNull($data['is_default'] ?? null) ?? false,
            updated: ScalarCaster::toDateTimeOrNull($data['updated'] ?? null),
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
            isBackgroundAvailable: ScalarCaster::toBoolOrNull($data['is_background_available'] ?? null),
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
        return array_filter([
            ...$this->extra,
            'id' => $this->id,
            'created' => $this->created?->format(DATE_ATOM),
            'type' => $this->type?->value,
            'template_type' => $this->templateType?->value,
            'name' => $this->name,
            'format' => $this->format?->value,
            'base64file' => $this->base64file,
            'is_default' => ($this->isDefault ? 1 : 0),
            'updated' => $this->updated?->format(DATE_ATOM),
            'customfield' => $this->customfield,
            'is_background_available' => null === $this->isBackgroundAvailable ? null : ($this->isBackgroundAvailable ? 1 : 0),
        ], static fn (mixed $v): bool => null !== $v);
    }
}
