<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\IncomingStatus;

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
            id: ScalarCaster::toIntOrNull($data['id'] ?? null),
            supplierId: ScalarCaster::toIntOrNull($data['supplier_id'] ?? null),
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            date: ScalarCaster::toDateTimeOrNull($data['date'] ?? null),
            supplyDate: ScalarCaster::toDateTimeOrNull($data['supply_date'] ?? null),
            dueDate: ScalarCaster::toDateTimeOrNull($data['due_date'] ?? null),
            dueDays: ScalarCaster::toIntOrNull($data['due_days'] ?? null),
            paidAt: ScalarCaster::toDateTimeOrNull($data['paid_at'] ?? null),
            status: IncomingStatus::fromApi(isset($data['status']) ? (string) $data['status'] : null),
            incomingNumber: ScalarCaster::toStringOrNull($data['incoming_number'] ?? null),
            address: ScalarCaster::toStringOrNull($data['address'] ?? null),
            label: ScalarCaster::toStringOrNull($data['label'] ?? null),
            intro: ScalarCaster::toStringOrNull($data['intro'] ?? null),
            note: ScalarCaster::toStringOrNull($data['note'] ?? null),
            totalGross: ScalarCaster::toFloatOrNull($data['total_gross'] ?? null),
            totalNet: ScalarCaster::toFloatOrNull($data['total_net'] ?? null),
            paidAmount: ScalarCaster::toFloatOrNull($data['paid_amount'] ?? null),
            openAmount: ScalarCaster::toFloatOrNull($data['open_amount'] ?? null),
            currencyCode: ScalarCaster::toStringOrNull($data['currency_code'] ?? null),
            quote: ScalarCaster::toFloatOrNull($data['quote'] ?? null),
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
}
