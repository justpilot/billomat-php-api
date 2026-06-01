<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Model\Enum\InvoiceItemType;

use const FILTER_VALIDATE_BOOLEAN;

/**
 * Position einer Abo-Rechnung.
 *
 * Strukturell identisch zu InvoiceItem, referenziert aber `recurring_id`
 * statt `invoice_id`.
 *
 * Doku: https://www.billomat.com/api/abo-rechnungen/positionen/
 */
final readonly class RecurringItem
{
    public function __construct(
        public ?int $id,
        public ?int $recurringId,
        public ?int $articleId,
        public ?int $position,
        public ?string $unit,
        public float $quantity,
        public float $unitPrice,
        public ?string $taxName,
        public ?float $taxRate,
        public ?bool $taxChangedManually,
        public ?string $title,
        public ?string $description,
        public ?string $reduction,
        public ?InvoiceItemType $type,
        public ?float $totalGross,
        public ?float $totalNet,
        public ?float $totalGrossUnreduced,
        public ?float $totalNetUnreduced,
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            recurringId: isset($data['recurring_id']) && '' !== $data['recurring_id']
                ? (int) $data['recurring_id']
                : null,
            articleId: isset($data['article_id']) && '' !== $data['article_id']
                ? (int) $data['article_id']
                : null,
            position: isset($data['position']) ? (int) $data['position'] : null,
            unit: $data['unit'] ?? null,
            quantity: isset($data['quantity']) ? (float) $data['quantity'] : 0.0,
            unitPrice: isset($data['unit_price']) ? (float) $data['unit_price'] : 0.0,
            taxName: $data['tax_name'] ?? null,
            taxRate: isset($data['tax_rate']) ? (float) $data['tax_rate'] : null,
            taxChangedManually: isset($data['tax_changed_manually'])
                ? filter_var($data['tax_changed_manually'], FILTER_VALIDATE_BOOLEAN)
                : null,
            title: $data['title'] ?? null,
            description: $data['description'] ?? null,
            reduction: $data['reduction'] ?? null,
            type: isset($data['type']) ? InvoiceItemType::tryFrom((string) $data['type']) : null,
            totalGross: isset($data['total_gross']) ? (float) $data['total_gross'] : null,
            totalNet: isset($data['total_net']) ? (float) $data['total_net'] : null,
            totalGrossUnreduced: isset($data['total_gross_unreduced'])
                ? (float) $data['total_gross_unreduced']
                : null,
            totalNetUnreduced: isset($data['total_net_unreduced'])
                ? (float) $data['total_net_unreduced']
                : null,
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'recurring_id' => $this->recurringId,
            'article_id' => $this->articleId,
            'position' => $this->position,
            'unit' => $this->unit,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice,
            'tax_name' => $this->taxName,
            'tax_rate' => $this->taxRate,
            'tax_changed_manually' => $this->taxChangedManually,
            'title' => $this->title,
            'description' => $this->description,
            'reduction' => $this->reduction,
            'type' => $this->type?->value,
            'total_gross' => $this->totalGross,
            'total_net' => $this->totalNet,
            'total_gross_unreduced' => $this->totalGrossUnreduced,
            'total_net_unreduced' => $this->totalNetUnreduced,
        ];
    }
}
