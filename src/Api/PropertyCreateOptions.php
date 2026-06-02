<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\PropertyType;

/**
 * Geteilte Options-Klasse für POST der Property-Definitionen
 * (article-properties, client-properties, supplier-properties, incoming-properties).
 *
 * Doku: https://www.billomat.com/en/api/settings/client-properties/
 */
final class PropertyCreateOptions
{
    public ?PropertyType $type = null;

    public ?string $defaultValue = null;

    public ?int $position = null;

    public function __construct(
        public string $name,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [
            'name' => $this->name,
            'type' => $this->type?->value,
            'default_value' => $this->defaultValue,
            'position' => $this->position,
        ];

        return array_filter($data, static fn (int|string|null $v): bool => null !== $v);
    }
}
