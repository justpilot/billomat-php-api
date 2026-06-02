<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Internal\ScalarCaster;

/**
 * Land aus der Billomat-Länderliste.
 *
 * Doku: https://www.billomat.com/en/api/countries/
 */
final readonly class Country
{
    public function __construct(
        public string $code,
        public ?string $name = null,
        public ?string $nameDe = null,
        public ?bool $eu = null,
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            code: (string) ($data['code'] ?? $data['country_code'] ?? ''),
            name: ScalarCaster::toStringOrNull($data['name'] ?? null),
            nameDe: ScalarCaster::toStringOrNull($data['name_de'] ?? null),
            eu: ScalarCaster::toBoolOrNull($data['eu'] ?? null),
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'name_de' => $this->nameDe,
            'eu' => $this->eu,
        ];
    }
}
