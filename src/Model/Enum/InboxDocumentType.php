<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model\Enum;

/**
 * Dokumenttyp eines Posteingangs-Dokuments (`document_type`); hilft Billomats Texterkennung.
 *
 * Doku: https://www.billomat.com/api/eingangsrechnungen/inbox/
 */
enum InboxDocumentType: string
{
    use FromApi;

    case BANK_STATEMENT = 'BANK_STATEMENT';
    case CONTRACT = 'CONTRACT';
    case INVOICE = 'INVOICE';
    case REMINDER = 'REMINDER';
    case REMITTANCE_SLIP = 'REMITTANCE_SLIP';
    case TRAVEL_EXPENSE_REPORT = 'TRAVEL_EXPENSE_REPORT';
    case RECEIPT = 'RECEIPT';
    case FUEL_RECEIPT = 'FUEL_RECEIPT';
    case ENERGY = 'ENERGY';
    case OTHER = 'OTHER';

    /**
     * Menschlich lesbares Label.
     */
    public function label(): string
    {
        return match ($this) {
            self::BANK_STATEMENT => 'Kontoauszug',
            self::CONTRACT => 'Vertrag',
            self::INVOICE => 'Rechnung',
            self::REMINDER => 'Mahnung',
            self::REMITTANCE_SLIP => 'Überweisungsbeleg',
            self::TRAVEL_EXPENSE_REPORT => 'Reisekostenabrechnung',
            self::RECEIPT => 'Kassenbeleg',
            self::FUEL_RECEIPT => 'Tankbeleg',
            self::ENERGY => 'Stromrechnung',
            self::OTHER => 'Sonstiges',
        };
    }
}
