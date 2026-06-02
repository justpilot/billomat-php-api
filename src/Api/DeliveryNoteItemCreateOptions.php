<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\InvoiceItemType;

use const ARRAY_FILTER_USE_BOTH;

/**
 * Typisierter Payload für POST /delivery-notes (delivery-note-items-Block)
 * bzw. POST /delivery-note-items.
 */
final class DeliveryNoteItemCreateOptions
{
    public ?InvoiceItemType $type = null;

    public ?int $articleId = null;

    public ?string $title = null;

    public ?string $description = null;

    public ?string $unit = null;

    public ?string $taxName = null;

    public ?float $taxRate = null;

    public ?bool $taxChangedManually = null;

    public ?string $reduction = null;

    public ?int $position = null;

    public function __construct(
        public float $quantity,
        public float $unitPrice,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [
            'type' => $this->type?->value,
            'article_id' => $this->articleId,
            'title' => $this->title,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice,
            'unit' => $this->unit,
            'tax_name' => $this->taxName,
            'tax_rate' => $this->taxRate,
            'tax_changed_manually' => $this->taxChangedManually,
            'reduction' => $this->reduction,
            'position' => $this->position,
        ];

        return array_filter(
            $data,
            static fn ($v, string $k): bool => null !== $v || \in_array($k, ['quantity', 'unit_price'], true),
            ARRAY_FILTER_USE_BOTH,
        );
    }
}
