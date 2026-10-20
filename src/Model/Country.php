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
        /**
         * @deprecated since 3.1 – Billomat liefert `name_de` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.
         */
        public ?string $nameDe = null,
        /**
         * @deprecated since 3.1 – Billomat liefert `eu` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.
         */
        public ?bool $eu = null,
        public ?int $id = null,
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
            id: ScalarCaster::toIntOrNull($data['id'] ?? null),
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
            'id' => $this->id,
        ];
    }
}
