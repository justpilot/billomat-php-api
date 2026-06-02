<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;
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
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            updated: ScalarCaster::toDateTimeOrNull($data['updated'] ?? null),
            bgcolor: ScalarCaster::toStringOrNull($data['bgcolor'] ?? null),
            color1: ScalarCaster::toStringOrNull($data['color1'] ?? null),
            color2: ScalarCaster::toStringOrNull($data['color2'] ?? null),
            color3: ScalarCaster::toStringOrNull($data['color3'] ?? null),
            currencyCode: ScalarCaster::toStringOrNull($data['currency_code'] ?? null),
            locale: ScalarCaster::toStringOrNull($data['locale'] ?? null),
            netGross: $netGross,
            sepaCreditorId: ScalarCaster::toStringOrNull($data['sepa_creditor_id'] ?? null),
            numberRangeMode: $mode,
            articleNumberPre: ScalarCaster::toStringOrNull($data['article_number_pre'] ?? null),
            articleNumberLength: ScalarCaster::toIntOrNull($data['article_number_length'] ?? null),
            articleNumberNext: ScalarCaster::toIntOrNull($data['article_number_next'] ?? null),
            priceGroups: $priceGroups,
            clientNumberPre: ScalarCaster::toStringOrNull($data['client_number_pre'] ?? null),
            clientNumberLength: ScalarCaster::toIntOrNull($data['client_number_length'] ?? null),
            clientNumberNext: ScalarCaster::toIntOrNull($data['client_number_next'] ?? null),
            invoiceNumberPre: ScalarCaster::toStringOrNull($data['invoice_number_pre'] ?? null),
            invoiceNumberLength: ScalarCaster::toIntOrNull($data['invoice_number_length'] ?? null),
            invoiceNumberNext: ScalarCaster::toIntOrNull($data['invoice_number_next'] ?? null),
            invoiceLabel: ScalarCaster::toStringOrNull($data['invoice_label'] ?? null),
            invoiceIntro: ScalarCaster::toStringOrNull($data['invoice_intro'] ?? null),
            invoiceNote: ScalarCaster::toStringOrNull($data['invoice_note'] ?? null),
            invoiceFilename: ScalarCaster::toStringOrNull($data['invoice_filename'] ?? null),
            dueDays: ScalarCaster::toIntOrNull($data['due_days'] ?? null),
            discountRate: ScalarCaster::toFloatOrNull($data['discount_rate'] ?? null),
            discountDays: ScalarCaster::toIntOrNull($data['discount_days'] ?? null),
            offerNumberPre: ScalarCaster::toStringOrNull($data['offer_number_pre'] ?? null),
            offerNumberLength: ScalarCaster::toIntOrNull($data['offer_number_length'] ?? null),
            offerNumberNext: ScalarCaster::toIntOrNull($data['offer_number_next'] ?? null),
            offerLabel: ScalarCaster::toStringOrNull($data['offer_label'] ?? null),
            offerIntro: ScalarCaster::toStringOrNull($data['offer_intro'] ?? null),
            offerNote: ScalarCaster::toStringOrNull($data['offer_note'] ?? null),
            offerFilename: ScalarCaster::toStringOrNull($data['offer_filename'] ?? null),
            offerValidityDays: ScalarCaster::toIntOrNull($data['offer_validity_days'] ?? null),
            confirmationNumberPre: ScalarCaster::toStringOrNull($data['confirmation_number_pre'] ?? null),
            confirmationNumberLength: ScalarCaster::toIntOrNull($data['confirmation_number_length'] ?? null),
            confirmationNumberNext: ScalarCaster::toIntOrNull($data['confirmation_number_next'] ?? null),
            confirmationLabel: ScalarCaster::toStringOrNull($data['confirmation_label'] ?? null),
            confirmationIntro: ScalarCaster::toStringOrNull($data['confirmation_intro'] ?? null),
            confirmationNote: ScalarCaster::toStringOrNull($data['confirmation_note'] ?? null),
            confirmationFilename: ScalarCaster::toStringOrNull($data['confirmation_filename'] ?? null),
            creditNoteNumberPre: ScalarCaster::toStringOrNull($data['credit_note_number_pre'] ?? null),
            creditNoteNumberLength: ScalarCaster::toIntOrNull($data['credit_note_number_length'] ?? null),
            creditNoteNumberNext: ScalarCaster::toIntOrNull($data['credit_note_number_next'] ?? null),
            creditNoteLabel: ScalarCaster::toStringOrNull($data['credit_note_label'] ?? null),
            creditNoteIntro: ScalarCaster::toStringOrNull($data['credit_note_intro'] ?? null),
            creditNoteNote: ScalarCaster::toStringOrNull($data['credit_note_note'] ?? null),
            creditNoteFilename: ScalarCaster::toStringOrNull($data['credit_note_filename'] ?? null),
            deliveryNoteNumberPre: ScalarCaster::toStringOrNull($data['delivery_note_number_pre'] ?? null),
            deliveryNoteNumberLength: ScalarCaster::toIntOrNull($data['delivery_note_number_length'] ?? null),
            deliveryNoteNumberNext: ScalarCaster::toIntOrNull($data['delivery_note_number_next'] ?? null),
            deliveryNoteLabel: ScalarCaster::toStringOrNull($data['delivery_note_label'] ?? null),
            deliveryNoteIntro: ScalarCaster::toStringOrNull($data['delivery_note_intro'] ?? null),
            deliveryNoteNote: ScalarCaster::toStringOrNull($data['delivery_note_note'] ?? null),
            deliveryNoteFilename: ScalarCaster::toStringOrNull($data['delivery_note_filename'] ?? null),
            reminderFilename: ScalarCaster::toStringOrNull($data['reminder_filename'] ?? null),
            reminderDueDays: ScalarCaster::toIntOrNull($data['reminder_due_days'] ?? null),
            letterLabel: ScalarCaster::toStringOrNull($data['letter_label'] ?? null),
            letterIntro: ScalarCaster::toStringOrNull($data['letter_intro'] ?? null),
            letterFilename: ScalarCaster::toStringOrNull($data['letter_filename'] ?? null),
            templateEngine: $engine,
            printVersion: ScalarCaster::toBoolOrNull($data['print_version'] ?? null),
            defaultEmailSender: ScalarCaster::toStringOrNull($data['default_email_sender'] ?? null),
            bccAddresses: $bcc,
            taxation: ScalarCaster::toStringOrNull($data['taxation'] ?? null),
        );
    }
}
