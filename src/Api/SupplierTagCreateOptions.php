<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Payload für POST /supplier-tags.
 */
final class SupplierTagCreateOptions
{
    public function __construct(
        public int $supplierId,
        public string $name,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'supplier_id' => $this->supplierId,
            'name' => $this->name,
        ];
    }
}
