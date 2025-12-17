<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\NetGross;
use Justpilot\Billomat\Model\Enum\NumberRangeMode;

/**
 * Payload für PUT /settings
 *
 * Nur änderbare Felder – readonly Felder (…_next) fehlen bewusst.
 *
 * Dokumentation:
 * https://www.billomat.com/api/einstellungen/
 */
final class SettingsUpdateOptions
{
    /** Kundenportal Farben */
    public ?string $bgcolor = null;
    public ?string $color1 = null;
    public ?string $color2 = null;
    public ?string $color3 = null;

    /** Währung & Locale */
    public ?string $currencyCode = null;
    public ?string $locale = null;

    /** Preisbasis */
    public ?NetGross $netGross = null;

    /** SEPA */
    public ?string $sepaCreditorId = null;

    /** Nummernlogik */
    public ?NumberRangeMode $numberRangeMode = null;

    /** Kundennummer */
    public ?string $clientNumberPre = null;
    public ?int $clientNumberLength = null;

    /** Rechnungen */
    public ?string $invoiceNumberPre = null;
    public ?int $invoiceNumberLength = null;
    public ?string $invoiceFilename = null;
    public ?int $dueDays = null;
    public ?float $discountRate = null;
    public ?int $discountDays = null;

    /** Angebote */
    public ?string $offerNumberPre = null;
    public ?int $offerNumberLength = null;
    public ?int $offerValidityDays = null;

    /** Druckversion */
    public ?bool $printVersion = null;

    /** Mail */
    public ?string $defaultEmailSender = null;

    /**
     * Serialisiert in Billomat-konformes Payload-Array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [
            'bgcolor' => $this->bgcolor,
            'color1' => $this->color1,
            'color2' => $this->color2,
            'color3' => $this->color3,

            'currency_code' => $this->currencyCode,
            'locale' => $this->locale,
            'net_gross' => $this->netGross?->value,
            'sepa_creditor_id' => $this->sepaCreditorId,
            'number_range_mode' => $this->numberRangeMode?->value,

            'client_number_pre' => $this->clientNumberPre,
            'client_number_length' => $this->clientNumberLength,

            'invoice_number_pre' => $this->invoiceNumberPre,
            'invoice_number_length' => $this->invoiceNumberLength,
            'invoice_filename' => $this->invoiceFilename,
            'due_days' => $this->dueDays,
            'discount_rate' => $this->discountRate,
            'discount_days' => $this->discountDays,

            'offer_number_pre' => $this->offerNumberPre,
            'offer_number_length' => $this->offerNumberLength,
            'offer_validity_days' => $this->offerValidityDays,

            'print_version' => $this->printVersion !== null
                ? ($this->printVersion ? 1 : 0)
                : null,

            'default_email_sender' => $this->defaultEmailSender,
        ];

        return array_filter($data, static fn($v) => $v !== null);
    }
}