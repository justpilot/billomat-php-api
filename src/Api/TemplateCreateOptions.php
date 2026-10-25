<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\TemplateDocumentType;
use Justpilot\Billomat\Model\Enum\TemplateFormat;

/**
 * Typisierter Payload für POST /templates gemäß Billomat-API.
 *
 * Werden `format` und `base64file` gesetzt, vergibt Billomat den template_type
 * UPLOADED, sonst DEFINED. Null-Werte werden beim Serialisieren ignoriert.
 *
 * Doku:
 * https://www.billomat.com/api/einstellungen/vorlagen/
 *
 * Generiert aus tools/codegen/definitions/templates.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class TemplateCreateOptions implements WriteOptions
{
    public function __construct(
        /**
         * Dokumenttyp, für den die Vorlage gilt.
         *
         * Billomat-Feld: type
         * Typ: ENUM
         */
        public TemplateDocumentType $type,
        /**
         * Bezeichnung der Vorlage.
         *
         * Billomat-Feld: name
         * Typ: ALNUM
         */
        public ?string $name = null,
        /**
         * Dateiformat der hochgeladenen Vorlage.
         *
         * Im Read-Model nur bei UPLOADED und GET /templates/{id} verfügbar.
         *
         * Billomat-Feld: format
         * Typ: ENUM
         */
        public ?TemplateFormat $format = null,
        /**
         * Base64-kodierte Vorlagendatei.
         *
         * Im Read-Model nur bei UPLOADED und GET /templates/{id} verfügbar.
         *
         * Billomat-Feld: base64file
         * Typ: ENUM
         */
        public ?string $base64file = null,
        /**
         * Standard-Vorlage für den Dokumenttyp?
         *
         * Billomat-Feld: is_default
         * Typ: BOOL
         * Default: 0
         */
        public ?bool $isDefault = null,
    ) {
    }

    /** Kopie mit geändertem `type`. */
    public function withType(TemplateDocumentType $type): self
    {
        return clone($this, ['type' => $type]);
    }

    /** Kopie mit geändertem `name`. */
    public function withName(?string $name): self
    {
        return clone($this, ['name' => $name]);
    }

    /** Kopie mit geändertem `format`. */
    public function withFormat(?TemplateFormat $format): self
    {
        return clone($this, ['format' => $format]);
    }

    /** Kopie mit geändertem `base64file`. */
    public function withBase64file(?string $base64file): self
    {
        return clone($this, ['base64file' => $base64file]);
    }

    /** Kopie mit geändertem `isDefault`. */
    public function withIsDefault(?bool $isDefault): self
    {
        return clone($this, ['isDefault' => $isDefault]);
    }

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'type' => $this->type->value,
            'name' => $this->name,
            'format' => $this->format?->value,
            'base64file' => $this->base64file,
            'is_default' => null === $this->isDefault ? null : ($this->isDefault ? 1 : 0),
        ], static fn (mixed $v): bool => null !== $v);
    }
}
