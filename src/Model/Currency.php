<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

/**
 * Währung aus der Billomat-Währungsliste.
 *
 * Doku: https://www.billomat.com/en/api/currencies/
 */
final readonly class Currency
{
    public function __construct(
        public string $code,
        public ?string $name = null,
        public ?string $symbol = null,
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            code: (string) ($data['code'] ?? $data['currency_code'] ?? ''),
            name: $data['name'] ?? null,
            symbol: $data['symbol'] ?? null,
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
            'symbol' => $this->symbol,
        ];
    }
}
