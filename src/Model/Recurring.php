<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\NetGross;
use Justpilot\Billomat\Model\Enum\RecurringAction;
use Justpilot\Billomat\Model\Enum\RecurringCycle;
use Justpilot\Billomat\Model\Enum\SupplyDateType;

use const DATE_ATOM;

/**
 * Repräsentiert eine Abo-Rechnung (Recurring) aus der Billomat-API.
 *
 * Doku: https://www.billomat.com/api/abo-rechnungen/
 *
 * Eine Abo-Rechnung erzeugt periodisch (gemäß cycle/cycle_number) neue
 * "echte" Rechnungen. Was bei jedem Lauf geschieht, steuert `action`.
 */
final readonly class Recurring
{
    /**
     * @param list<array{name: string, rate: float, amount: float}> $taxes
     * @param list<RecurringItem>                                   $items
     */
    public function __construct(
        public ?int $id,
        public int $clientId,
        public ?int $contactId = null,
        public ?DateTimeImmutable $created = null,
        public ?string $address = null,
        public ?DateTimeImmutable $supplyDate = null,
        public ?SupplyDateType $supplyDateType = null,
        public ?int $dueDays = null,
        public ?float $discountRate = null,
        public ?int $discountDays = null,
        public ?string $name = null,
        public ?string $numberPre = null,
        public ?string $title = null,
        public ?string $label = null,
        public ?string $intro = null,
        public ?string $note = null,
        public ?string $currencyCode = null,
        public ?NetGross $netGross = null,
        public ?float $quote = null,
        public ?string $paymentTypes = null,
        public ?RecurringAction $action = null,
        public ?int $cycleNumber = null,
        public ?RecurringCycle $cycle = null,
        public ?int $hour = null,
        public ?DateTimeImmutable $startDate = null,
        public ?DateTimeImmutable $endDate = null,
        public ?DateTimeImmutable $lastCreationDate = null,
        public ?DateTimeImmutable $nextCreationDate = null,
        public ?int $iterations = null,
        public ?int $counter = null,
        public ?float $totalGross = null,
        public ?float $totalNet = null,
        public ?string $reduction = null,
        public ?string $emailSender = null,
        public ?string $emailSubject = null,
        public ?string $emailMessage = null,
        public ?int $emailTemplateId = null,
        public ?int $freeTextId = null,
        public ?int $templateId = null,
        public array $taxes = [],
        public array $items = [],
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $taxes = [];
        if (isset($data['taxes']['tax'])) {
            $rawTaxes = $data['taxes']['tax'];

            if (isset($rawTaxes['name'])) {
                $rawTaxes = [$rawTaxes];
            }

            if (\is_array($rawTaxes)) {
                foreach ($rawTaxes as $taxRow) {
                    if (!\is_array($taxRow)) {
                        continue;
                    }

                    $taxes[] = [
                        'name' => (string) ($taxRow['name'] ?? ''),
                        'rate' => isset($taxRow['rate']) ? (float) $taxRow['rate'] : 0.0,
                        'amount' => isset($taxRow['amount']) ? (float) $taxRow['amount'] : 0.0,
                    ];
                }
            }
        }

        $items = [];
        if (isset($data['recurring-items']['recurring-item'])) {
            $rawItems = $data['recurring-items']['recurring-item'];

            if (isset($rawItems['id'])) {
                $rawItems = [$rawItems];
            }

            if (\is_array($rawItems)) {
                $items = array_map(RecurringItem::fromArray(...), $rawItems);
            }
        }

        return new self(
            id: ScalarCaster::toIntOrNull($data['id'] ?? null),
            clientId: (int) ($data['client_id'] ?? 0),
            contactId: ScalarCaster::toIntOrNull($data['contact_id'] ?? null),
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            address: ScalarCaster::toStringOrNull($data['address'] ?? null),
            supplyDate: ScalarCaster::toDateTimeOrNull($data['supply_date'] ?? null),
            supplyDateType: isset($data['supply_date_type'])
                ? SupplyDateType::tryFrom((string) $data['supply_date_type'])
                : null,
            dueDays: ScalarCaster::toIntOrNull($data['due_days'] ?? null),
            discountRate: ScalarCaster::toFloatOrNull($data['discount_rate'] ?? null),
            discountDays: ScalarCaster::toIntOrNull($data['discount_days'] ?? null),
            name: ScalarCaster::toStringOrNull($data['name'] ?? null),
            numberPre: ScalarCaster::toStringOrNull($data['number_pre'] ?? null),
            title: ScalarCaster::toStringOrNull($data['title'] ?? null),
            label: ScalarCaster::toStringOrNull($data['label'] ?? null),
            intro: ScalarCaster::toStringOrNull($data['intro'] ?? null),
            note: ScalarCaster::toStringOrNull($data['note'] ?? null),
            currencyCode: ScalarCaster::toStringOrNull($data['currency_code'] ?? null),
            netGross: isset($data['net_gross'])
                ? NetGross::tryFrom((string) $data['net_gross'])
                : null,
            quote: ScalarCaster::toFloatOrNull($data['quote'] ?? null),
            paymentTypes: ScalarCaster::toStringOrNull($data['payment_types'] ?? null),
            action: isset($data['action'])
                ? RecurringAction::tryFrom((string) $data['action'])
                : null,
            cycleNumber: ScalarCaster::toIntOrNull($data['cycle_number'] ?? null),
            cycle: isset($data['cycle'])
                ? RecurringCycle::tryFrom((string) $data['cycle'])
                : null,
            hour: ScalarCaster::toIntOrNull($data['hour'] ?? null),
            startDate: ScalarCaster::toDateTimeOrNull($data['start_date'] ?? null),
            endDate: ScalarCaster::toDateTimeOrNull($data['end_date'] ?? null),
            lastCreationDate: ScalarCaster::toDateTimeOrNull($data['last_creation_date'] ?? null),
            nextCreationDate: ScalarCaster::toDateTimeOrNull($data['next_creation_date'] ?? null),
            iterations: ScalarCaster::toIntOrNull($data['iterations'] ?? null),
            counter: ScalarCaster::toIntOrNull($data['counter'] ?? null),
            totalGross: ScalarCaster::toFloatOrNull($data['total_gross'] ?? null),
            totalNet: ScalarCaster::toFloatOrNull($data['total_net'] ?? null),
            reduction: ScalarCaster::toStringOrNull($data['reduction'] ?? null),
            emailSender: ScalarCaster::toStringOrNull($data['email_sender'] ?? null),
            emailSubject: ScalarCaster::toStringOrNull($data['email_subject'] ?? null),
            emailMessage: ScalarCaster::toStringOrNull($data['email_message'] ?? null),
            emailTemplateId: ScalarCaster::toIntOrNull($data['email_template_id'] ?? null),
            freeTextId: ScalarCaster::toIntOrNull($data['free_text_id'] ?? null),
            templateId: ScalarCaster::toIntOrNull($data['template_id'] ?? null),
            taxes: $taxes,
            items: $items,
        );
    }

    /**
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
            'number_pre' => $this->numberPre,
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
            'total_gross' => $this->totalGross,
            'total_net' => $this->totalNet,
            'reduction' => $this->reduction,
            'email_sender' => $this->emailSender,
            'email_subject' => $this->emailSubject,
            'email_message' => $this->emailMessage,
            'email_template_id' => $this->emailTemplateId,
            'free_text_id' => $this->freeTextId,
            'template_id' => $this->templateId,
        ];

        if ([] !== $this->taxes) {
            $data['taxes'] = [
                'tax' => array_map(
                    static fn (array $t): array => [
                        'name' => $t['name'],
                        'rate' => $t['rate'],
                        'amount' => $t['amount'],
                    ],
                    $this->taxes,
                ),
            ];
        }

        if ([] !== $this->items) {
            $data['recurring-items'] = [
                'recurring-item' => array_map(
                    static fn (RecurringItem $item): array => $item->toArray(),
                    $this->items,
                ),
            ];
        }

        return $data;
    }
}
