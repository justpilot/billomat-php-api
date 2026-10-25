<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ListCaster;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\NetGross;
use Justpilot\Billomat\Model\Enum\RecurringAction;
use Justpilot\Billomat\Model\Enum\RecurringCycle;
use Justpilot\Billomat\Model\Enum\SupplyDateType;

use const DATE_ATOM;

/**
 * Repräsentiert eine Abo-Rechnung (Recurring) aus der Billomat-API.
 *
 * Eine Abo-Rechnung erzeugt periodisch (gemäß cycle/cycle_number) neue
 * "echte" Rechnungen. Was bei jedem Lauf geschieht, steuert `action`.
 *
 * Dokumentation:
 * https://www.billomat.com/api/abo-rechnungen/
 *
 * Generiert aus tools/codegen/definitions/recurrings.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class Recurring
{
    /**
     * @param list<array{name: string, rate: float, amount: Money}> $taxes
     * @param list<RecurringItem>                                   $items
     */
    public function __construct(
        /** Interne Billomat-ID der Abo-Rechnung. */
        public ?int $id,
        /** ID des Kunden. */
        public int $clientId,
        /** ID des Kontakts. */
        public ?int $contactId = null,
        /** Erstellungszeitpunkt der Abo-Rechnung. */
        public ?DateTimeImmutable $created = null,
        /**
         * Komplette Adresse.
         *
         * Wird ein leerer Wert übergeben, wird die aktuelle Kunden-Adresse verwendet.
         */
        public ?string $address = null,
        /** Liefer-/Leistungsdatum. */
        public ?DateTimeImmutable $supplyDate = null,
        /** Typ des Liefer-/Leistungsdatums. */
        public ?SupplyDateType $supplyDateType = null,
        /** Tage bis zur Fälligkeit der erzeugten Rechnungen. */
        public ?int $dueDays = null,
        /** Skonto in Prozent. */
        public ?float $discountRate = null,
        /** Skontofrist in Tagen. */
        public ?int $discountDays = null,
        /** Name der Abo-Rechnung (nur intern sichtbar). */
        public ?string $name = null,
        /** Dokumentenüberschrift. */
        public ?string $title = null,
        /** Bezeichnung. */
        public ?string $label = null,
        /** Einleitungstext. */
        public ?string $intro = null,
        /** Anmerkungstext. */
        public ?string $note = null,
        /** Währung (ISO-Währungscode). */
        public ?string $currencyCode = null,
        /** Preisbasis (Brutto- oder Netto-Preise). */
        public ?NetGross $netGross = null,
        /** Währungskurs (für Umrechnung in die Standard-Währung). */
        public ?float $quote = null,
        /** Akzeptierte Zahlungsarten (kommasepariert). */
        public ?string $paymentTypes = null,
        /** Aktion, die bei jedem Lauf ausgeführt wird. */
        public ?RecurringAction $action = null,
        /** Anzahl der Intervall-Einheiten zwischen zwei Läufen. */
        public ?int $cycleNumber = null,
        /** Intervall-Einheit (z. B. monatlich). */
        public ?RecurringCycle $cycle = null,
        /** Stunde, zu der die Rechnung erzeugt wird. */
        public ?int $hour = null,
        /** Startdatum. */
        public ?DateTimeImmutable $startDate = null,
        /** Enddatum. */
        public ?DateTimeImmutable $endDate = null,
        /** Datum der zuletzt erzeugten Rechnung. */
        public ?DateTimeImmutable $lastCreationDate = null,
        /** Datum der nächsten Rechnung. */
        public ?DateTimeImmutable $nextCreationDate = null,
        /** Anzahl der Läufe, nach denen die Abo-Rechnung endet. */
        public ?int $iterations = null,
        /** Anzahl der bisher erzeugten Rechnungen. */
        public ?int $counter = null,
        /** Gesamtbetrag brutto. */
        public ?Money $totalGross = null,
        /** Gesamtbetrag netto. */
        public ?Money $totalNet = null,
        /** Rabatt (absolut oder als Prozentwert, z. B. "10" oder "10%"). */
        public ?string $reduction = null,
        /** Absender-Adresse der E-Mail. */
        public ?string $emailSender = null,
        /** Betreff der E-Mail. */
        public ?string $emailSubject = null,
        /** Text der E-Mail. */
        public ?string $emailMessage = null,
        /** ID der E-Mail-Vorlage. */
        public ?int $emailTemplateId = null,
        /** ID des Freitextes zur Belegung von title, label, intro und note. */
        public ?int $freeTextId = null,
        /** ID der Vorlage für die erzeugten Rechnungen. */
        public ?int $templateId = null,
        /** Steuerzeilen der Abo-Rechnung. */
        public array $taxes = [],
        /** Positionen der Abo-Rechnung (nur bei GET /recurrings/{id}). */
        public array $items = [],
        /** Zeitpunkt der letzten Änderung. */
        public ?DateTimeImmutable $updated = null,
        /** Freies Zusatzfeld (siehe Custom-Meta-Attribute). */
        public ?string $customfield = null,
        /** Summe der Rabatte. */
        public ?Money $totalReduction = null,
        /** Gesamtbetrag netto vor Rabatt. */
        public ?Money $totalNetUnreduced = null,
        /** Gesamtbetrag brutto vor Rabatt. */
        public ?Money $totalGrossUnreduced = null,
        /** Skontobetrag. */
        public ?Money $discountAmount = null,
        /** Status der Abo-Rechnung. */
        public ?string $status = null,
        /** Läuft gerade eine Rechnungserzeugung? */
        public ?bool $inProgress = null,
        /** Rechnungen immer zum Monatsletzten erzeugen? */
        public ?bool $ultimo = null,
        /** Dateiname des PDF-Anhangs. */
        public ?string $emailFilename = null,
        /** E-Mail zusätzlich als BCC an den Absender senden? */
        public ?bool $emailBcc = null,
        /** Brief farbig drucken? */
        public ?bool $letterColor = null,
        /** Brief beidseitig drucken? */
        public ?bool $letterDuplex = null,
        /** Papiergewicht des Briefs in g/m². */
        public ?int $letterPaperWeight = null,
        /** ID des Quell-Angebots. */
        public ?int $offerId = null,
        /** ID der Quell-Auftragsbestätigung. */
        public ?int $confirmationId = null,
    ) {
    }

    /**
     * Hydriert das Model aus einem Billomat-Response-Array.
     *
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ScalarCaster::toIntOrNull($data['id'] ?? null),
            clientId: ScalarCaster::toIntOrNull($data['client_id'] ?? null) ?? 0,
            contactId: ScalarCaster::toIntOrNull($data['contact_id'] ?? null),
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            address: ScalarCaster::toStringOrNull($data['address'] ?? null),
            supplyDate: ScalarCaster::toDateTimeOrNull($data['supply_date'] ?? null),
            supplyDateType: SupplyDateType::tryFrom(ScalarCaster::toStringOrNull($data['supply_date_type'] ?? null) ?? ''),
            dueDays: ScalarCaster::toIntOrNull($data['due_days'] ?? null),
            discountRate: ScalarCaster::toFloatOrNull($data['discount_rate'] ?? null),
            discountDays: ScalarCaster::toIntOrNull($data['discount_days'] ?? null),
            name: ScalarCaster::toStringOrNull($data['name'] ?? null),
            title: ScalarCaster::toStringOrNull($data['title'] ?? null),
            label: ScalarCaster::toStringOrNull($data['label'] ?? null),
            intro: ScalarCaster::toStringOrNull($data['intro'] ?? null),
            note: ScalarCaster::toStringOrNull($data['note'] ?? null),
            currencyCode: ScalarCaster::toStringOrNull($data['currency_code'] ?? null),
            netGross: NetGross::tryFrom(ScalarCaster::toStringOrNull($data['net_gross'] ?? null) ?? ''),
            quote: ScalarCaster::toFloatOrNull($data['quote'] ?? null),
            paymentTypes: ScalarCaster::toStringOrNull($data['payment_types'] ?? null),
            action: RecurringAction::tryFrom(ScalarCaster::toStringOrNull($data['action'] ?? null) ?? ''),
            cycleNumber: ScalarCaster::toIntOrNull($data['cycle_number'] ?? null),
            cycle: RecurringCycle::tryFrom(ScalarCaster::toStringOrNull($data['cycle'] ?? null) ?? ''),
            hour: ScalarCaster::toIntOrNull($data['hour'] ?? null),
            startDate: ScalarCaster::toDateTimeOrNull($data['start_date'] ?? null),
            endDate: ScalarCaster::toDateTimeOrNull($data['end_date'] ?? null),
            lastCreationDate: ScalarCaster::toDateTimeOrNull($data['last_creation_date'] ?? null),
            nextCreationDate: ScalarCaster::toDateTimeOrNull($data['next_creation_date'] ?? null),
            iterations: ScalarCaster::toIntOrNull($data['iterations'] ?? null),
            counter: ScalarCaster::toIntOrNull($data['counter'] ?? null),
            totalGross: ScalarCaster::toMoneyOrNull($data['total_gross'] ?? null),
            totalNet: ScalarCaster::toMoneyOrNull($data['total_net'] ?? null),
            reduction: ScalarCaster::toStringOrNull($data['reduction'] ?? null),
            emailSender: ScalarCaster::toStringOrNull($data['email_sender'] ?? null),
            emailSubject: ScalarCaster::toStringOrNull($data['email_subject'] ?? null),
            emailMessage: ScalarCaster::toStringOrNull($data['email_message'] ?? null),
            emailTemplateId: ScalarCaster::toIntOrNull($data['email_template_id'] ?? null),
            freeTextId: ScalarCaster::toIntOrNull($data['free_text_id'] ?? null),
            templateId: ScalarCaster::toIntOrNull($data['template_id'] ?? null),
            taxes: ListCaster::toTaxList($data['taxes']['tax'] ?? null),
            items: ListCaster::toList($data['recurring-items']['recurring-item'] ?? null, 'id', RecurringItem::fromArray(...)),
            updated: ScalarCaster::toDateTimeOrNull($data['updated'] ?? null),
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
            totalReduction: ScalarCaster::toMoneyOrNull($data['total_reduction'] ?? null),
            totalNetUnreduced: ScalarCaster::toMoneyOrNull($data['total_net_unreduced'] ?? null),
            totalGrossUnreduced: ScalarCaster::toMoneyOrNull($data['total_gross_unreduced'] ?? null),
            discountAmount: ScalarCaster::toMoneyOrNull($data['discount_amount'] ?? null),
            status: ScalarCaster::toStringOrNull($data['status'] ?? null),
            inProgress: ScalarCaster::toBoolOrNull($data['in_progress'] ?? null),
            ultimo: ScalarCaster::toBoolOrNull($data['ultimo'] ?? null),
            emailFilename: ScalarCaster::toStringOrNull($data['email_filename'] ?? null),
            emailBcc: ScalarCaster::toBoolOrNull($data['email_bcc'] ?? null),
            letterColor: ScalarCaster::toBoolOrNull($data['letter_color'] ?? null),
            letterDuplex: ScalarCaster::toBoolOrNull($data['letter_duplex'] ?? null),
            letterPaperWeight: ScalarCaster::toIntOrNull($data['letter_paper_weight'] ?? null),
            offerId: ScalarCaster::toIntOrNull($data['offer_id'] ?? null),
            confirmationId: ScalarCaster::toIntOrNull($data['confirmation_id'] ?? null),
        );
    }

    /**
     * Exportiert das Model als Array mit Billomat-Feldnamen.
     *
     * Praktisch für Debugging oder Log-Ausgaben.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [
            'id' => $this->id,
            'client_id' => $this->clientId,
            'contact_id' => $this->contactId,
            'created' => $this->created?->format(DATE_ATOM),
            'address' => $this->address,
            'supply_date' => $this->supplyDate?->format('Y-m-d'),
            'supply_date_type' => $this->supplyDateType?->value,
            'due_days' => $this->dueDays,
            'discount_rate' => $this->discountRate,
            'discount_days' => $this->discountDays,
            'name' => $this->name,
            'title' => $this->title,
            'label' => $this->label,
            'intro' => $this->intro,
            'note' => $this->note,
            'currency_code' => $this->currencyCode,
            'net_gross' => $this->netGross?->value,
            'quote' => $this->quote,
            'payment_types' => $this->paymentTypes,
            'action' => $this->action?->value,
            'cycle_number' => $this->cycleNumber,
            'cycle' => $this->cycle?->value,
            'hour' => $this->hour,
            'start_date' => $this->startDate?->format('Y-m-d'),
            'end_date' => $this->endDate?->format('Y-m-d'),
            'last_creation_date' => $this->lastCreationDate?->format('Y-m-d'),
            'next_creation_date' => $this->nextCreationDate?->format('Y-m-d'),
            'iterations' => $this->iterations,
            'counter' => $this->counter,
            'total_gross' => $this->totalGross?->toString(),
            'total_net' => $this->totalNet?->toString(),
            'reduction' => $this->reduction,
            'email_sender' => $this->emailSender,
            'email_subject' => $this->emailSubject,
            'email_message' => $this->emailMessage,
            'email_template_id' => $this->emailTemplateId,
            'free_text_id' => $this->freeTextId,
            'template_id' => $this->templateId,
            'updated' => $this->updated?->format(DATE_ATOM),
            'customfield' => $this->customfield,
            'total_reduction' => $this->totalReduction?->toString(),
            'total_net_unreduced' => $this->totalNetUnreduced?->toString(),
            'total_gross_unreduced' => $this->totalGrossUnreduced?->toString(),
            'discount_amount' => $this->discountAmount?->toString(),
            'status' => $this->status,
            'in_progress' => $this->inProgress,
            'ultimo' => $this->ultimo,
            'email_filename' => $this->emailFilename,
            'email_bcc' => $this->emailBcc,
            'letter_color' => $this->letterColor,
            'letter_duplex' => $this->letterDuplex,
            'letter_paper_weight' => $this->letterPaperWeight,
            'offer_id' => $this->offerId,
            'confirmation_id' => $this->confirmationId,
        ];

        if ([] !== $this->taxes) {
            $data['taxes'] = [
                'tax' => array_map(static fn (array $tax): array => [...$tax, 'amount' => $tax['amount']->toString()], $this->taxes),
            ];
        }

        if ([] !== $this->items) {
            $data['recurring-items'] = [
                'recurring-item' => array_map(static fn (RecurringItem $item): array => $item->toArray(), $this->items),
            ];
        }

        return $data;
    }
}
