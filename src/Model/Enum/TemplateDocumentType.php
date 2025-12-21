<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model\Enum;

/**
 * Dokumenttyp der Vorlage.
 *
 * Billomat: type (INVOICE, OFFER, CONFIRMATION, REMINDER, DELIVERY_NOTE, CREDIT_NOTE, LETTER)
 */
enum TemplateDocumentType: string
{
    case INVOICE = 'INVOICE';
    case OFFER = 'OFFER';
    case CONFIRMATION = 'CONFIRMATION';
    case REMINDER = 'REMINDER';
    case DELIVERY_NOTE = 'DELIVERY_NOTE';
    case CREDIT_NOTE = 'CREDIT_NOTE';
    case LETTER = 'LETTER';

    public static function fromApi(?string $value): ?self
    {
        return $value === null ? null : (self::tryFrom($value) ?? null);
    }

    /**
     * Deutsches Label für UI/Logs.
     */
    public function label(): string
    {
        return match ($this) {
            self::INVOICE => 'Rechnung',
            self::OFFER => 'Angebot',
            self::CONFIRMATION => 'Auftragsbestätigung',
            self::REMINDER => 'Mahnung',
            self::DELIVERY_NOTE => 'Lieferschein',
            self::CREDIT_NOTE => 'Gutschrift',
            self::LETTER => 'Brief',
        };
    }
}