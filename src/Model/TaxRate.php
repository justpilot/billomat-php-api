<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

/**
 * Repräsentiert einen Steuersatz aus der Billomat-API.
 *
 * Dokumentation:
 * https://www.billomat.com/api/einstellungen/steuersaetze/
 */
final readonly class TaxRate
{
    /** Interne Billomat-ID des Steuersatzes. */
    public ?int $id;

    /** ID des Accounts, zu dem der Steuersatz gehört. */
    public ?int $accountId;

    /** Name des Steuersatzes, z. B. "MwSt". */
    public string $name;

    /** Höhe des Steuersatzes in Prozent, z. B. 19.0. */
    public float $rate;

    /** Gibt an, ob es sich um den Standardsteuersatz handelt. */
    public bool $isDefault;

    public function __construct(
        ?int   $id,
        ?int   $accountId,
        string $name,
        float  $rate,
        bool   $isDefault,
    )
    {
        $this->id = $id;
        $this->accountId = $accountId;
        $this->name = $name;
        $this->rate = $rate;
        $this->isDefault = $isDefault;
    }

    /**
     * Hydriert ein TaxRate-Model aus einem Billomat-Array.
     *
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int)$data['id'] : null,
            accountId: isset($data['account_id']) ? (int)$data['account_id'] : null,
            name: (string)($data['name'] ?? ''),
            rate: isset($data['rate']) ? (float)$data['rate'] : 0.0,
            isDefault: isset($data['is_default']) && (int)$data['is_default'] === 1,
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
        ];
    }
}