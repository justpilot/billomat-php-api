<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model\Enum;

/**
 * Erlaubte Schlüssel für die Metadaten eines Posteingangs-Dokuments (`metadata`).
 *
 * Billomat lehnt andere Schlüssel mit HTTP 400 ab („Unknown meta data field“).
 *
 * Doku: https://www.billomat.com/api/eingangsrechnungen/inbox/
 */
enum InboxDocumentMetadataKey: string
{
    use FromApi;

    case TOTAL_GROSS = 'TOTAL_GROSS';
    case TOTAL_NET = 'TOTAL_NET';
    case CURRENCY_CODE = 'CURRENCY_CODE';
    case INVOICE_NUMBER = 'INVOICE_NUMBER';
    case CLIENT_NUMBER = 'CLIENT_NUMBER';
    case DATE = 'DATE';
    case DUE_DATE = 'DUE_DATE';
    case NOTE = 'NOTE';
    case SENDER_SALUTATION = 'SENDER_SALUTATION';
    case SENDER_NAME = 'SENDER_NAME';
    case SENDER_NAME_ADDITION = 'SENDER_NAME_ADDITION';
    case SENDER_STREET = 'SENDER_STREET';
    case SENDER_PO_BOX = 'SENDER_PO_BOX';
    case SENDER_ZIP = 'SENDER_ZIP';
    case SENDER_CITY = 'SENDER_CITY';
    case SENDER_STATE = 'SENDER_STATE';
    case SENDER_COUNTRY = 'SENDER_COUNTRY';
    case PHONE = 'PHONE';
    case MOBILE = 'MOBILE';
    case FAX = 'FAX';
    case EMAIL = 'EMAIL';
    case WWW = 'WWW';
    case BANK_ACCOUNT_NUMBER = 'BANK_ACCOUNT_NUMBER';
    case BANK_NUMBER = 'BANK_NUMBER';
    case BANK_NAME = 'BANK_NAME';
    case IBAN = 'IBAN';
    case BIC = 'BIC';
    case TAX_NUMBER = 'TAX_NUMBER';
    case VAT_NUMBER = 'VAT_NUMBER';
    case COMPANY_REGISTER_ID = 'COMPANY_REGISTER_ID';
    case SEPA_CREDITOR_IDENTIFIER = 'SEPA_CREDITOR_IDENTIFIER';

    /**
     * Menschlich lesbares Label.
     */
    public function label(): string
    {
        return match ($this) {
            self::TOTAL_GROSS => 'Gesamtbetrag brutto',
            self::TOTAL_NET => 'Gesamtbetrag netto',
            self::CURRENCY_CODE => 'ISO-Währungscode',
            self::INVOICE_NUMBER => 'Rechnungsnummer',
            self::CLIENT_NUMBER => 'Kundennummer beim Lieferanten',
            self::DATE => 'Datum',
            self::DUE_DATE => 'Fälligkeitsdatum',
            self::NOTE => 'Notiz',
            self::SENDER_SALUTATION => 'Anrede des Absenders',
            self::SENDER_NAME => 'Name des Absenders',
            self::SENDER_NAME_ADDITION => 'Namenszusatz des Absenders',
            self::SENDER_STREET => 'Straße des Absenders',
            self::SENDER_PO_BOX => 'Postfach des Absenders',
            self::SENDER_ZIP => 'PLZ des Absenders',
            self::SENDER_CITY => 'Stadt des Absenders',
            self::SENDER_STATE => 'Staat/Bundesland des Absenders',
            self::SENDER_COUNTRY => 'Land des Absenders',
            self::PHONE => 'Telefonnummer des Absenders',
            self::MOBILE => 'Handynummer des Absenders',
            self::FAX => 'Faxnummer des Absenders',
            self::EMAIL => 'E-Mail-Adresse des Absenders',
            self::WWW => 'Homepage des Absenders',
            self::BANK_ACCOUNT_NUMBER => 'Kontonummer des Absenders',
            self::BANK_NUMBER => 'BLZ des Absenders',
            self::BANK_NAME => 'Bank des Absenders',
            self::IBAN => 'IBAN des Absenders',
            self::BIC => 'BIC des Absenders',
            self::TAX_NUMBER => 'Steuernummer des Absenders',
            self::VAT_NUMBER => 'USt-IdNr. des Absenders',
            self::COMPANY_REGISTER_ID => 'Handelsregisternummer des Absenders',
            self::SEPA_CREDITOR_IDENTIFIER => 'SEPA-Gläubiger-ID des Absenders',
        };
    }
}
