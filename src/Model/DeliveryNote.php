<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Model\Enum\DeliveryNoteStatus;
use Justpilot\Billomat\Model\Enum\NetGross;
use Throwable;

use const DATE_ATOM;

/**
 * Repräsentiert einen Lieferschein (Delivery Note) aus der Billomat-API.
 *
 * Doku: https://www.billomat.com/en/api/delivery-notes/
 */
final readonly class DeliveryNote
{
    /**
     * @param list<array{name: string, rate: float, amount: float}> $taxes
     * @param list<DeliveryNoteItem>                                $items
     */
    public function __construct(
        public ?int $id,
        public int $clientId,
        public ?int $contactId = null,
        public ?DateTimeImmutable $created = null,
        public ?string $deliveryNoteNumber = null,
        public ?int $number = null,
        public ?string $numberPre = null,
        public ?int $numberLength = null,
        public ?DeliveryNoteStatus $status = null,
        public ?DateTimeImmutable $date = null,
        public ?string $address = null,
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
        /** Quell-Rechnung. */
        public ?int $invoiceId = null,
        /** Quell-Auftragsbestätigung. */
        public ?int $confirmationId = null,
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
        if (isset($data['delivery-note-items']['delivery-note-item'])) {
            $rawItems = $data['delivery-note-items']['delivery-note-item'];

            if (isset($rawItems['id'])) {
                $rawItems = [$rawItems];
            }

            if (\is_array($rawItems)) {
                $items = array_map(
                    DeliveryNoteItem::fromArray(...),
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
            deliveryNoteNumber: $data['delivery_note_number'] ?? null,
            number: isset($data['number']) && '' !== $data['number']
                ? (int) $data['number']
                : null,
            numberPre: $data['number_pre'] ?? null,
            numberLength: isset($data['number_length']) ? (int) $data['number_length'] : null,
            status: DeliveryNoteStatus::fromApi(isset($data['status']) ? (string) $data['status'] : null),
            date: $date,
            address: $data['address'] ?? null,
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
            invoiceId: isset($data['invoice_id']) && '' !== $data['invoice_id']
                ? (int) $data['invoice_id']
                : null,
            confirmationId: isset($data['confirmation_id']) && '' !== $data['confirmation_id']
                ? (int) $data['confirmation_id']
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
            'delivery_note_number' => $this->deliveryNoteNumber,
            'number' => $this->number,
            'number_pre' => $this->numberPre,
            'number_length' => $this->numberLength,
            'status' => $this->status?->value,
            'date' => $this->date?->format('Y-m-d'),
            'address' => $this->address,
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
            'invoice_id' => $this->invoiceId,
            'confirmation_id' => $this->confirmationId,
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
            $data['delivery-note-items'] = [
                'delivery-note-item' => array_map(
                    static fn (DeliveryNoteItem $item): array => $item->toArray(),
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
