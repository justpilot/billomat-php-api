<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Internal\ScalarCaster;

/**
 * Währung aus der Billomat-Währungsliste.
 *
 * Dokumentation:
 * https://www.billomat.com/en/api/currencies/
 *
 * Generiert aus tools/codegen/definitions/currencies.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class Currency
{
    /** Billomat-Felder, die das Model abbildet. */
    private const array FIELDS = [
        'code' => true,
        'currency_code' => true,
        'name' => true,
        'symbol' => true,
        'id' => true,
    ];

    /** @param array<string, mixed> $extra */
    public function __construct(
        /** ISO-Währungscode (z. B. "EUR"). */
        public string $code,
        /** Name der Währung. */
        public ?string $name = null,
        /** Währungssymbol (z. B. "€"). */
        public ?string $symbol = null,
        /** Interne Billomat-ID der Währung. */
        public ?int $id = null,
        /**
         * Felder, die Billomat liefert, das SDK aber (noch) nicht abbildet – roh, wie in der Response.
         *
         * Neue Billomat-Felder gehen so nicht verloren; toArray() gibt sie mit aus.
         */
        public array $extra = [],
    ) {
    }

    /**
     * Hydriert das Model aus einem Billomat-Response-Array.
     *
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            code: ScalarCaster::toStringOrNull($data['code'] ?? $data['currency_code'] ?? null) ?? '',
            name: ScalarCaster::toStringOrNull($data['name'] ?? null),
            symbol: ScalarCaster::toStringOrNull($data['symbol'] ?? null),
            id: ScalarCaster::toIntOrNull($data['id'] ?? null),
            extra: array_diff_key($data, self::FIELDS),
        );
    }

    /**
     * Exportiert das Model als Array mit Billomat-Feldnamen.
     *
     * Praktisch für Debugging oder Log-Ausgaben.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            ...$this->extra,
            'code' => $this->code,
            'name' => $this->name,
            'symbol' => $this->symbol,
            'id' => $this->id,
        ];
    }
}
