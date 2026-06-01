<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use DateTimeInterface;
use Justpilot\Billomat\Model\Enum\TemplateDocumentType;
use Justpilot\Billomat\Model\Enum\TemplateFormat;
use Justpilot\Billomat\Model\Enum\TemplateType;
use Throwable;

/**
 * Repräsentiert eine Vorlage (Template) aus der Billomat API.
 *
 * Hinweis laut Doku:
 * - format und base64file kommen nur bei GET /templates/{id}
 * - und nur wenn template_type = UPLOADED
 */
final readonly class Template
{
    public function __construct(
        public ?int $id,
        public ?DateTimeImmutable $created = null,
        public ?TemplateDocumentType $type = null,
        public ?TemplateType $templateType = null,
        public ?string $name = null,
        /** Nur bei UPLOADED + single GET verfügbar */
        public ?TemplateFormat $format = null,
        /** Nur bei UPLOADED + single GET verfügbar */
        public ?string $base64file = null,
        public bool $isDefault = false
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $created = null;
        if (!empty($data['created'])) {
            try {
                $created = new DateTimeImmutable((string) $data['created']);
            } catch (Throwable) {
                $created = null;
            }
        }

        $isDefaultRaw = $data['is_default'] ?? 0;
        $isDefault = '1' === (string) $isDefaultRaw || 1 === $isDefaultRaw || true === $isDefaultRaw;

        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            created: $created,
            type: TemplateDocumentType::fromApi($data['type'] ?? null),
            templateType: TemplateType::fromApi($data['template_type'] ?? null),
            name: $data['name'] ?? null,
            format: TemplateFormat::fromApi($data['format'] ?? null),
            base64file: $data['base64file'] ?? null,
            isDefault: $isDefault,
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'created' => $this->created?->format(DateTimeInterface::ATOM),
            'type' => $this->type?->value,
            'template_type' => $this->templateType?->value,
            'name' => $this->name,
            'format' => $this->format?->value,
            'base64file' => $this->base64file,
            'is_default' => $this->isDefault ? 1 : 0,
        ], static fn (int|string|null $v): bool => null !== $v);
    }
}
