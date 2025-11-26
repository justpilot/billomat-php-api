<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model\Enum;

enum InvoicePdfType: string
{
    /** Signiertes PDF */
    case SIGNED = 'signed';

    /** Druckversion (ohne Hintergrund) */
    case PRINT = 'print';

    /**
     * Wandelt ein API-String oder null in ein Enum um.
     */
    public static function fromApi(?string $value): ?self
    {
        return $value ? self::tryFrom($value) : null;
    }

    /**
     * Menschlich lesbares Label.
     */
    public function label(): string
    {
        return match ($this) {
            self::SIGNED => 'Signiertes PDF',
            self::PRINT => 'Druckversion (ohne Hintergrund)',
        };
    }
}