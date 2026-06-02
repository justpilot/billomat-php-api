<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Model\Enum\ConfirmationStatus;
use Justpilot\Billomat\Model\Enum\NetGross;
use Throwable;

use const DATE_ATOM;

/**
 * Repräsentiert eine Auftragsbestätigung (Confirmation) aus der Billomat-API.
 *
 * Doku: https://www.billomat.com/en/api/confirmations/
 */
final readonly class Confirmation
{
    /**
     * @param list<array{name: string, rate: float, amount: float}> $taxes
     * @param list<ConfirmationItem>                                $items
     */
    public function __construct(
        public ?int $id,
        public int $clientId,
        public ?int $contactId = null,
        public ?DateTimeImmutable $created = null,
        public ?string $confirmationNumber = null,
        public ?int $number = null,
        public ?string $numberPre = null,
        public ?int $numberLength = null,
        public ?ConfirmationStatus $status = null,
        public ?DateTimeImmutable $date = null,
        public ?string $address = null,
        public ?float $discountRate = null,
        public ?DateTimeImmutable $discountDate = null,
        public ?int $discountDays = null,
        public ?float $discountAmount = null,
        public ?string $title = null,
        public ?string $label = null,
        public ?string $intro = null,
        public ?string $note = null,
        public ?float $totalGross = null,
        public ?float $totalNet = null,
        public ?NetGross $netGross = null,
        public ?string $reduction = null,
        public ?float $totalGrossUnreduced = null,
        public ?float $totalNetUnreduced = null,
        public ?string $currencyCode = null,
        public ?float $quote = null,
        public ?int $offerId = null,
        public ?int $freeTextId = null,
        public ?int $templateId = null,
        public array $taxes = [],
        public ?string $customerportalUrl = null,
        public array $items = [],
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $created = self::parseDateTime($data['created'] ?? null);
        $date = self::parseDateTime($data['date'] ?? null);
        $discountDate = self::parseDateTime($data['discount_date'] ?? null);

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
        if (isset($data['confirmation-items']['confirmation-item'])) {
            $rawItems = $data['confirmation-items']['confirmation-item'];

            if (isset($rawItems['id'])) {
                $rawItems = [$rawItems];
            }

            if (\is_array($rawItems)) {
                $items = array_map(
                    ConfirmationItem::fromArray(...),
                    $rawItems,
                );
            }
        }

        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            clientId: (int) ($data['client_id'] ?? 0),
            contactId: isset($data['contact_id']) && '' !== $data['contact_id']
                ? (int) $data['contact_id']
                : null,
            created: $created,
            confirmationNumber: $data['confirmation_number'] ?? null,
            number: isset($data['number']) && '' !== $data['number']
                ? (int) $data['number']
                : null,
            numberPre: $data['number_pre'] ?? null,
            numberLength: isset($data['number_length']) ? (int) $data['number_length'] : null,
            status: ConfirmationStatus::fromApi(isset($data['status']) ? (string) $data['status'] : null),
            date: $date,
            address: $data['address'] ?? null,
            discountRate: isset($data['discount_rate']) ? (float) $data['discount_rate'] : null,
            discountDate: $discountDate,
            discountDays: isset($data['discount_days']) ? (int) $data['discount_days'] : null,
            discountAmount: isset($data['discount_amount']) ? (float) $data['discount_amount'] : null,
            title: $data['title'] ?? null,
            label: $data['label'] ?? null,
            intro: $data['intro'] ?? null,
            note: $data['note'] ?? null,
            totalGross: isset($data['total_gross']) ? (float) $data['total_gross'] : null,
            totalNet: isset($data['total_net']) ? (float) $data['total_net'] : null,
            netGross: isset($data['net_gross'])
                ? NetGross::tryFrom((string) $data['net_gross'])
                : null,
            reduction: $data['reduction'] ?? null,
            totalGrossUnreduced: isset($data['total_gross_unreduced'])
                ? (float) $data['total_gross_unreduced']
                : null,
            totalNetUnreduced: isset($data['total_net_unreduced'])
                ? (float) $data['total_net_unreduced']
                : null,
            currencyCode: $data['currency_code'] ?? null,
            quote: isset($data['quote']) ? (float) $data['quote'] : null,
            offerId: isset($data['offer_id']) && '' !== $data['offer_id']
                ? (int) $data['offer_id']
                : null,
            freeTextId: isset($data['free_text_id']) && '' !== $data['free_text_id']
                ? (int) $data['free_text_id']
                : null,
            templateId: isset($data['template_id']) && '' !== $data['template_id']
                ? (int) $data['template_id']
                : null,
            taxes: $taxes,
            customerportalUrl: $data['customerportal_url'] ?? null,
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
            'confirmation_number' => $this->confirmationNumber,
            'number' => $this->number,
            'number_pre' => $this->numberPre,
            'number_length' => $this->numberLength,
            'status' => $this->status?->value,
            'date' => $this->date?->format('Y-m-d'),
            'address' => $this->address,
            'discount_rate' => $this->discountRate,
            'discount_date' => $this->discountDate?->format('Y-m-d'),
            'discount_days' => $this->discountDays,
            'discount_amount' => $this->discountAmount,
            'title' => $this->title,
            'label' => $this->label,
            'intro' => $this->intro,
            'note' => $this->note,
            'total_gross' => $this->totalGross,
            'total_net' => $this->totalNet,
            'net_gross' => $this->netGross?->value,
            'reduction' => $this->reduction,
            'total_gross_unreduced' => $this->totalGrossUnreduced,
            'total_net_unreduced' => $this->totalNetUnreduced,
            'currency_code' => $this->currencyCode,
            'quote' => $this->quote,
            'offer_id' => $this->offerId,
            'free_text_id' => $this->freeTextId,
            'template_id' => $this->templateId,
            'customerportal_url' => $this->customerportalUrl,
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
            $data['confirmation-items'] = [
                'confirmation-item' => array_map(
                    static fn (ConfirmationItem $item): array => $item->toArray(),
                    $this->items,
                ),
            ];
        }

        return $data;
    }

    private static function parseDateTime(?string $value): ?DateTimeImmutable
    {
        if (null === $value || '' === $value) {
            return null;
        }

        try {
            return new DateTimeImmutable($value);
        } catch (Throwable) {
            return null;
        }
    }
}
