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
        /**
         * @deprecated since 3.1 – Billomat liefert `supply_date` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.
         */
        public ?DateTimeImmutable $supplyDate = null,
        public ?DateTimeImmutable $dueDate = null,
        /**
         * @deprecated since 3.1 – Billomat liefert `due_days` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.
         */
        public ?int $dueDays = null,
        /**
         * @deprecated since 3.1 – Billomat liefert `paid_at` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.
         */
        public ?DateTimeImmutable $paidAt = null,
        public ?IncomingStatus $status = null,
        public ?string $incomingNumber = null,
        public ?string $address = null,
        public ?string $label = null,
        /**
         * @deprecated since 3.1 – Billomat liefert `intro` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.
         */
        public ?string $intro = null,
        public ?string $note = null,
        public ?float $totalGross = null,
        public ?float $totalNet = null,
        public ?float $paidAmount = null,
        public ?float $openAmount = null,
        public ?string $currencyCode = null,
        public ?float $quote = null,
        public ?DateTimeImmutable $updated = null,
        public ?string $customfield = null,
        public ?string $clientNumber = null,
        public ?string $expenseAccountNumber = null,
        public ?string $category = null,
        public ?int $pageCount = null,
        public ?bool $hasAttachment = null,
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
            // Billomat liefert die Belegnummer als `number`; `incoming_number` nur als Fallback.
            incomingNumber: ScalarCaster::toStringOrNull($data['number'] ?? $data['incoming_number'] ?? null),
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
            updated: ScalarCaster::toDateTimeOrNull($data['updated'] ?? null),
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
            clientNumber: ScalarCaster::toStringOrNull($data['client_number'] ?? null),
            expenseAccountNumber: ScalarCaster::toStringOrNull($data['expense_account_number'] ?? null),
            category: ScalarCaster::toStringOrNull($data['category'] ?? null),
            pageCount: ScalarCaster::toIntOrNull($data['page_count'] ?? null),
            hasAttachment: ScalarCaster::toBoolOrNull($data['has_attachment'] ?? null),
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
            'updated' => $this->updated?->format(DATE_ATOM),
            'customfield' => $this->customfield,
            'client_number' => $this->clientNumber,
            'expense_account_number' => $this->expenseAccountNumber,
            'category' => $this->category,
            'page_count' => $this->pageCount,
            'has_attachment' => $this->hasAttachment,
        ];
    }
}
