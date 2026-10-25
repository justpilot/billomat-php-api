<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\NetGross;
use Justpilot\Billomat\Model\Enum\NumberRangeMode;

/**
 * Payload für PUT /settings.
 *
 * Nicht gesetzte Felder (null) werden nicht gesendet, {@see Clear::Field} leert ein Feld.
 *
 * Nur änderbare Felder – readonly Felder (`*_next`, `created`, `updated`)
 * sind hier bewusst nicht abbildbar.
 *
 * Dokumentation:
 * https://www.billomat.com/en/api/settings/
 */
final readonly class SettingsUpdateOptions implements WriteOptions
{
    public function __construct(
        // ---------------------------------------------------------------------
        // Kundenportal Farben
        // ---------------------------------------------------------------------
        public string|Clear|null $bgcolor = null,
        public string|Clear|null $color1 = null,
        public string|Clear|null $color2 = null,
        public string|Clear|null $color3 = null,
        // ---------------------------------------------------------------------
        // Locale / Währung / SEPA / Nummernkreis
        // ---------------------------------------------------------------------
        public string|Clear|null $currencyCode = null,
        public string|Clear|null $locale = null,
        public NetGross|Clear|null $netGross = null,
        public string|Clear|null $sepaCreditorId = null,
        public NumberRangeMode|Clear|null $numberRangeMode = null,
        // ---------------------------------------------------------------------
        // Artikelnummer
        // ---------------------------------------------------------------------
        public string|Clear|null $articleNumberPre = null,
        public int|Clear|null $articleNumberLength = null,
        /**
         * Preisgruppen (Index → Name). Werden als `price_group2`, `price_group3`, …
         * an die API geschickt.
         *
         * @var array<int,string>
         */
        public array $priceGroups = [],
        // ---------------------------------------------------------------------
        // Kundennummer
        // ---------------------------------------------------------------------
        public string|Clear|null $clientNumberPre = null,
        public int|Clear|null $clientNumberLength = null,
        // ---------------------------------------------------------------------
        // Rechnungsnummer / -Defaults
        // ---------------------------------------------------------------------
        public string|Clear|null $invoiceNumberPre = null,
        public int|Clear|null $invoiceNumberLength = null,
        public string|Clear|null $invoiceLabel = null,
        public string|Clear|null $invoiceIntro = null,
        public string|Clear|null $invoiceNote = null,
        public string|Clear|null $invoiceFilename = null,
        public int|Clear|null $dueDays = null,
        public float|Clear|null $discountRate = null,
        public int|Clear|null $discountDays = null,
        // ---------------------------------------------------------------------
        // Angebote
        // ---------------------------------------------------------------------
        public string|Clear|null $offerNumberPre = null,
        public int|Clear|null $offerNumberLength = null,
        public string|Clear|null $offerLabel = null,
        public string|Clear|null $offerIntro = null,
        public string|Clear|null $offerNote = null,
        public string|Clear|null $offerFilename = null,
        public int|Clear|null $offerValidityDays = null,
        // ---------------------------------------------------------------------
        // Auftragsbestätigungen
        // ---------------------------------------------------------------------
        public string|Clear|null $confirmationNumberPre = null,
        public int|Clear|null $confirmationNumberLength = null,
        public string|Clear|null $confirmationLabel = null,
        public string|Clear|null $confirmationIntro = null,
        public string|Clear|null $confirmationNote = null,
        public string|Clear|null $confirmationFilename = null,
        // ---------------------------------------------------------------------
        // Gutschriften
        // ---------------------------------------------------------------------
        public string|Clear|null $creditNoteNumberPre = null,
        public int|Clear|null $creditNoteNumberLength = null,
        public string|Clear|null $creditNoteLabel = null,
        public string|Clear|null $creditNoteIntro = null,
        public string|Clear|null $creditNoteNote = null,
        public string|Clear|null $creditNoteFilename = null,
        // ---------------------------------------------------------------------
        // Lieferscheine
        // ---------------------------------------------------------------------
        public string|Clear|null $deliveryNoteNumberPre = null,
        public int|Clear|null $deliveryNoteNumberLength = null,
        public string|Clear|null $deliveryNoteLabel = null,
        public string|Clear|null $deliveryNoteIntro = null,
        public string|Clear|null $deliveryNoteNote = null,
        public string|Clear|null $deliveryNoteFilename = null,
        // ---------------------------------------------------------------------
        // Mahnungen
        // ---------------------------------------------------------------------
        public string|Clear|null $reminderFilename = null,
        public int|Clear|null $reminderDueDays = null,
        // ---------------------------------------------------------------------
        // Briefe
        // ---------------------------------------------------------------------
        public string|Clear|null $letterLabel = null,
        public string|Clear|null $letterIntro = null,
        public string|Clear|null $letterFilename = null,
        // ---------------------------------------------------------------------
        // Sonstiges
        // ---------------------------------------------------------------------
        /** Druckversion ohne Hintergrundbild erzeugen? */
        public bool|Clear|null $printVersion = null,
        /** Standard-Absender für ausgehende E-Mails. */
        public string|Clear|null $defaultEmailSender = null,
        /**
         * BCC-Adressen. Werden als CSV-String an Billomat geschickt.
         *
         * @var list<string>
         */
        public array $bccAddresses = [],
        /** Steuerart / Taxation (account-abhängig). */
        public string|Clear|null $taxation = null,
    ) {
    }

    /** Kopie mit geändertem `bgcolor`. */
    public function withBgcolor(string|Clear|null $bgcolor): self
    {
        return clone($this, ['bgcolor' => $bgcolor]);
    }

    /** Kopie mit geändertem `color1`. */
    public function withColor1(string|Clear|null $color1): self
    {
        return clone($this, ['color1' => $color1]);
    }

    /** Kopie mit geändertem `color2`. */
    public function withColor2(string|Clear|null $color2): self
    {
        return clone($this, ['color2' => $color2]);
    }

    /** Kopie mit geändertem `color3`. */
    public function withColor3(string|Clear|null $color3): self
    {
        return clone($this, ['color3' => $color3]);
    }

    /** Kopie mit geändertem `currencyCode`. */
    public function withCurrencyCode(string|Clear|null $currencyCode): self
    {
        return clone($this, ['currencyCode' => $currencyCode]);
    }

    /** Kopie mit geändertem `locale`. */
    public function withLocale(string|Clear|null $locale): self
    {
        return clone($this, ['locale' => $locale]);
    }

    /** Kopie mit geändertem `netGross`. */
    public function withNetGross(NetGross|Clear|null $netGross): self
    {
        return clone($this, ['netGross' => $netGross]);
    }

    /** Kopie mit geändertem `sepaCreditorId`. */
    public function withSepaCreditorId(string|Clear|null $sepaCreditorId): self
    {
        return clone($this, ['sepaCreditorId' => $sepaCreditorId]);
    }

    /** Kopie mit geändertem `numberRangeMode`. */
    public function withNumberRangeMode(NumberRangeMode|Clear|null $numberRangeMode): self
    {
        return clone($this, ['numberRangeMode' => $numberRangeMode]);
    }

    /** Kopie mit geändertem `articleNumberPre`. */
    public function withArticleNumberPre(string|Clear|null $articleNumberPre): self
    {
        return clone($this, ['articleNumberPre' => $articleNumberPre]);
    }

    /** Kopie mit geändertem `articleNumberLength`. */
    public function withArticleNumberLength(int|Clear|null $articleNumberLength): self
    {
        return clone($this, ['articleNumberLength' => $articleNumberLength]);
    }

    /**
     * Kopie mit geändertem `priceGroups`.
     *
     * @param array<int,string> $priceGroups
     */
    public function withPriceGroups(array $priceGroups): self
    {
        return clone($this, ['priceGroups' => $priceGroups]);
    }

    /** Kopie mit geändertem `clientNumberPre`. */
    public function withClientNumberPre(string|Clear|null $clientNumberPre): self
    {
        return clone($this, ['clientNumberPre' => $clientNumberPre]);
    }

    /** Kopie mit geändertem `clientNumberLength`. */
    public function withClientNumberLength(int|Clear|null $clientNumberLength): self
    {
        return clone($this, ['clientNumberLength' => $clientNumberLength]);
    }

    /** Kopie mit geändertem `invoiceNumberPre`. */
    public function withInvoiceNumberPre(string|Clear|null $invoiceNumberPre): self
    {
        return clone($this, ['invoiceNumberPre' => $invoiceNumberPre]);
    }

    /** Kopie mit geändertem `invoiceNumberLength`. */
    public function withInvoiceNumberLength(int|Clear|null $invoiceNumberLength): self
    {
        return clone($this, ['invoiceNumberLength' => $invoiceNumberLength]);
    }

    /** Kopie mit geändertem `invoiceLabel`. */
    public function withInvoiceLabel(string|Clear|null $invoiceLabel): self
    {
        return clone($this, ['invoiceLabel' => $invoiceLabel]);
    }

    /** Kopie mit geändertem `invoiceIntro`. */
    public function withInvoiceIntro(string|Clear|null $invoiceIntro): self
    {
        return clone($this, ['invoiceIntro' => $invoiceIntro]);
    }

    /** Kopie mit geändertem `invoiceNote`. */
    public function withInvoiceNote(string|Clear|null $invoiceNote): self
    {
        return clone($this, ['invoiceNote' => $invoiceNote]);
    }

    /** Kopie mit geändertem `invoiceFilename`. */
    public function withInvoiceFilename(string|Clear|null $invoiceFilename): self
    {
        return clone($this, ['invoiceFilename' => $invoiceFilename]);
    }

    /** Kopie mit geändertem `dueDays`. */
    public function withDueDays(int|Clear|null $dueDays): self
    {
        return clone($this, ['dueDays' => $dueDays]);
    }

    /** Kopie mit geändertem `discountRate`. */
    public function withDiscountRate(float|Clear|null $discountRate): self
    {
        return clone($this, ['discountRate' => $discountRate]);
    }

    /** Kopie mit geändertem `discountDays`. */
    public function withDiscountDays(int|Clear|null $discountDays): self
    {
        return clone($this, ['discountDays' => $discountDays]);
    }

    /** Kopie mit geändertem `offerNumberPre`. */
    public function withOfferNumberPre(string|Clear|null $offerNumberPre): self
    {
        return clone($this, ['offerNumberPre' => $offerNumberPre]);
    }

    /** Kopie mit geändertem `offerNumberLength`. */
    public function withOfferNumberLength(int|Clear|null $offerNumberLength): self
    {
        return clone($this, ['offerNumberLength' => $offerNumberLength]);
    }

    /** Kopie mit geändertem `offerLabel`. */
    public function withOfferLabel(string|Clear|null $offerLabel): self
    {
        return clone($this, ['offerLabel' => $offerLabel]);
    }

    /** Kopie mit geändertem `offerIntro`. */
    public function withOfferIntro(string|Clear|null $offerIntro): self
    {
        return clone($this, ['offerIntro' => $offerIntro]);
    }

    /** Kopie mit geändertem `offerNote`. */
    public function withOfferNote(string|Clear|null $offerNote): self
    {
        return clone($this, ['offerNote' => $offerNote]);
    }

    /** Kopie mit geändertem `offerFilename`. */
    public function withOfferFilename(string|Clear|null $offerFilename): self
    {
        return clone($this, ['offerFilename' => $offerFilename]);
    }

    /** Kopie mit geändertem `offerValidityDays`. */
    public function withOfferValidityDays(int|Clear|null $offerValidityDays): self
    {
        return clone($this, ['offerValidityDays' => $offerValidityDays]);
    }

    /** Kopie mit geändertem `confirmationNumberPre`. */
    public function withConfirmationNumberPre(string|Clear|null $confirmationNumberPre): self
    {
        return clone($this, ['confirmationNumberPre' => $confirmationNumberPre]);
    }

    /** Kopie mit geändertem `confirmationNumberLength`. */
    public function withConfirmationNumberLength(int|Clear|null $confirmationNumberLength): self
    {
        return clone($this, ['confirmationNumberLength' => $confirmationNumberLength]);
    }

    /** Kopie mit geändertem `confirmationLabel`. */
    public function withConfirmationLabel(string|Clear|null $confirmationLabel): self
    {
        return clone($this, ['confirmationLabel' => $confirmationLabel]);
    }

    /** Kopie mit geändertem `confirmationIntro`. */
    public function withConfirmationIntro(string|Clear|null $confirmationIntro): self
    {
        return clone($this, ['confirmationIntro' => $confirmationIntro]);
    }

    /** Kopie mit geändertem `confirmationNote`. */
    public function withConfirmationNote(string|Clear|null $confirmationNote): self
    {
        return clone($this, ['confirmationNote' => $confirmationNote]);
    }

    /** Kopie mit geändertem `confirmationFilename`. */
    public function withConfirmationFilename(string|Clear|null $confirmationFilename): self
    {
        return clone($this, ['confirmationFilename' => $confirmationFilename]);
    }

    /** Kopie mit geändertem `creditNoteNumberPre`. */
    public function withCreditNoteNumberPre(string|Clear|null $creditNoteNumberPre): self
    {
        return clone($this, ['creditNoteNumberPre' => $creditNoteNumberPre]);
    }

    /** Kopie mit geändertem `creditNoteNumberLength`. */
    public function withCreditNoteNumberLength(int|Clear|null $creditNoteNumberLength): self
    {
        return clone($this, ['creditNoteNumberLength' => $creditNoteNumberLength]);
    }

    /** Kopie mit geändertem `creditNoteLabel`. */
    public function withCreditNoteLabel(string|Clear|null $creditNoteLabel): self
    {
        return clone($this, ['creditNoteLabel' => $creditNoteLabel]);
    }

    /** Kopie mit geändertem `creditNoteIntro`. */
    public function withCreditNoteIntro(string|Clear|null $creditNoteIntro): self
    {
        return clone($this, ['creditNoteIntro' => $creditNoteIntro]);
    }

    /** Kopie mit geändertem `creditNoteNote`. */
    public function withCreditNoteNote(string|Clear|null $creditNoteNote): self
    {
        return clone($this, ['creditNoteNote' => $creditNoteNote]);
    }

    /** Kopie mit geändertem `creditNoteFilename`. */
    public function withCreditNoteFilename(string|Clear|null $creditNoteFilename): self
    {
        return clone($this, ['creditNoteFilename' => $creditNoteFilename]);
    }

    /** Kopie mit geändertem `deliveryNoteNumberPre`. */
    public function withDeliveryNoteNumberPre(string|Clear|null $deliveryNoteNumberPre): self
    {
        return clone($this, ['deliveryNoteNumberPre' => $deliveryNoteNumberPre]);
    }

    /** Kopie mit geändertem `deliveryNoteNumberLength`. */
    public function withDeliveryNoteNumberLength(int|Clear|null $deliveryNoteNumberLength): self
    {
        return clone($this, ['deliveryNoteNumberLength' => $deliveryNoteNumberLength]);
    }

    /** Kopie mit geändertem `deliveryNoteLabel`. */
    public function withDeliveryNoteLabel(string|Clear|null $deliveryNoteLabel): self
    {
        return clone($this, ['deliveryNoteLabel' => $deliveryNoteLabel]);
    }

    /** Kopie mit geändertem `deliveryNoteIntro`. */
    public function withDeliveryNoteIntro(string|Clear|null $deliveryNoteIntro): self
    {
        return clone($this, ['deliveryNoteIntro' => $deliveryNoteIntro]);
    }

    /** Kopie mit geändertem `deliveryNoteNote`. */
    public function withDeliveryNoteNote(string|Clear|null $deliveryNoteNote): self
    {
        return clone($this, ['deliveryNoteNote' => $deliveryNoteNote]);
    }

    /** Kopie mit geändertem `deliveryNoteFilename`. */
    public function withDeliveryNoteFilename(string|Clear|null $deliveryNoteFilename): self
    {
        return clone($this, ['deliveryNoteFilename' => $deliveryNoteFilename]);
    }

    /** Kopie mit geändertem `reminderFilename`. */
    public function withReminderFilename(string|Clear|null $reminderFilename): self
    {
        return clone($this, ['reminderFilename' => $reminderFilename]);
    }

    /** Kopie mit geändertem `reminderDueDays`. */
    public function withReminderDueDays(int|Clear|null $reminderDueDays): self
    {
        return clone($this, ['reminderDueDays' => $reminderDueDays]);
    }

    /** Kopie mit geändertem `letterLabel`. */
    public function withLetterLabel(string|Clear|null $letterLabel): self
    {
        return clone($this, ['letterLabel' => $letterLabel]);
    }

    /** Kopie mit geändertem `letterIntro`. */
    public function withLetterIntro(string|Clear|null $letterIntro): self
    {
        return clone($this, ['letterIntro' => $letterIntro]);
    }

    /** Kopie mit geändertem `letterFilename`. */
    public function withLetterFilename(string|Clear|null $letterFilename): self
    {
        return clone($this, ['letterFilename' => $letterFilename]);
    }

    /** Kopie mit geändertem `printVersion`. */
    public function withPrintVersion(bool|Clear|null $printVersion): self
    {
        return clone($this, ['printVersion' => $printVersion]);
    }

    /** Kopie mit geändertem `defaultEmailSender`. */
    public function withDefaultEmailSender(string|Clear|null $defaultEmailSender): self
    {
        return clone($this, ['defaultEmailSender' => $defaultEmailSender]);
    }

    /**
     * Kopie mit geändertem `bccAddresses`.
     *
     * @param list<string> $bccAddresses
     */
    public function withBccAddresses(array $bccAddresses): self
    {
        return clone($this, ['bccAddresses' => $bccAddresses]);
    }

    /** Kopie mit geändertem `taxation`. */
    public function withTaxation(string|Clear|null $taxation): self
    {
        return clone($this, ['taxation' => $taxation]);
    }

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
            'net_gross' => $this->netGross instanceof NetGross ? $this->netGross->value : $this->netGross,
            'sepa_creditor_id' => $this->sepaCreditorId,
            'number_range_mode' => $this->numberRangeMode instanceof NumberRangeMode ? $this->numberRangeMode->value : $this->numberRangeMode,

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

            'print_version' => \is_bool($this->printVersion) ? ($this->printVersion ? 1 : 0) : $this->printVersion,

            'default_email_sender' => $this->defaultEmailSender,
            'bcc_addresses' => [] !== $this->bccAddresses
                ? implode(',', $this->bccAddresses)
                : null,

            'taxation' => $this->taxation,
        ];

        // Preisgruppen als price_group2, price_group3, …
        foreach ($this->priceGroups as $index => $name) {
            $data['price_group'.$index] = $name;
        }

        // Nicht gesetzte Felder entfernen, Clear::Field als null senden.
        return array_map(
            static fn (mixed $v): mixed => $v instanceof Clear ? null : $v,
            array_filter($data, static fn (mixed $v): bool => null !== $v),
        );
    }
}
