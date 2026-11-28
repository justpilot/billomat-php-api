<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use DateTimeImmutable;
use Justpilot\Billomat\Exception\UnexpectedResponseException;
use Justpilot\Billomat\Internal\ListCaster;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\NetGross;
use Justpilot\Billomat\Model\Enum\PaymentType;
use Justpilot\Billomat\Model\Enum\SupplyDateType;

/**
 * Typisierter Payload für POST /invoices gemäß Billomat-API.
 *
 * Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren ignoriert,
 * sodass Billomat seine Defaults verwenden kann.
 * Positionen lassen sich beim Anlegen über `items:` bzw. withItem() direkt mitsenden.
 *
 * Doku:
 * https://www.billomat.com/en/api/invoices/
 *
 * Generiert aus tools/codegen/definitions/invoices.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class InvoiceCreateOptions implements WriteOptions
{
    public function __construct(
        /**
         * ID des Kunden.
         *
         * Billomat-Feld: client_id
         * Typ: INT
         */
        public int $clientId,
        /**
         * ID des Kontakts.
         *
         * Billomat-Feld: contact_id
         * Typ: INT
         */
        public ?int $contactId = null,
        /**
         * ID der Lieferadresse des Kunden.
         *
         * Billomat-Feld: client_delivery_address_id
         * Typ: INT
         */
        public ?int $clientDeliveryAddressId = null,
        /**
         * Laufende Nummer (ohne Präfix).
         *
         * Billomat-Feld: number
         * Typ: INT
         * Default: nächste freie Nummer
         */
        public ?int $number = null,
        /**
         * Präfix der Rechnungsnummer (z. B. "RE").
         *
         * Billomat-Feld: number_pre
         * Typ: ALNUM
         * Default: Wert aus Einstellungen
         */
        public ?string $numberPre = null,
        /**
         * Mindestlänge der Rechnungsnummer (wird mit führenden Nullen aufgefüllt).
         *
         * Billomat-Feld: number_length
         * Typ: INT
         * Default: Wert aus den Einstellungen
         */
        public ?int $numberLength = null,
        /**
         * Rechnungsdatum.
         *
         * Format: YYYY-MM-DD
         *
         * Billomat-Feld: date
         * Typ: DATE
         * Default: heute
         */
        public ?DateTimeImmutable $date = null,
        /**
         * Liefer-/Leistungsdatum.
         *
         * Format: YYYY-MM-DD
         *
         * Billomat-Feld: supply_date
         * Typ: DATE
         *
         * @deprecated wird von Billomat nicht mehr verarbeitet; stattdessen supplyDateStart/supplyDateEnd verwenden
         */
        public ?DateTimeImmutable $supplyDate = null,
        /**
         * Typ des Liefer-/Leistungsdatums.
         *
         * Billomat-Feld: supply_date_type
         * Typ: ALNUM („SUPPLY_DATE“, „DELIVERY_DATE“, „SUPPLY_TEXT“, „DELIVERY_TEXT“)
         *
         * @deprecated wird von Billomat nicht mehr verarbeitet; stattdessen supplyDateStart/supplyDateEnd verwenden
         */
        public ?SupplyDateType $supplyDateType = null,
        /**
         * Beginn des Liefer-/Leistungszeitraums.
         *
         * Format: YYYY-MM-DD
         *
         * Billomat-Feld: supply_date_start
         * Typ: DATE
         */
        public ?DateTimeImmutable $supplyDateStart = null,
        /**
         * Ende des Liefer-/Leistungszeitraums.
         *
         * Format: YYYY-MM-DD
         *
         * Billomat-Feld: supply_date_end
         * Typ: DATE
         */
        public ?DateTimeImmutable $supplyDateEnd = null,
        /**
         * Fälligkeitsdatum.
         *
         * Format: YYYY-MM-DD
         *
         * Billomat-Feld: due_date
         * Typ: DATE
         * Default: date + due_days
         */
        public ?DateTimeImmutable $dueDate = null,
        /**
         * Tage bis zur Fälligkeit.
         *
         * Billomat-Feld: due_days
         * Typ: INT
         * Default: Fälligkeit in Tagen aus Einstellungen
         */
        public ?int $dueDays = null,
        /**
         * Vollständige Rechnungsadresse (formatiert).
         *
         * Billomat-Feld: address
         * Typ: ALNUM
         * Default: Adresse des Kunden
         */
        public ?string $address = null,
        /**
         * Skonto in Prozent.
         *
         * Billomat-Feld: discount_rate
         * Typ: INT
         * Default: Wert aus Einstellungen
         */
        public ?int $discountRate = null,
        /**
         * Skontodatum.
         *
         * Format: YYYY-MM-DD
         *
         * Billomat-Feld: discount_date
         * Typ: DATE
         * Default: date + discount_days
         */
        public ?DateTimeImmutable $discountDate = null,
        /**
         * Skontofrist in Tagen.
         *
         * Billomat-Feld: discount_days
         * Typ: INT
         * Default: Skontozeitraum in Tagen aus Einstellungen
         */
        public ?int $discountDays = null,
        /**
         * Dokumentenüberschrift.
         *
         * Billomat-Feld: title
         * Typ: ALNUM
         */
        public ?string $title = null,
        /**
         * Bezeichnung.
         *
         * Billomat-Feld: label
         * Typ: ALNUM
         */
        public ?string $label = null,
        /**
         * Einleitungstext.
         *
         * Billomat-Feld: intro
         * Typ: ALNUM
         * Default: Wert aus Einstellungen
         */
        public ?string $intro = null,
        /**
         * Anmerkungstext.
         *
         * Billomat-Feld: note
         * Typ: ALNUM
         * Default: Wert aus Einstellungen
         */
        public ?string $note = null,
        /**
         * Preisbasis (NET, GROSS oder SETTINGS).
         *
         * Billomat-Feld: net_gross
         * Typ: ALNUM („NET“, „GROSS“)
         * Default: Wert aus den Einstellungen
         */
        public ?NetGross $netGross = null,
        /**
         * Rabatt (absolut oder als Prozentwert, z. B. "10" oder "10%").
         *
         * Billomat-Feld: reduction
         * Typ: ALNUM
         */
        public ?string $reduction = null,
        /**
         * Währung (ISO-Währungscode, z. B. "EUR").
         *
         * Billomat-Feld: currency_code
         * Typ: ISO-Währungscode
         * Default: Standard-Währung
         */
        public ?string $currencyCode = null,
        /**
         * Währungskurs (für Umrechnung in die Standard-Währung).
         *
         * Billomat-Feld: quote
         * Typ: FLOAT
         * Default: 1.0000
         */
        public ?float $quote = null,
        /**
         * ID der korrigierten Rechnung (bei Korrekturrechnung).
         *
         * Billomat-Feld: invoice_id
         * Typ: INT
         */
        public ?int $invoiceId = null,
        /**
         * ID des Angebots, aus dem die Rechnung entstanden ist.
         *
         * Billomat-Feld: offer_id
         * Typ: INT
         */
        public ?int $offerId = null,
        /**
         * ID der Auftragsbestätigung, aus der die Rechnung entstanden ist.
         *
         * Billomat-Feld: confirmation_id
         * Typ: INT
         */
        public ?int $confirmationId = null,
        /**
         * ID der Abo-Rechnung, aus der die Rechnung entstanden ist.
         *
         * Billomat-Feld: recurring_id
         * Typ: INT
         */
        public ?int $recurringId = null,
        /**
         * Akzeptierte Zahlungsarten.
         *
         * Billomat überträgt sie kommasepariert, z. B. "BANK_TRANSFER,CASH".
         *
         * Billomat-Feld: payment_types
         * Typ: ALNUM
         * Default: Wert aus den Einstellungen
         *
         * @var list<PaymentType>
         */
        public array $paymentTypes = [],
        /**
         * IBAN einer vom Account abweichenden Bankverbindung für diese Rechnung.
         *
         * Erscheint auf dem PDF und in der E-Rechnung statt der Bankverbindung aus den Einstellungen.
         * Billomat prüft die Prüfsumme; Pflicht, sobald eines der anderen bank_*-Felder gesetzt ist.
         * Korrekturrechnungen übernehmen die Bankverbindung der korrigierten Rechnung.
         *
         * Billomat-Feld: bank_iban
         * Typ: ALNUM (IBAN)
         */
        public ?string $bankIban = null,
        /**
         * BIC der abweichenden Bankverbindung.
         *
         * Billomat-Feld: bank_swift
         * Typ: ALNUM (BIC)
         */
        public ?string $bankSwift = null,
        /**
         * Kontoinhaber der abweichenden Bankverbindung (max. 250 Zeichen).
         *
         * Billomat-Feld: bank_account_owner
         * Typ: ALNUM (max. 250)
         */
        public ?string $bankAccountOwner = null,
        /**
         * Name der Bank der abweichenden Bankverbindung (max. 100 Zeichen).
         *
         * Billomat-Feld: bank_name
         * Typ: ALNUM (max. 100)
         */
        public ?string $bankName = null,
        /**
         * Projektreferenz für E-Rechnungen (max. 255 Zeichen).
         *
         * Billomat-Feld: project_reference
         * Typ: ALNUM (max. 255)
         */
        public ?string $projectReference = null,
        /**
         * Vertragsreferenz für E-Rechnungen (max. 255 Zeichen).
         *
         * Billomat-Feld: contract_reference
         * Typ: ALNUM (max. 255)
         */
        public ?string $contractReference = null,
        /**
         * ID der Vorlage, mit der die Rechnung abgeschlossen wird.
         *
         * Billomat-Feld: template_id
         * Typ: INT
         * Default: ID der Standardvorlage
         */
        public ?int $templateId = null,
        /**
         * Bestellreferenz bzw. Auftragsnummer des Käufers für E-Rechnungen (max. 32 Zeichen).
         *
         * Nur verfügbar, wenn facturx_fields_enabled beim eigenen Unternehmen (`$billomat->clients->getMyself()`) aktiv ist.
         *
         * Billomat-Feld: facturx_reference
         * Typ: ALNUM (max. 32)
         */
        public ?string $facturxReference = null,
        /**
         * ID des Freitextes zur Belegung von title, label, intro und note.
         *
         * Billomat-Feld: free_text_id
         * Typ: INT
         */
        public ?int $freeTextId = null,
        /**
         * Positionen, die beim Anlegen mitgesendet werden.
         *
         * @var list<InvoiceItemCreateOptions>
         */
        public array $items = [],
    ) {
    }

    /** Kopie mit geändertem `clientId`. */
    public function withClientId(int $clientId): self
    {
        return clone($this, ['clientId' => $clientId]);
    }

    /** Kopie mit geändertem `contactId`. */
    public function withContactId(?int $contactId): self
    {
        return clone($this, ['contactId' => $contactId]);
    }

    /** Kopie mit geändertem `clientDeliveryAddressId`. */
    public function withClientDeliveryAddressId(?int $clientDeliveryAddressId): self
    {
        return clone($this, ['clientDeliveryAddressId' => $clientDeliveryAddressId]);
    }

    /** Kopie mit geändertem `number`. */
    public function withNumber(?int $number): self
    {
        return clone($this, ['number' => $number]);
    }

    /** Kopie mit geändertem `numberPre`. */
    public function withNumberPre(?string $numberPre): self
    {
        return clone($this, ['numberPre' => $numberPre]);
    }

    /** Kopie mit geändertem `numberLength`. */
    public function withNumberLength(?int $numberLength): self
    {
        return clone($this, ['numberLength' => $numberLength]);
    }

    /** Kopie mit geändertem `date`. */
    public function withDate(?DateTimeImmutable $date): self
    {
        return clone($this, ['date' => $date]);
    }

    /** Kopie mit geändertem `supplyDate`. */
    public function withSupplyDate(?DateTimeImmutable $supplyDate): self
    {
        return clone($this, ['supplyDate' => $supplyDate]);
    }

    /** Kopie mit geändertem `supplyDateType`. */
    public function withSupplyDateType(?SupplyDateType $supplyDateType): self
    {
        return clone($this, ['supplyDateType' => $supplyDateType]);
    }

    /** Kopie mit geändertem `supplyDateStart`. */
    public function withSupplyDateStart(?DateTimeImmutable $supplyDateStart): self
    {
        return clone($this, ['supplyDateStart' => $supplyDateStart]);
    }

    /** Kopie mit geändertem `supplyDateEnd`. */
    public function withSupplyDateEnd(?DateTimeImmutable $supplyDateEnd): self
    {
        return clone($this, ['supplyDateEnd' => $supplyDateEnd]);
    }

    /** Kopie mit geändertem `dueDate`. */
    public function withDueDate(?DateTimeImmutable $dueDate): self
    {
        return clone($this, ['dueDate' => $dueDate]);
    }

    /** Kopie mit geändertem `dueDays`. */
    public function withDueDays(?int $dueDays): self
    {
        return clone($this, ['dueDays' => $dueDays]);
    }

    /** Kopie mit geändertem `address`. */
    public function withAddress(?string $address): self
    {
        return clone($this, ['address' => $address]);
    }

    /** Kopie mit geändertem `discountRate`. */
    public function withDiscountRate(?int $discountRate): self
    {
        return clone($this, ['discountRate' => $discountRate]);
    }

    /** Kopie mit geändertem `discountDate`. */
    public function withDiscountDate(?DateTimeImmutable $discountDate): self
    {
        return clone($this, ['discountDate' => $discountDate]);
    }

    /** Kopie mit geändertem `discountDays`. */
    public function withDiscountDays(?int $discountDays): self
    {
        return clone($this, ['discountDays' => $discountDays]);
    }

    /** Kopie mit geändertem `title`. */
    public function withTitle(?string $title): self
    {
        return clone($this, ['title' => $title]);
    }

    /** Kopie mit geändertem `label`. */
    public function withLabel(?string $label): self
    {
        return clone($this, ['label' => $label]);
    }

    /** Kopie mit geändertem `intro`. */
    public function withIntro(?string $intro): self
    {
        return clone($this, ['intro' => $intro]);
    }

    /** Kopie mit geändertem `note`. */
    public function withNote(?string $note): self
    {
        return clone($this, ['note' => $note]);
    }

    /** Kopie mit geändertem `netGross`. */
    public function withNetGross(?NetGross $netGross): self
    {
        return clone($this, ['netGross' => $netGross]);
    }

    /** Kopie mit geändertem `reduction`. */
    public function withReduction(?string $reduction): self
    {
        return clone($this, ['reduction' => $reduction]);
    }

    /** Kopie mit geändertem `currencyCode`. */
    public function withCurrencyCode(?string $currencyCode): self
    {
        return clone($this, ['currencyCode' => $currencyCode]);
    }

    /** Kopie mit geändertem `quote`. */
    public function withQuote(?float $quote): self
    {
        return clone($this, ['quote' => $quote]);
    }

    /** Kopie mit geändertem `invoiceId`. */
    public function withInvoiceId(?int $invoiceId): self
    {
        return clone($this, ['invoiceId' => $invoiceId]);
    }

    /** Kopie mit geändertem `offerId`. */
    public function withOfferId(?int $offerId): self
    {
        return clone($this, ['offerId' => $offerId]);
    }

    /** Kopie mit geändertem `confirmationId`. */
    public function withConfirmationId(?int $confirmationId): self
    {
        return clone($this, ['confirmationId' => $confirmationId]);
    }

    /** Kopie mit geändertem `recurringId`. */
    public function withRecurringId(?int $recurringId): self
    {
        return clone($this, ['recurringId' => $recurringId]);
    }

    /**
     * Kopie mit geändertem `paymentTypes`.
     *
     * @param list<PaymentType> $paymentTypes
     */
    public function withPaymentTypes(array $paymentTypes): self
    {
        return clone($this, ['paymentTypes' => $paymentTypes]);
    }

    /** Kopie mit geändertem `bankIban`. */
    public function withBankIban(?string $bankIban): self
    {
        return clone($this, ['bankIban' => $bankIban]);
    }

    /** Kopie mit geändertem `bankSwift`. */
    public function withBankSwift(?string $bankSwift): self
    {
        return clone($this, ['bankSwift' => $bankSwift]);
    }

    /** Kopie mit geändertem `bankAccountOwner`. */
    public function withBankAccountOwner(?string $bankAccountOwner): self
    {
        return clone($this, ['bankAccountOwner' => $bankAccountOwner]);
    }

    /** Kopie mit geändertem `bankName`. */
    public function withBankName(?string $bankName): self
    {
        return clone($this, ['bankName' => $bankName]);
    }

    /** Kopie mit geändertem `projectReference`. */
    public function withProjectReference(?string $projectReference): self
    {
        return clone($this, ['projectReference' => $projectReference]);
    }

    /** Kopie mit geändertem `contractReference`. */
    public function withContractReference(?string $contractReference): self
    {
        return clone($this, ['contractReference' => $contractReference]);
    }

    /** Kopie mit geändertem `templateId`. */
    public function withTemplateId(?int $templateId): self
    {
        return clone($this, ['templateId' => $templateId]);
    }

    /** Kopie mit geändertem `facturxReference`. */
    public function withFacturxReference(?string $facturxReference): self
    {
        return clone($this, ['facturxReference' => $facturxReference]);
    }

    /** Kopie mit geändertem `freeTextId`. */
    public function withFreeTextId(?int $freeTextId): self
    {
        return clone($this, ['freeTextId' => $freeTextId]);
    }

    /** Kopie mit einer zusätzlichen Position. */
    public function withItem(InvoiceItemCreateOptions $item): self
    {
        return clone($this, ['items' => [...$this->items, $item]]);
    }

    /**
     * Baut die Options aus einem Datensatz mit Billomat-Feldnamen.
     *
     * Grundlage für {@see InvoicesApi::preset()}: Billomat liefert den Rechnungsentwurf mit diesen Feldnamen.
     *
     * Felder, die die Options nicht kennen (Summen, Status, …), fallen weg; leere Werte bleiben null.
     *
     * @param array<string,mixed> $data
     *
     * @throws UnexpectedResponseException wenn ein Pflichtfeld fehlt
     */
    public static function fromArray(array $data): self
    {
        return new self(
            clientId: ScalarCaster::required(ScalarCaster::toIntOrNull($data['client_id'] ?? null), self::class, 'client_id'),
            contactId: ScalarCaster::toIntOrNull($data['contact_id'] ?? null),
            clientDeliveryAddressId: ScalarCaster::toIntOrNull($data['client_delivery_address_id'] ?? null),
            number: ScalarCaster::toIntOrNull($data['number'] ?? null),
            numberPre: ScalarCaster::toStringOrNull($data['number_pre'] ?? null),
            numberLength: ScalarCaster::toIntOrNull($data['number_length'] ?? null),
            date: ScalarCaster::toDateTimeOrNull($data['date'] ?? null),
            supplyDate: ScalarCaster::toDateTimeOrNull($data['supply_date'] ?? null),
            supplyDateType: SupplyDateType::tryFrom(ScalarCaster::toStringOrNull($data['supply_date_type'] ?? null) ?? ''),
            supplyDateStart: ScalarCaster::toDateTimeOrNull($data['supply_date_start'] ?? null),
            supplyDateEnd: ScalarCaster::toDateTimeOrNull($data['supply_date_end'] ?? null),
            dueDate: ScalarCaster::toDateTimeOrNull($data['due_date'] ?? null),
            dueDays: ScalarCaster::toIntOrNull($data['due_days'] ?? null),
            address: ScalarCaster::toStringOrNull($data['address'] ?? null),
            discountRate: ScalarCaster::toIntOrNull($data['discount_rate'] ?? null),
            discountDate: ScalarCaster::toDateTimeOrNull($data['discount_date'] ?? null),
            discountDays: ScalarCaster::toIntOrNull($data['discount_days'] ?? null),
            title: ScalarCaster::toStringOrNull($data['title'] ?? null),
            label: ScalarCaster::toStringOrNull($data['label'] ?? null),
            intro: ScalarCaster::toStringOrNull($data['intro'] ?? null),
            note: ScalarCaster::toStringOrNull($data['note'] ?? null),
            netGross: NetGross::tryFrom(ScalarCaster::toStringOrNull($data['net_gross'] ?? null) ?? ''),
            reduction: ScalarCaster::toStringOrNull($data['reduction'] ?? null),
            currencyCode: ScalarCaster::toStringOrNull($data['currency_code'] ?? null),
            quote: ScalarCaster::toFloatOrNull($data['quote'] ?? null),
            invoiceId: ScalarCaster::toIntOrNull($data['invoice_id'] ?? null),
            offerId: ScalarCaster::toIntOrNull($data['offer_id'] ?? null),
            confirmationId: ScalarCaster::toIntOrNull($data['confirmation_id'] ?? null),
            recurringId: ScalarCaster::toIntOrNull($data['recurring_id'] ?? null),
            paymentTypes: ListCaster::toEnumList($data['payment_types'] ?? null, PaymentType::class),
            bankIban: ScalarCaster::toStringOrNull($data['bank_iban'] ?? null),
            bankSwift: ScalarCaster::toStringOrNull($data['bank_swift'] ?? null),
            bankAccountOwner: ScalarCaster::toStringOrNull($data['bank_account_owner'] ?? null),
            bankName: ScalarCaster::toStringOrNull($data['bank_name'] ?? null),
            projectReference: ScalarCaster::toStringOrNull($data['project_reference'] ?? null),
            contractReference: ScalarCaster::toStringOrNull($data['contract_reference'] ?? null),
            templateId: ScalarCaster::toIntOrNull($data['template_id'] ?? null),
            facturxReference: ScalarCaster::toStringOrNull($data['facturx_reference'] ?? null),
            freeTextId: ScalarCaster::toIntOrNull($data['free_text_id'] ?? null),
        );
    }

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = array_filter([
            'client_id' => $this->clientId,
            'contact_id' => $this->contactId,
            'client_delivery_address_id' => $this->clientDeliveryAddressId,
            'address' => $this->address,
            'number_pre' => $this->numberPre,
            'number' => $this->number,
            'number_length' => $this->numberLength,
            'date' => $this->date?->format('Y-m-d'),
            'supply_date_start' => $this->supplyDateStart?->format('Y-m-d'),
            'supply_date_end' => $this->supplyDateEnd?->format('Y-m-d'),
            'supply_date' => $this->supplyDate?->format('Y-m-d'),
            'supply_date_type' => $this->supplyDateType?->value,
            'due_days' => $this->dueDays,
            'due_date' => $this->dueDate?->format('Y-m-d'),
            'discount_rate' => $this->discountRate,
            'discount_days' => $this->discountDays,
            'discount_date' => $this->discountDate?->format('Y-m-d'),
            'title' => $this->title,
            'label' => $this->label,
            'intro' => $this->intro,
            'note' => $this->note,
            'reduction' => $this->reduction,
            'currency_code' => $this->currencyCode,
            'net_gross' => $this->netGross?->value,
            'quote' => $this->quote,
            'payment_types' => [] === $this->paymentTypes ? null : implode(',', array_map(static fn (PaymentType $v): string => $v->value, $this->paymentTypes)),
            'bank_iban' => $this->bankIban,
            'bank_swift' => $this->bankSwift,
            'bank_account_owner' => $this->bankAccountOwner,
            'bank_name' => $this->bankName,
            'facturx_reference' => $this->facturxReference,
            'project_reference' => $this->projectReference,
            'contract_reference' => $this->contractReference,
            'invoice_id' => $this->invoiceId,
            'offer_id' => $this->offerId,
            'confirmation_id' => $this->confirmationId,
            'recurring_id' => $this->recurringId,
            'free_text_id' => $this->freeTextId,
            'template_id' => $this->templateId,
        ], static fn (mixed $v): bool => null !== $v);

        if ([] !== $this->items) {
            $data['invoice-items'] = [
                'invoice-item' => array_map(static fn (InvoiceItemCreateOptions $item): array => $item->toArray(), $this->items),
            ];
        }

        return $data;
    }
}
