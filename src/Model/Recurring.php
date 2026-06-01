<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Model\Enum\NetGross;
use Justpilot\Billomat\Model\Enum\RecurringAction;
use Justpilot\Billomat\Model\Enum\RecurringCycle;
use Justpilot\Billomat\Model\Enum\SupplyDateType;
use Throwable;

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
            id: isset($data['id']) ? (int) $data['id'] : null,
            clientId: (int) ($data['client_id'] ?? 0),
            contactId: isset($data['contact_id']) && '' !== $data['contact_id']
                ? (int) $data['contact_id']
                : null,
            created: self::parseDateTime($data['created'] ?? null),
            address: $data['address'] ?? null,
            supplyDate: self::parseDateTime($data['supply_date'] ?? null),
            supplyDateType: isset($data['supply_date_type'])
                ? SupplyDateType::tryFrom((string) $data['supply_date_type'])
                : null,
            dueDays: isset($data['due_days']) ? (int) $data['due_days'] : null,
            discountRate: isset($data['discount_rate']) ? (float) $data['discount_rate'] : null,
            discountDays: isset($data['discount_days']) ? (int) $data['discount_days'] : null,
            name: $data['name'] ?? null,
            numberPre: $data['number_pre'] ?? null,
            title: $data['title'] ?? null,
            label: $data['label'] ?? null,
            intro: $data['intro'] ?? null,
            note: $data['note'] ?? null,
            currencyCode: $data['currency_code'] ?? null,
            netGross: isset($data['net_gross'])
                ? NetGross::tryFrom((string) $data['net_gross'])
                : null,
            quote: isset($data['quote']) ? (float) $data['quote'] : null,
            paymentTypes: $data['payment_types'] ?? null,
            action: isset($data['action'])
                ? RecurringAction::tryFrom((string) $data['action'])
                : null,
            cycleNumber: isset($data['cycle_number']) ? (int) $data['cycle_number'] : null,
            cycle: isset($data['cycle'])
                ? RecurringCycle::tryFrom((string) $data['cycle'])
                : null,
            hour: isset($data['hour']) && '' !== $data['hour'] ? (int) $data['hour'] : null,
            startDate: self::parseDateTime($data['start_date'] ?? null),
            endDate: self::parseDateTime($data['end_date'] ?? null),
            lastCreationDate: self::parseDateTime($data['last_creation_date'] ?? null),
            nextCreationDate: self::parseDateTime($data['next_creation_date'] ?? null),
            iterations: isset($data['iterations']) && '' !== $data['iterations']
                ? (int) $data['iterations']
                : null,
            counter: isset($data['counter']) ? (int) $data['counter'] : null,
            totalGross: isset($data['total_gross']) ? (float) $data['total_gross'] : null,
            totalNet: isset($data['total_net']) ? (float) $data['total_net'] : null,
            reduction: $data['reduction'] ?? null,
            emailSender: $data['email_sender'] ?? null,
            emailSubject: $data['email_subject'] ?? null,
            emailMessage: $data['email_message'] ?? null,
            emailTemplateId: isset($data['email_template_id']) && '' !== $data['email_template_id']
                ? (int) $data['email_template_id']
                : null,
            freeTextId: isset($data['free_text_id']) && '' !== $data['free_text_id']
                ? (int) $data['free_text_id']
                : null,
            templateId: isset($data['template_id']) && '' !== $data['template_id']
                ? (int) $data['template_id']
                : null,
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

    private static function parseDateTime(mixed $value): ?DateTimeImmutable
    {
        if (!\is_string($value) || '' === trim($value)) {
            return null;
        }

        try {
            return new DateTimeImmutable($value);
        } catch (Throwable) {
            return null;
        }
    }
}
