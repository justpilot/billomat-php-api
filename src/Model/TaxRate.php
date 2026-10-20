<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;

use const DATE_ATOM;

/**
 * Repräsentiert einen Steuersatz aus der Billomat-API.
 *
 * Dokumentation:
 * https://www.billomat.com/api/einstellungen/steuersaetze/
 */
final readonly class TaxRate
{
    public function __construct(
        /** Interne Billomat-ID des Steuersatzes. */
        public ?int $id,
        /**
         * ID des Accounts, zu dem der Steuersatz gehört.
         *
         * @deprecated since 3.1 – Billomat liefert `account_id` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.
         */
        public ?int $accountId,
        /** Name des Steuersatzes, z. B. "MwSt". */
        public string $name,
        /** Höhe des Steuersatzes in Prozent, z. B. 19.0. */
        public float $rate,
        /** Gibt an, ob es sich um den Standardsteuersatz handelt. */
        public bool $isDefault,
        public ?DateTimeImmutable $created = null,
        public ?DateTimeImmutable $updated = null,
        public ?string $customfield = null,
        public ?bool $canDelete = null,
    ) {
    }

    /**
     * Hydriert ein TaxRate-Model aus einem Billomat-Array.
     *
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ScalarCaster::toIntOrNull($data['id'] ?? null),
            accountId: ScalarCaster::toIntOrNull($data['account_id'] ?? null),
            name: (string) ($data['name'] ?? ''),
            rate: isset($data['rate']) ? (float) $data['rate'] : 0.0,
            isDefault: isset($data['is_default']) && 1 === (int) $data['is_default'],
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            updated: ScalarCaster::toDateTimeOrNull($data['updated'] ?? null),
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
            canDelete: ScalarCaster::toBoolOrNull($data['can_delete'] ?? null),
        );
    }

    /**
     * Exportiert den Steuersatz als Array mit Billomat-Feldnamen.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'account_id' => $this->accountId,
            'name' => $this->name,
            'rate' => $this->rate,
            'is_default' => $this->isDefault ? 1 : 0,
            'created' => $this->created?->format(DATE_ATOM),
            'updated' => $this->updated?->format(DATE_ATOM),
            'customfield' => $this->customfield,
            'can_delete' => null === $this->canDelete ? null : ($this->canDelete ? 1 : 0),
        ];
    }
}
