<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Model\Enum\NetGross;
use Justpilot\Billomat\Model\Enum\NumberRangeMode;
use Justpilot\Billomat\Model\Enum\TemplateEngine;
use Throwable;

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
    /**
     * @param array<int,string> $priceGroups
     * @param list<string>      $bccAddresses
     */
    public function __construct(
        /** created (datetime) */
        public ?DateTimeImmutable $created = null,
        /** updated (datetime) */
        public ?DateTimeImmutable $updated = null,
        /** Hintergrundfarbe im Kundenportal als hexadezimaler RGB-Wert */
        public ?string $bgcolor = null,
        /** Farbe 1 im Kundenportal als hexadezimaler RGB-Wert */
        public ?string $color1 = null,
        /** Farbe 2 im Kundenportal als hexadezimaler RGB-Wert */
        public ?string $color2 = null,
        /** Farbe 3 im Kundenportal als hexadezimaler RGB-Wert */
        public ?string $color3 = null,
        /** Standard-Währung des Accounts (ISO-Währungscode) */
        public ?string $currencyCode = null,
        /** Gebietsschema des Accounts (z.B. de_DE) */
        public ?string $locale = null,
        /** Preisbasis (NET/GROSS) */
        public ?NetGross $netGross = null,
        /** SEPA-Gläubiger-Identifikationsnummer */
        public ?string $sepaCreditorId = null,
        /** Nummernkreis-Modus (IGNORE_PREFIX / CONSIDER_PREFIX) */
        public ?NumberRangeMode $numberRangeMode = null,
        /** Präfix Artikelnummer */
        public ?string $articleNumberPre = null,
        /** Mindestlänge von Artikelnummern */
        public ?int $articleNumberLength = null,
        /** Nächste fortlaufende Artikelnummer (readonly) */
        public ?int $articleNumberNext = null,
        /**
         * Preisgruppen (price_group2, price_group3, ...).
         */
        public array $priceGroups = [],
        /** Präfix Kundennummer */
        public ?string $clientNumberPre = null,
        /** Mindestlänge von Kundennummern */
        public ?int $clientNumberLength = null,
        /** Nächste fortlaufende Kundennummer (readonly) */
        public ?int $clientNumberNext = null,
        /** Präfix Rechnungsnummer (kann Platzhalter enthalten, z.B. "RE-[Date.year]-") */
        public ?string $invoiceNumberPre = null,
        /** Mindestlänge von Rechnungsnummern */
        public ?int $invoiceNumberLength = null,
        /** Nächste fortlaufende Rechnungsnummer (readonly) */
        public ?int $invoiceNumberNext = null,
        /** Dokument-Label für Rechnung */
        public ?string $invoiceLabel = null,
        /** Einleitungstext für Rechnung */
        public ?string $invoiceIntro = null,
        /** Notiztext für Rechnung */
        public ?string $invoiceNote = null,
        /** Dateiname Rechnung */
        public ?string $invoiceFilename = null,
        /** Fälligkeit in Tagen ab Rechnungsdatum */
        public ?int $dueDays = null,
        /** Skontosatz in Prozent */
        public ?float $discountRate = null,
        /** Skontozeitraum in Tagen ab Rechnungsdatum */
        public ?int $discountDays = null,
        /** Präfix Angebotsnummer */
        public ?string $offerNumberPre = null,
        /** Mindestlänge von Angebotsnummern */
        public ?int $offerNumberLength = null,
        /** Nächste fortlaufende Angebotsnummer (readonly) */
        public ?int $offerNumberNext = null,
        /** Dokument-Label für Angebot */
        public ?string $offerLabel = null,
        /** Einleitungstext für Angebot */
        public ?string $offerIntro = null,
        /** Notiztext für Angebot */
        public ?string $offerNote = null,
        /** Dateiname Angebot */
        public ?string $offerFilename = null,
        /** Gültigkeit von Angeboten in Tagen */
        public ?int $offerValidityDays = null,
        /** Präfix Auftragsbestätigungsnummer */
        public ?string $confirmationNumberPre = null,
        /** Mindestlänge von Auftragsbestätigungsnummern */
        public ?int $confirmationNumberLength = null,
        /** Nächste fortlaufende Auftragsbestätigungsnummer (readonly) */
        public ?int $confirmationNumberNext = null,
        /** Dokument-Label für Auftragsbestätigung */
        public ?string $confirmationLabel = null,
        /** Einleitungstext für Auftragsbestätigung */
        public ?string $confirmationIntro = null,
        /** Notiztext für Auftragsbestätigung */
        public ?string $confirmationNote = null,
        /** Dateiname Auftragsbestätigung */
        public ?string $confirmationFilename = null,
        /** Präfix Gutschriftennummer */
        public ?string $creditNoteNumberPre = null,
        /** Mindestlänge von Gutschriftennummern */
        public ?int $creditNoteNumberLength = null,
        /** Nächste fortlaufende Gutschriftennummer (readonly) */
        public ?int $creditNoteNumberNext = null,
        /** Dokument-Label für Gutschrift */
        public ?string $creditNoteLabel = null,
        /** Einleitungstext für Gutschrift */
        public ?string $creditNoteIntro = null,
        /** Notiztext für Gutschrift */
        public ?string $creditNoteNote = null,
        /** Dateiname Gutschrift */
        public ?string $creditNoteFilename = null,
        /** Präfix Lieferscheinnummer */
        public ?string $deliveryNoteNumberPre = null,
        /** Mindestlänge von Lieferscheinnummern */
        public ?int $deliveryNoteNumberLength = null,
        /** Nächste fortlaufende Lieferscheinnummer (readonly) */
        public ?int $deliveryNoteNumberNext = null,
        /** Dokument-Label für Lieferschein */
        public ?string $deliveryNoteLabel = null,
        /** Einleitungstext für Lieferschein */
        public ?string $deliveryNoteIntro = null,
        /** Notiztext für Lieferschein */
        public ?string $deliveryNoteNote = null,
        /** Dateiname Lieferschein */
        public ?string $deliveryNoteFilename = null,
        /** Dateiname Mahnung */
        public ?string $reminderFilename = null,
        /** Fälligkeit der Mahnung in Tagen ab Mahnungsdatum */
        public ?int $reminderDueDays = null,
        /** Brief-Label */
        public ?string $letterLabel = null,
        /** Brief-Einleitung */
        public ?string $letterIntro = null,
        /** Dateiname Brief */
        public ?string $letterFilename = null,
        /** Template-Engine */
        public ?TemplateEngine $templateEngine = null,
        /** Druckversion ohne Hintergrundbild erzeugen? */
        public ?bool $printVersion = null,
        /** Standard-Absender von E-Mails */
        public ?string $defaultEmailSender = null,
        /**
         * BCC-Adressen (in Billomat teils als String/CSV geliefert).
         */
        public array $bccAddresses = [],
        /** taxation (laut Response vorhanden, Format je nach Account ggf. leer) */
        public ?string $taxation = null
    ) {
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
            if (\is_string($k) && preg_match('/^price_group(\d+)$/', $k, $m) && \is_string($v)) {
                $priceGroups[(int) $m[1]] = $v;
            }
        }

        // bcc_addresses: häufig als String/CSV
        $bcc = [];
        $rawBcc = $data['bcc_addresses'] ?? null;
        if (\is_string($rawBcc)) {
            $rawBcc = trim($rawBcc);
            $bcc = '' === $rawBcc
                ? []
                : array_values(array_filter(array_map(trim(...), explode(',', $rawBcc)), static fn ($s): bool => '' !== $s));
        } elseif (\is_array($rawBcc) && isset($rawBcc['bcc_address'])) {
            // defensive: falls Billomat es als Tags liefert
            $tmp = $rawBcc['bcc_address'];
            if (\is_string($tmp)) {
                $bcc = [$tmp];
            } elseif (\is_array($tmp)) {
                $bcc = array_values(array_filter(array_map(
                    static fn ($x): ?string => \is_string($x) ? $x : null,
                    $tmp
                )));
            }
        }

        $netGross = isset($data['net_gross']) && \is_string($data['net_gross'])
            ? NetGross::tryFrom($data['net_gross'])
            : null;

        $mode = isset($data['number_range_mode']) && \is_string($data['number_range_mode'])
            ? NumberRangeMode::fromApi($data['number_range_mode'])
            : null;

        $engine = isset($data['template_engine']) && \is_string($data['template_engine'])
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

        return \is_string($v) ? $v : null;
    }

    private static function int(array $data, string $key): ?int
    {
        $v = $data[$key] ?? null;
        if (null === $v || '' === $v) {
            return null;
        }

        return is_numeric($v) ? (int) $v : null;
    }

    private static function float(array $data, string $key): ?float
    {
        $v = $data[$key] ?? null;
        if (null === $v || '' === $v) {
            return null;
        }

        return is_numeric($v) ? (float) $v : null;
    }

    private static function bool(array $data, string $key): ?bool
    {
        $v = $data[$key] ?? null;
        if (null === $v || '' === $v) {
            return null;
        }
        if (\is_bool($v)) {
            return $v;
        }
        $s = strtolower(trim((string) $v));

        return \in_array($s, ['1', 'true', 'yes'], true);
    }

    private static function dt(array $data, string $key): ?DateTimeImmutable
    {
        $v = $data[$key] ?? null;
        if (!\is_string($v) || '' === trim($v)) {
            return null;
        }

        try {
            return new DateTimeImmutable($v);
        } catch (Throwable) {
            return null;
        }
    }
}
