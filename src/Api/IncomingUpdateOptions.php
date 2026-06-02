<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use DateTimeImmutable;

/**
 * Typisierter Payload für PUT /incomings/{id}.
 */
final class IncomingUpdateOptions
{
    public ?int $supplierId = null;

    public ?string $incomingNumber = null;

    public ?DateTimeImmutable $date = null;

    public ?DateTimeImmutable $supplyDate = null;

    public ?DateTimeImmutable $dueDate = null;

    public ?int $dueDays = null;

    public ?string $address = null;

    public ?string $label = null;

    public ?string $intro = null;

    public ?string $note = null;

    public ?float $totalGross = null;

    public ?float $totalNet = null;

    public ?string $currencyCode = null;

    public ?float $quote = null;

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [
            'supplier_id' => $this->supplierId,
            'incoming_number' => $this->incomingNumber,
            'date' => $this->date?->format('Y-m-d'),
            'supply_date' => $this->supplyDate?->format('Y-m-d'),
            'due_date' => $this->dueDate?->format('Y-m-d'),
            'due_days' => $this->dueDays,
            'address' => $this->address,
            'label' => $this->label,
            'intro' => $this->intro,
            'note' => $this->note,
            'total_gross' => $this->totalGross,
            'total_net' => $this->totalNet,
            'currency_code' => $this->currencyCode,
            'quote' => $this->quote,
        ];

        return array_filter($data, static fn (int|string|float|null $v): bool => null !== $v);
    }
}
