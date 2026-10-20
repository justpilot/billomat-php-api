<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;

use const DATE_ATOM;

/**
 * Wert einer Kunden-Eigenschaft.
 *
 * Doku: https://www.billomat.com/api/kunden/attribute/
 */
final readonly class ClientPropertyValue
{
    public function __construct(
        public ?int $id,
        public int $clientId,
        public int $clientPropertyId,
        public ?string $type,
        public ?string $name,
        public mixed $value,
        public ?DateTimeImmutable $created = null,
        public ?DateTimeImmutable $updated = null,
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
            clientId: ScalarCaster::toIntOrNull($data['client_id'] ?? null) ?? 0,
            clientPropertyId: ScalarCaster::toIntOrNull($data['client_property_id'] ?? null) ?? 0,
            type: ScalarCaster::toStringOrNull($data['type'] ?? null),
            name: ScalarCaster::toStringOrNull($data['name'] ?? null),
            value: $data['value'] ?? null,
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            updated: ScalarCaster::toDateTimeOrNull($data['updated'] ?? null),
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
            'client_id' => $this->clientId,
            'client_property_id' => $this->clientPropertyId,
            'type' => $this->type,
            'name' => $this->name,
            'value' => $this->value,
            'created' => $this->created?->format(DATE_ATOM),
            'updated' => $this->updated?->format(DATE_ATOM),
            'customfield' => $this->customfield,
        ];
    }
}
