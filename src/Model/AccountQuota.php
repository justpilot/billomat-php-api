<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Internal\ScalarCaster;

/**
 * Kontingent-Eintrag des Billomat-Accounts.
 *
 * Pro abgerechneter Entität (Dokumente, Kunden, Artikel, Speicherplatz)
 * liefert Billomat das verfügbare und das bereits verbrauchte Volumen.
 *
 * Sonderwert für unbegrenzte Kontingente: `available === -1`.
 *
 * Dokumentation: https://www.billomat.com/api/account/
 */
final readonly class AccountQuota
{
    public function __construct(
        public string $entity,
        public int $available,
        public int $used,
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            entity: ScalarCaster::toStringOrNull($data['entity'] ?? null) ?? '',
            available: ScalarCaster::toIntOrNull($data['available'] ?? null) ?? 0,
            used: ScalarCaster::toIntOrNull($data['used'] ?? null) ?? 0,
        );
    }

    public function isUnlimited(): bool
    {
        return -1 === $this->available;
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'entity' => $this->entity,
            'available' => $this->available,
            'used' => $this->used,
        ];
    }
}
