<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model\Enum;

/**
 * Zahlungsarten für Invoice-Payments laut Billomat.
 *
 * Quelle:
 * https://www.billomat.com/api/rechnungen/zahlungen/
 */
enum InvoicePaymentType: string
{
    case INVOICE_CORRECTION = 'INVOICE_CORRECTION';
    case CREDIT_NOTE = 'CREDIT_NOTE';
    case BANK_CARD = 'BANK_CARD';
    case BANK_TRANSFER = 'BANK_TRANSFER';
    case DEBIT = 'DEBIT';
    case CASH = 'CASH';
    case CHECK = 'CHECK';
    case PAYPAL = 'PAYPAL';
    case CREDIT_CARD = 'CREDIT_CARD';
    case COUPON = 'COUPON';
    case MISC = 'MISC';

    public static function fromApi(?string $type): ?self
    {
        if ($type === null || $type === '') {
            return null;
        }

        return self::tryFrom($type);
    }

    /**
     * Deutsche Labels für die UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::INVOICE_CORRECTION => 'Korrekturrechnung',
            self::CREDIT_NOTE => 'Gutschrift',
            self::BANK_CARD => 'Bankkarte',
            self::BANK_TRANSFER => 'Überweisung',
            self::DEBIT => 'Lastschrift',
            self::CASH => 'Barzahlung',
            self::CHECK => 'Scheck',
            self::PAYPAL => 'PayPal',
            self::CREDIT_CARD => 'Kreditkarte',
            self::COUPON => 'Gutschein',
            self::MISC => 'Sonstiges',
        };
    }
}