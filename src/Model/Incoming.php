<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Model\Enum\IncomingStatus;
use Throwable;

use const DATE_ATOM;

/**
 * Eingangsrechnung (Incoming) aus der Billomat-API.
 *
 * Doku: https://www.billomat.com/en/api/incomings/
 */
final readonly class Incoming
{
    public function __construct(
        public ?int $id,
        public ?int $supplierId,
        public ?DateTimeImmutable $created = null,
        public ?DateTimeImmutable $date = null,
        public ?DateTimeImmutable $supplyDate = null,
        public ?DateTimeImmutable $dueDate = null,
        public ?int $dueDays = null,
        public ?DateTimeImmutable $paidAt = null,
        public ?IncomingStatus $status = null,
        public ?string $incomingNumber = null,
        public ?string $address = null,
        public ?string $label = null,
        public ?string $intro = null,
        public ?string $note = null,
        public ?float $totalGross = null,
        public ?float $totalNet = null,
        public ?float $paidAmount = null,
        public ?float $openAmount = null,
        public ?string $currencyCode = null,
        public ?float $quote = null,
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            supplierId: isset($data['supplier_id']) && '' !== $data['supplier_id']
                ? (int) $data['supplier_id']
                : null,
            created: self::parseDateTime($data['created'] ?? null),
            date: self::parseDateTime($data['date'] ?? null),
            supplyDate: self::parseDateTime($data['supply_date'] ?? null),
            dueDate: self::parseDateTime($data['due_date'] ?? null),
            dueDays: isset($data['due_days']) ? (int) $data['due_days'] : null,
            paidAt: self::parseDateTime($data['paid_at'] ?? null),
            status: IncomingStatus::fromApi(isset($data['status']) ? (string) $data['status'] : null),
            incomingNumber: $data['incoming_number'] ?? null,
            address: $data['address'] ?? null,
            label: $data['label'] ?? null,
            intro: $data['intro'] ?? null,
            note: $data['note'] ?? null,
            totalGross: isset($data['total_gross']) ? (float) $data['total_gross'] : null,
            totalNet: isset($data['total_net']) ? (float) $data['total_net'] : null,
            paidAmount: isset($data['paid_amount']) ? (float) $data['paid_amount'] : null,
            openAmount: isset($data['open_amount']) ? (float) $data['open_amount'] : null,
            currencyCode: $data['currency_code'] ?? null,
            quote: isset($data['quote']) ? (float) $data['quote'] : null,
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'supplier_id' => $this->supplierId,
            'created' => $this->created?->format(DATE_ATOM),
            'date' => $this->date?->format('Y-m-d'),
            'supply_date' => $this->supplyDate?->format('Y-m-d'),
            'due_date' => $this->dueDate?->format('Y-m-d'),
            'due_days' => $this->dueDays,
            'paid_at' => $this->paidAt?->format('Y-m-d'),
            'status' => $this->status?->value,
            'incoming_number' => $this->incomingNumber,
            'address' => $this->address,
            'label' => $this->label,
            'intro' => $this->intro,
            'note' => $this->note,
            'total_gross' => $this->totalGross,
            'total_net' => $this->totalNet,
            'paid_amount' => $this->paidAmount,
            'open_amount' => $this->openAmount,
            'currency_code' => $this->currencyCode,
            'quote' => $this->quote,
        ];
    }

    private static function parseDateTime(mixed $value): ?DateTimeImmutable
    {
        if (!\is_string($value) || '' === $value) {
            return null;
        }

        try {
            return new DateTimeImmutable($value);
        } catch (Throwable) {
            return null;
        }
    }
}
