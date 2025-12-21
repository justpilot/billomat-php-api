<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Payload für PUT /api/templates/{id}
 *
 * Laut Doku z.B. name editierbar; is_default ist sinnvoll als Option.
 */
final class TemplateUpdateOptions
{
    public ?string $name = null;

    /** Default-Template? */
    public ?bool $isDefault = null;

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [
            'name' => $this->name,
            'is_default' => $this->isDefault === null ? null : ($this->isDefault ? 1 : 0),
        ];

        return array_filter($data, static fn($v) => $v !== null);
    }
}