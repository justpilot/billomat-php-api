<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model\Enum;

/**
 * Dokumenttyp, für den eine E-Mail-Vorlage gilt.
 *
 * Billomat verwendet hier Pluralformen: type (INVOICES, OFFERS, CONFIRMATIONS, CREDIT_NOTES,
 * DELIVERY_NOTES, REMINDERS, PAYMENT_THANKS).
 *
 * Doku: https://www.billomat.com/api/einstellungen/email-vorlagen/
 */
enum EmailTemplateType: string
{
    use FromApi;

    case INVOICES = 'INVOICES';
    case OFFERS = 'OFFERS';
    case CONFIRMATIONS = 'CONFIRMATIONS';
    case CREDIT_NOTES = 'CREDIT_NOTES';
    case DELIVERY_NOTES = 'DELIVERY_NOTES';
    case REMINDERS = 'REMINDERS';
    case PAYMENT_THANKS = 'PAYMENT_THANKS';

    /**
     * Deutsches Label für UI/Logs.
     */
    public function label(): string
    {
        return match ($this) {
            self::INVOICES => 'Rechnungen',
            self::OFFERS => 'Angebote',
            self::CONFIRMATIONS => 'Auftragsbestätigungen',
            self::CREDIT_NOTES => 'Gutschriften',
            self::DELIVERY_NOTES => 'Lieferscheine',
            self::REMINDERS => 'Mahnungen',
            self::PAYMENT_THANKS => 'Zahlungsdank',
        };
    }
}
