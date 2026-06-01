<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\NetGross;
use Justpilot\Billomat\Model\Enum\NumberRangeMode;

/**
 * Payload für PUT /settings
 *
 * Nur änderbare Felder – readonly Felder (`*_next`, `created`, `updated`)
 * sind hier bewusst nicht abbildbar.
 *
 * Dokumentation:
 * https://www.billomat.com/en/api/settings/
 */
final class SettingsUpdateOptions
{
    // ---------------------------------------------------------------------
    // Kundenportal Farben
    // ---------------------------------------------------------------------

    public ?string $bgcolor = null;
    public ?string $color1 = null;
    public ?string $color2 = null;
    public ?string $color3 = null;

    // ---------------------------------------------------------------------
    // Locale / Währung / SEPA / Nummernkreis
    // ---------------------------------------------------------------------

    public ?string $currencyCode = null;
    public ?string $locale = null;
    public ?NetGross $netGross = null;
    public ?string $sepaCreditorId = null;
    public ?NumberRangeMode $numberRangeMode = null;

    // ---------------------------------------------------------------------
    // Artikelnummer
    // ---------------------------------------------------------------------

    public ?string $articleNumberPre = null;
    public ?int $articleNumberLength = null;

    /**
     * Preisgruppen (Index → Name). Werden als `price_group2`, `price_group3`, …
     * an die API geschickt.
     *
     * @var array<int,string>
     */
    public array $priceGroups = [];

    // ---------------------------------------------------------------------
    // Kundennummer
    // ---------------------------------------------------------------------

    public ?string $clientNumberPre = null;
    public ?int $clientNumberLength = null;

    // ---------------------------------------------------------------------
    // Rechnungsnummer / -Defaults
    // ---------------------------------------------------------------------

    public ?string $invoiceNumberPre = null;
    public ?int $invoiceNumberLength = null;
    public ?string $invoiceLabel = null;
    public ?string $invoiceIntro = null;
    public ?string $invoiceNote = null;
    public ?string $invoiceFilename = null;

    public ?int $dueDays = null;
    public ?float $discountRate = null;
    public ?int $discountDays = null;

    // ---------------------------------------------------------------------
    // Angebote
    // ---------------------------------------------------------------------

    public ?string $offerNumberPre = null;
    public ?int $offerNumberLength = null;
    public ?string $offerLabel = null;
    public ?string $offerIntro = null;
    public ?string $offerNote = null;
    public ?string $offerFilename = null;
    public ?int $offerValidityDays = null;

    // ---------------------------------------------------------------------
    // Auftragsbestätigungen
    // ---------------------------------------------------------------------

    public ?string $confirmationNumberPre = null;
    public ?int $confirmationNumberLength = null;
    public ?string $confirmationLabel = null;
    public ?string $confirmationIntro = null;
    public ?string $confirmationNote = null;
    public ?string $confirmationFilename = null;

    // ---------------------------------------------------------------------
    // Gutschriften
    // ---------------------------------------------------------------------

    public ?string $creditNoteNumberPre = null;
    public ?int $creditNoteNumberLength = null;
    public ?string $creditNoteLabel = null;
    public ?string $creditNoteIntro = null;
    public ?string $creditNoteNote = null;
    public ?string $creditNoteFilename = null;

    // ---------------------------------------------------------------------
    // Lieferscheine
    // ---------------------------------------------------------------------

    public ?string $deliveryNoteNumberPre = null;
    public ?int $deliveryNoteNumberLength = null;
    public ?string $deliveryNoteLabel = null;
    public ?string $deliveryNoteIntro = null;
    public ?string $deliveryNoteNote = null;
    public ?string $deliveryNoteFilename = null;

    // ---------------------------------------------------------------------
    // Mahnungen
    // ---------------------------------------------------------------------

    public ?string $reminderFilename = null;
    public ?int $reminderDueDays = null;

    // ---------------------------------------------------------------------
    // Briefe
    // ---------------------------------------------------------------------

    public ?string $letterLabel = null;
    public ?string $letterIntro = null;
    public ?string $letterFilename = null;

    // ---------------------------------------------------------------------
    // Sonstiges
    // ---------------------------------------------------------------------

    /** Druckversion ohne Hintergrundbild erzeugen? */
    public ?bool $printVersion = null;

    /** Standard-Absender für ausgehende E-Mails. */
    public ?string $defaultEmailSender = null;

    /**
     * BCC-Adressen. Werden als CSV-String an Billomat geschickt.
     *
     * @var list<string>
     */
    public array $bccAddresses = [];

    /** Steuerart / Taxation (account-abhängig). */
    public ?string $taxation = null;

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

            'article_number_pre' => $this->articleNumberPre,
            'article_number_length' => $this->articleNumberLength,

            'client_number_pre' => $this->clientNumberPre,
            'client_number_length' => $this->clientNumberLength,

            'invoice_number_pre' => $this->invoiceNumberPre,
            'invoice_number_length' => $this->invoiceNumberLength,
            'invoice_label' => $this->invoiceLabel,
            'invoice_intro' => $this->invoiceIntro,
            'invoice_note' => $this->invoiceNote,
            'invoice_filename' => $this->invoiceFilename,

            'due_days' => $this->dueDays,
            'discount_rate' => $this->discountRate,
            'discount_days' => $this->discountDays,

            'offer_number_pre' => $this->offerNumberPre,
            'offer_number_length' => $this->offerNumberLength,
            'offer_label' => $this->offerLabel,
            'offer_intro' => $this->offerIntro,
            'offer_note' => $this->offerNote,
            'offer_filename' => $this->offerFilename,
            'offer_validity_days' => $this->offerValidityDays,

            'confirmation_number_pre' => $this->confirmationNumberPre,
            'confirmation_number_length' => $this->confirmationNumberLength,
            'confirmation_label' => $this->confirmationLabel,
            'confirmation_intro' => $this->confirmationIntro,
            'confirmation_note' => $this->confirmationNote,
            'confirmation_filename' => $this->confirmationFilename,

            'credit_note_number_pre' => $this->creditNoteNumberPre,
            'credit_note_number_length' => $this->creditNoteNumberLength,
            'credit_note_label' => $this->creditNoteLabel,
            'credit_note_intro' => $this->creditNoteIntro,
            'credit_note_note' => $this->creditNoteNote,
            'credit_note_filename' => $this->creditNoteFilename,

            'delivery_note_number_pre' => $this->deliveryNoteNumberPre,
            'delivery_note_number_length' => $this->deliveryNoteNumberLength,
            'delivery_note_label' => $this->deliveryNoteLabel,
            'delivery_note_intro' => $this->deliveryNoteIntro,
            'delivery_note_note' => $this->deliveryNoteNote,
            'delivery_note_filename' => $this->deliveryNoteFilename,

            'reminder_filename' => $this->reminderFilename,
            'reminder_due_days' => $this->reminderDueDays,

            'letter_label' => $this->letterLabel,
            'letter_intro' => $this->letterIntro,
            'letter_filename' => $this->letterFilename,

            'print_version' => $this->printVersion !== null
                ? ($this->printVersion ? 1 : 0)
                : null,

            'default_email_sender' => $this->defaultEmailSender,
            'bcc_addresses' => $this->bccAddresses !== []
                ? implode(',', $this->bccAddresses)
                : null,

            'taxation' => $this->taxation,
        ];

        // Preisgruppen als price_group2, price_group3, …
        foreach ($this->priceGroups as $index => $name) {
            $data['price_group' . $index] = $name;
        }

        return array_filter($data, static fn($v) => $v !== null);
    }
}
