<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

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
            name: $data['name'] ?? null,
            nameDe: $data['name_de'] ?? null,
            eu: isset($data['eu']) ? (bool) (int) $data['eu'] : null,
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
