<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Model\Enum\NetGross;
use Justpilot\Billomat\Model\Enum\NumberRangeMode;
use Justpilot\Billomat\Model\Enum\TemplateEngine;

/**
 * Account-Einstellungen (GET/PUT /api/settings).
 *
 * Basierend auf der Billomat-API-Dokumentation:
 * https://www.billomat.com/api/einstellungen/
 *
 * Hinweis:
 * Billomat liefert viele numerische Werte als Strings ("0", "14" etc.). Dieses Model normalisiert
 * diese Werte in sinnvolle PHP-Typen (int/float/bool/DateTimeImmutable).
 */
final readonly class Settings
{
    /** created (datetime) */
    public ?\DateTimeImmutable $created;

    /** updated (datetime) */
    public ?\DateTimeImmutable $updated;

    /** Hintergrundfarbe im Kundenportal als hexadezimaler RGB-Wert */
    public ?string $bgcolor;

    /** Farbe 1 im Kundenportal als hexadezimaler RGB-Wert */
    public ?string $color1;

    /** Farbe 2 im Kundenportal als hexadezimaler RGB-Wert */
    public ?string $color2;

    /** Farbe 3 im Kundenportal als hexadezimaler RGB-Wert */
    public ?string $color3;

    /** Standard-Währung des Accounts (ISO-Währungscode) */
    public ?string $currencyCode;

    /** Gebietsschema des Accounts (z.B. de_DE) */
    public ?string $locale;

    /** Preisbasis (NET/GROSS) */
    public ?NetGross $netGross;

    /** SEPA-Gläubiger-Identifikationsnummer */
    public ?string $sepaCreditorId;

    /** Nummernkreis-Modus (IGNORE_PREFIX / CONSIDER_PREFIX) */
    public ?NumberRangeMode $numberRangeMode;

    /** Präfix Artikelnummer */
    public ?string $articleNumberPre;

    /** Mindestlänge von Artikelnummern */
    public ?int $articleNumberLength;

    /** Nächste fortlaufende Artikelnummer (readonly) */
    public ?int $articleNumberNext;

    /**
     * Preisgruppen (price_group2, price_group3, ...).
     *
     * @var array<int,string> index => name (z.B. 2 => "Preisgruppe 2")
     */
    public array $priceGroups;

    /** Präfix Kundennummer */
    public ?string $clientNumberPre;

    /** Mindestlänge von Kundennummern */
    public ?int $clientNumberLength;

    /** Nächste fortlaufende Kundennummer (readonly) */
    public ?int $clientNumberNext;

    /** Präfix Rechnungsnummer (kann Platzhalter enthalten, z.B. "RE-[Date.year]-") */
    public ?string $invoiceNumberPre;

    /** Mindestlänge von Rechnungsnummern */
    public ?int $invoiceNumberLength;

    /** Nächste fortlaufende Rechnungsnummer (readonly) */
    public ?int $invoiceNumberNext;

    /** Dokument-Label für Rechnung */
    public ?string $invoiceLabel;

    /** Einleitungstext für Rechnung */
    public ?string $invoiceIntro;

    /** Notiztext für Rechnung */
    public ?string $invoiceNote;

    /** Dateiname Rechnung */
    public ?string $invoiceFilename;

    /** Fälligkeit in Tagen ab Rechnungsdatum */
    public ?int $dueDays;

    /** Skontosatz in Prozent */
    public ?float $discountRate;

    /** Skontozeitraum in Tagen ab Rechnungsdatum */
    public ?int $discountDays;

    /** Präfix Angebotsnummer */
    public ?string $offerNumberPre;

    /** Mindestlänge von Angebotsnummern */
    public ?int $offerNumberLength;

    /** Nächste fortlaufende Angebotsnummer (readonly) */
    public ?int $offerNumberNext;

    /** Dokument-Label für Angebot */
    public ?string $offerLabel;

    /** Einleitungstext für Angebot */
    public ?string $offerIntro;

    /** Notiztext für Angebot */
    public ?string $offerNote;

    /** Dateiname Angebot */
    public ?string $offerFilename;

    /** Gültigkeit von Angeboten in Tagen */
    public ?int $offerValidityDays;

    /** Präfix Auftragsbestätigungsnummer */
    public ?string $confirmationNumberPre;

    /** Mindestlänge von Auftragsbestätigungsnummern */
    public ?int $confirmationNumberLength;

    /** Nächste fortlaufende Auftragsbestätigungsnummer (readonly) */
    public ?int $confirmationNumberNext;

    /** Dokument-Label für Auftragsbestätigung */
    public ?string $confirmationLabel;

    /** Einleitungstext für Auftragsbestätigung */
    public ?string $confirmationIntro;

    /** Notiztext für Auftragsbestätigung */
    public ?string $confirmationNote;

    /** Dateiname Auftragsbestätigung */
    public ?string $confirmationFilename;

    /** Präfix Gutschriftennummer */
    public ?string $creditNoteNumberPre;

    /** Mindestlänge von Gutschriftennummern */
    public ?int $creditNoteNumberLength;

    /** Nächste fortlaufende Gutschriftennummer (readonly) */
    public ?int $creditNoteNumberNext;

    /** Dokument-Label für Gutschrift */
    public ?string $creditNoteLabel;

    /** Einleitungstext für Gutschrift */
    public ?string $creditNoteIntro;

    /** Notiztext für Gutschrift */
    public ?string $creditNoteNote;

    /** Dateiname Gutschrift */
    public ?string $creditNoteFilename;

    /** Präfix Lieferscheinnummer */
    public ?string $deliveryNoteNumberPre;

    /** Mindestlänge von Lieferscheinnummern */
    public ?int $deliveryNoteNumberLength;

    /** Nächste fortlaufende Lieferscheinnummer (readonly) */
    public ?int $deliveryNoteNumberNext;

    /** Dokument-Label für Lieferschein */
    public ?string $deliveryNoteLabel;

    /** Einleitungstext für Lieferschein */
    public ?string $deliveryNoteIntro;

    /** Notiztext für Lieferschein */
    public ?string $deliveryNoteNote;

    /** Dateiname Lieferschein */
    public ?string $deliveryNoteFilename;

    /** Dateiname Mahnung */
    public ?string $reminderFilename;

    /** Fälligkeit der Mahnung in Tagen ab Mahnungsdatum */
    public ?int $reminderDueDays;

    /** Brief-Label */
    public ?string $letterLabel;

    /** Brief-Einleitung */
    public ?string $letterIntro;

    /** Dateiname Brief */
    public ?string $letterFilename;

    /** Template-Engine */
    public ?TemplateEngine $templateEngine;

    /** Druckversion ohne Hintergrundbild erzeugen? */
    public ?bool $printVersion;

    /** Standard-Absender von E-Mails */
    public ?string $defaultEmailSender;

    /**
     * BCC-Adressen (in Billomat teils als String/CSV geliefert).
     *
     * @var list<string>
     */
    public array $bccAddresses;

    /** taxation (laut Response vorhanden, Format je nach Account ggf. leer) */
    public ?string $taxation;

    /**
     * @param array<int,string> $priceGroups
     * @param list<string> $bccAddresses
     */
    public function __construct(
        ?\DateTimeImmutable $created = null,
        ?\DateTimeImmutable $updated = null,

        ?string             $bgcolor = null,
        ?string             $color1 = null,
        ?string             $color2 = null,
        ?string             $color3 = null,

        ?string             $currencyCode = null,
        ?string             $locale = null,
        ?NetGross           $netGross = null,

        ?string             $sepaCreditorId = null,
        ?NumberRangeMode    $numberRangeMode = null,

        ?string             $articleNumberPre = null,
        ?int                $articleNumberLength = null,
        ?int                $articleNumberNext = null,

        array               $priceGroups = [],

        ?string             $clientNumberPre = null,
        ?int                $clientNumberLength = null,
        ?int                $clientNumberNext = null,

        ?string             $invoiceNumberPre = null,
        ?int                $invoiceNumberLength = null,
        ?int                $invoiceNumberNext = null,
        ?string             $invoiceLabel = null,
        ?string             $invoiceIntro = null,
        ?string             $invoiceNote = null,
        ?string             $invoiceFilename = null,

        ?int                $dueDays = null,
        ?float              $discountRate = null,
        ?int                $discountDays = null,

        ?string             $offerNumberPre = null,
        ?int                $offerNumberLength = null,
        ?int                $offerNumberNext = null,
        ?string             $offerLabel = null,
        ?string             $offerIntro = null,
        ?string             $offerNote = null,
        ?string             $offerFilename = null,
        ?int                $offerValidityDays = null,

        ?string             $confirmationNumberPre = null,
        ?int                $confirmationNumberLength = null,
        ?int                $confirmationNumberNext = null,
        ?string             $confirmationLabel = null,
        ?string             $confirmationIntro = null,
        ?string             $confirmationNote = null,
        ?string             $confirmationFilename = null,

        ?string             $creditNoteNumberPre = null,
        ?int                $creditNoteNumberLength = null,
        ?int                $creditNoteNumberNext = null,
        ?string             $creditNoteLabel = null,
        ?string             $creditNoteIntro = null,
        ?string             $creditNoteNote = null,
        ?string             $creditNoteFilename = null,

        ?string             $deliveryNoteNumberPre = null,
        ?int                $deliveryNoteNumberLength = null,
        ?int                $deliveryNoteNumberNext = null,
        ?string             $deliveryNoteLabel = null,
        ?string             $deliveryNoteIntro = null,
        ?string             $deliveryNoteNote = null,
        ?string             $deliveryNoteFilename = null,

        ?string             $reminderFilename = null,
        ?int                $reminderDueDays = null,
        ?string             $letterLabel = null,
        ?string             $letterIntro = null,
        ?string             $letterFilename = null,

        ?TemplateEngine     $templateEngine = null,
        ?bool               $printVersion = null,
        ?string             $defaultEmailSender = null,
        array               $bccAddresses = [],

        ?string             $taxation = null,
    )
    {
        $this->created = $created;
        $this->updated = $updated;

        $this->bgcolor = $bgcolor;
        $this->color1 = $color1;
        $this->color2 = $color2;
        $this->color3 = $color3;

        $this->currencyCode = $currencyCode;
        $this->locale = $locale;
        $this->netGross = $netGross;

        $this->sepaCreditorId = $sepaCreditorId;
        $this->numberRangeMode = $numberRangeMode;

        $this->articleNumberPre = $articleNumberPre;
        $this->articleNumberLength = $articleNumberLength;
        $this->articleNumberNext = $articleNumberNext;

        $this->priceGroups = $priceGroups;

        $this->clientNumberPre = $clientNumberPre;
        $this->clientNumberLength = $clientNumberLength;
        $this->clientNumberNext = $clientNumberNext;

        $this->invoiceNumberPre = $invoiceNumberPre;
        $this->invoiceNumberLength = $invoiceNumberLength;
        $this->invoiceNumberNext = $invoiceNumberNext;
        $this->invoiceLabel = $invoiceLabel;
        $this->invoiceIntro = $invoiceIntro;
        $this->invoiceNote = $invoiceNote;
        $this->invoiceFilename = $invoiceFilename;

        $this->dueDays = $dueDays;
        $this->discountRate = $discountRate;
        $this->discountDays = $discountDays;

        $this->offerNumberPre = $offerNumberPre;
        $this->offerNumberLength = $offerNumberLength;
        $this->offerNumberNext = $offerNumberNext;
        $this->offerLabel = $offerLabel;
        $this->offerIntro = $offerIntro;
        $this->offerNote = $offerNote;
        $this->offerFilename = $offerFilename;
        $this->offerValidityDays = $offerValidityDays;

        $this->confirmationNumberPre = $confirmationNumberPre;
        $this->confirmationNumberLength = $confirmationNumberLength;
        $this->confirmationNumberNext = $confirmationNumberNext;
        $this->confirmationLabel = $confirmationLabel;
        $this->confirmationIntro = $confirmationIntro;
        $this->confirmationNote = $confirmationNote;
        $this->confirmationFilename = $confirmationFilename;

        $this->creditNoteNumberPre = $creditNoteNumberPre;
        $this->creditNoteNumberLength = $creditNoteNumberLength;
        $this->creditNoteNumberNext = $creditNoteNumberNext;
        $this->creditNoteLabel = $creditNoteLabel;
        $this->creditNoteIntro = $creditNoteIntro;
        $this->creditNoteNote = $creditNoteNote;
        $this->creditNoteFilename = $creditNoteFilename;

        $this->deliveryNoteNumberPre = $deliveryNoteNumberPre;
        $this->deliveryNoteNumberLength = $deliveryNoteNumberLength;
        $this->deliveryNoteNumberNext = $deliveryNoteNumberNext;
        $this->deliveryNoteLabel = $deliveryNoteLabel;
        $this->deliveryNoteIntro = $deliveryNoteIntro;
        $this->deliveryNoteNote = $deliveryNoteNote;
        $this->deliveryNoteFilename = $deliveryNoteFilename;

        $this->reminderFilename = $reminderFilename;
        $this->reminderDueDays = $reminderDueDays;
        $this->letterLabel = $letterLabel;
        $this->letterIntro = $letterIntro;
        $this->letterFilename = $letterFilename;

        $this->templateEngine = $templateEngine;
        $this->printVersion = $printVersion;
        $this->defaultEmailSender = $defaultEmailSender;
        $this->bccAddresses = $bccAddresses;

        $this->taxation = $taxation;
    }

    /**
     * Hydriert Settings aus API-Array.
     *
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        // price_group2..n
        $priceGroups = [];
        foreach ($data as $k => $v) {
            if (is_string($k) && preg_match('/^price_group(\d+)$/', $k, $m) && is_string($v)) {
                $priceGroups[(int)$m[1]] = $v;
            }
        }

        // bcc_addresses: häufig als String/CSV
        $bcc = [];
        $rawBcc = $data['bcc_addresses'] ?? null;
        if (is_string($rawBcc)) {
            $rawBcc = trim($rawBcc);
            $bcc = $rawBcc === ''
                ? []
                : array_values(array_filter(array_map('trim', explode(',', $rawBcc)), static fn($s) => $s !== ''));
        } elseif (is_array($rawBcc) && isset($rawBcc['bcc_address'])) {
            // defensive: falls Billomat es als Tags liefert
            $tmp = $rawBcc['bcc_address'];
            if (is_string($tmp)) {
                $bcc = [$tmp];
            } elseif (is_array($tmp)) {
                $bcc = array_values(array_filter(array_map(
                    static fn($x) => is_string($x) ? $x : null,
                    $tmp
                )));
            }
        }

        $netGross = isset($data['net_gross']) && is_string($data['net_gross'])
            ? NetGross::tryFrom($data['net_gross'])
            : null;

        $mode = isset($data['number_range_mode']) && is_string($data['number_range_mode'])
            ? NumberRangeMode::fromApi($data['number_range_mode'])
            : null;

        $engine = isset($data['template_engine']) && is_string($data['template_engine'])
            ? TemplateEngine::fromApi($data['template_engine'])
            : null;

        return new self(
            created: self::dt($data, 'created'),
            updated: self::dt($data, 'updated'),

            bgcolor: self::str($data, 'bgcolor'),
            color1: self::str($data, 'color1'),
            color2: self::str($data, 'color2'),
            color3: self::str($data, 'color3'),

            currencyCode: self::str($data, 'currency_code'),
            locale: self::str($data, 'locale'),
            netGross: $netGross,

            sepaCreditorId: self::str($data, 'sepa_creditor_id'),
            numberRangeMode: $mode,

            articleNumberPre: self::str($data, 'article_number_pre'),
            articleNumberLength: self::int($data, 'article_number_length'),
            articleNumberNext: self::int($data, 'article_number_next'),

            priceGroups: $priceGroups,

            clientNumberPre: self::str($data, 'client_number_pre'),
            clientNumberLength: self::int($data, 'client_number_length'),
            clientNumberNext: self::int($data, 'client_number_next'),

            invoiceNumberPre: self::str($data, 'invoice_number_pre'),
            invoiceNumberLength: self::int($data, 'invoice_number_length'),
            invoiceNumberNext: self::int($data, 'invoice_number_next'),
            invoiceLabel: self::str($data, 'invoice_label'),
            invoiceIntro: self::str($data, 'invoice_intro'),
            invoiceNote: self::str($data, 'invoice_note'),
            invoiceFilename: self::str($data, 'invoice_filename'),

            dueDays: self::int($data, 'due_days'),
            discountRate: self::float($data, 'discount_rate'),
            discountDays: self::int($data, 'discount_days'),

            offerNumberPre: self::str($data, 'offer_number_pre'),
            offerNumberLength: self::int($data, 'offer_number_length'),
            offerNumberNext: self::int($data, 'offer_number_next'),
            offerLabel: self::str($data, 'offer_label'),
            offerIntro: self::str($data, 'offer_intro'),
            offerNote: self::str($data, 'offer_note'),
            offerFilename: self::str($data, 'offer_filename'),
            offerValidityDays: self::int($data, 'offer_validity_days'),

            confirmationNumberPre: self::str($data, 'confirmation_number_pre'),
            confirmationNumberLength: self::int($data, 'confirmation_number_length'),
            confirmationNumberNext: self::int($data, 'confirmation_number_next'),
            confirmationLabel: self::str($data, 'confirmation_label'),
            confirmationIntro: self::str($data, 'confirmation_intro'),
            confirmationNote: self::str($data, 'confirmation_note'),
            confirmationFilename: self::str($data, 'confirmation_filename'),

            creditNoteNumberPre: self::str($data, 'credit_note_number_pre'),
            creditNoteNumberLength: self::int($data, 'credit_note_number_length'),
            creditNoteNumberNext: self::int($data, 'credit_note_number_next'),
            creditNoteLabel: self::str($data, 'credit_note_label'),
            creditNoteIntro: self::str($data, 'credit_note_intro'),
            creditNoteNote: self::str($data, 'credit_note_note'),
            creditNoteFilename: self::str($data, 'credit_note_filename'),

            deliveryNoteNumberPre: self::str($data, 'delivery_note_number_pre'),
            deliveryNoteNumberLength: self::int($data, 'delivery_note_number_length'),
            deliveryNoteNumberNext: self::int($data, 'delivery_note_number_next'),
            deliveryNoteLabel: self::str($data, 'delivery_note_label'),
            deliveryNoteIntro: self::str($data, 'delivery_note_intro'),
            deliveryNoteNote: self::str($data, 'delivery_note_note'),
            deliveryNoteFilename: self::str($data, 'delivery_note_filename'),

            reminderFilename: self::str($data, 'reminder_filename'),
            reminderDueDays: self::int($data, 'reminder_due_days'),
            letterLabel: self::str($data, 'letter_label'),
            letterIntro: self::str($data, 'letter_intro'),
            letterFilename: self::str($data, 'letter_filename'),

            templateEngine: $engine,
            printVersion: self::bool($data, 'print_version'),
            defaultEmailSender: self::str($data, 'default_email_sender'),
            bccAddresses: $bcc,

            taxation: self::str($data, 'taxation'),
        );
    }

    private static function str(array $data, string $key): ?string
    {
        $v = $data[$key] ?? null;
        return is_string($v) ? $v : null;
    }

    private static function int(array $data, string $key): ?int
    {
        $v = $data[$key] ?? null;
        if ($v === null || $v === '') {
            return null;
        }
        return is_numeric($v) ? (int)$v : null;
    }

    private static function float(array $data, string $key): ?float
    {
        $v = $data[$key] ?? null;
        if ($v === null || $v === '') {
            return null;
        }
        return is_numeric($v) ? (float)$v : null;
    }

    private static function bool(array $data, string $key): ?bool
    {
        $v = $data[$key] ?? null;
        if ($v === null || $v === '') {
            return null;
        }
        if (is_bool($v)) {
            return $v;
        }
        $s = strtolower(trim((string)$v));
        return $s === '1' || $s === 'true' || $s === 'yes';
    }

    private static function dt(array $data, string $key): ?\DateTimeImmutable
    {
        $v = $data[$key] ?? null;
        if (!is_string($v) || trim($v) === '') {
            return null;
        }

        try {
            return new \DateTimeImmutable($v);
        } catch (\Throwable) {
            return null;
        }
    }
}