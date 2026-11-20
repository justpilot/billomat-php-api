<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model\Enum;

/**
 * Dokumenttyp, für den ein Freitext gilt.
 *
 * Billomat: type (INVOICE, CORRECTION, OFFER, CONFIRMATION, CREDIT_NOTE, DELIVERY_NOTE, LETTER).
 * Anders als bei {@see TemplateDocumentType} gibt es CORRECTION (Korrekturrechnung) statt REMINDER.
 *
 * Doku: https://www.billomat.com/en/api/settings/free-texts/
 */
enum FreeTextType: string
{
    use FromApi;

    case INVOICE = 'INVOICE';
    case CORRECTION = 'CORRECTION';
    case OFFER = 'OFFER';
    case CONFIRMATION = 'CONFIRMATION';
    case CREDIT_NOTE = 'CREDIT_NOTE';
    case DELIVERY_NOTE = 'DELIVERY_NOTE';
    case LETTER = 'LETTER';

    /**
     * Deutsches Label für UI/Logs.
     */
    public function label(): string
    {
        return match ($this) {
            self::INVOICE => 'Rechnung',
            self::CORRECTION => 'Korrekturrechnung',
            self::OFFER => 'Angebot',
            self::CONFIRMATION => 'Auftragsbestätigung',
            self::CREDIT_NOTE => 'Gutschrift',
            self::DELIVERY_NOTE => 'Lieferschein',
            self::LETTER => 'Brief',
        };
    }
}
