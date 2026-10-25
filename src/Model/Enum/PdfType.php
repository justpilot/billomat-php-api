<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model\Enum;

/**
 * PDF-Variante für `pdf()` aller Dokument-Ressourcen (Rechnungen, Angebote, Gutschriften, …).
 */
enum PdfType: string
{
    use FromApi;

    /** Signiertes PDF */
    case SIGNED = 'signed';

    /** Druckversion (ohne Hintergrund) */
    case PRINT = 'print';

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
