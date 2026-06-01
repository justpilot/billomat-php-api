<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\TemplateDocumentType;
use Justpilot\Billomat\Model\Enum\TemplateFormat;

/**
 * Payload für POST /api/templates.
 *
 * Billomat:
 * - type ist Pflicht
 * - format + base64file optional (wenn gesetzt => template_type wird UPLOADED, sonst DEFINED)
 * - is_default optional (1/0)
 */
final class TemplateCreateOptions
{
    public ?string $name = null;

    public ?TemplateFormat $format = null;

    /** Base64-kodierte Template-Datei (nur bei UPLOADED) */
    public ?string $base64file = null;

    /** Default-Template? */
    public ?bool $isDefault = null;

    public function __construct(public TemplateDocumentType $type)
    {
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [
            'type' => $this->type->value,
            'name' => $this->name,
            'format' => $this->format?->value,
            'base64file' => $this->base64file,
            'is_default' => null === $this->isDefault ? null : ($this->isDefault ? 1 : 0),
        ];

        return array_filter($data, static fn (string|int|null $v): bool => null !== $v);
    }
}
