<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Internal\ScalarCaster;

/**
 * Steuerfreies Land (`/country-taxes`) — Ländercode nach ISO 3166 Alpha-2,
 * für das Billomat keine Mehrwertsteuer berechnet.
 *
 * Quelle: https://www.billomat.com/api/einstellungen/steuerfreie-laender/
 */
final readonly class CountryTax
{
    public function __construct(
        public ?int $id,
        public ?string $countryCode = null,
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ScalarCaster::toIntOrNull($data['id'] ?? null),
            countryCode: ScalarCaster::toStringOrNull($data['country_code'] ?? null),
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'country_code' => $this->countryCode,
        ];
    }
}
