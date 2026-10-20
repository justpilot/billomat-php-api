<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Internal\ScalarCaster;

/**
 * Schlagwort/Tag an einem Angebot.
 *
 * Doku: https://www.billomat.com/en/api/estimates/tags/
 */
final readonly class OfferTag
{
    public function __construct(
        public ?int $id,
        public int $offerId,
        public string $name,
        public ?string $customfield = null,
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ScalarCaster::toIntOrNull($data['id'] ?? null),
            offerId: (int) ($data['offer_id'] ?? 0),
            name: (string) ($data['name'] ?? ''),
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'offer_id' => $this->offerId,
            'name' => $this->name,
            'customfield' => $this->customfield,
        ];
    }
}
