<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Internal\ScalarCaster;

/**
 * Schlagwort/Tag an einer Auftragsbestätigung.
 *
 * Doku: https://www.billomat.com/en/api/confirmations/tags/
 */
final readonly class ConfirmationTag
{
    public function __construct(
        public ?int $id,
        public int $confirmationId,
        public string $name,
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ScalarCaster::toIntOrNull($data['id'] ?? null),
            confirmationId: (int) ($data['confirmation_id'] ?? 0),
            name: (string) ($data['name'] ?? ''),
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'confirmation_id' => $this->confirmationId,
            'name' => $this->name,
        ];
    }
}
