<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Typisierter Payload für POST/PUT /taxes.
 *
 * Dokumentation:
 * https://www.billomat.com/api/einstellungen/steuersaetze/
 */
final class TaxRateCreateOptions
{
    /**
     * Name des Steuersatzes.
     *
     * Billomat-Feld: name
     * Typ: ALNUM
     * Pflicht: ja
     */
    public string $name;

    /**
     * Höhe des Steuersatzes in Prozent.
     *
     * Billomat-Feld: rate
     * Typ: FLOAT
     * Pflicht: ja
     */
    public float $rate;

    /**
     * Ob es sich um den Standard-Steuersatz handelt (1 = ja, 0 = nein).
     *
     * Billomat-Feld: is_default
     * Typ: BOOL (0/1)
     * Pflicht: ja
     */
    public bool $isDefault;

    public function __construct(string $name, float $rate, bool $isDefault = false)
    {
        $this->name = $name;
        $this->rate = $rate;
        $this->isDefault = $isDefault;
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'rate' => $this->rate,
            'is_default' => $this->isDefault ? 1 : 0,
        ];
    }
}