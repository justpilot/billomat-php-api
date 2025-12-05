<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\InvoiceItemType;

final class InvoiceItemCreateOptions
{
    /** @var InvoiceItemType|null */
    public ?InvoiceItemType $type = null;

    public ?int $articleId = null;
    public ?string $title = null;
    public ?string $description = null;
    public float $quantity;
    public float $unitPrice;
    public ?string $unit = null;
    public ?string $taxName = null;
    public ?float $taxRate = null;
    public ?string $reduction = null;
    public ?int $position = null;

    public function __construct(
        float $quantity,
        float $unitPrice,
    )
    {
        $this->quantity = $quantity;
        $this->unitPrice = $unitPrice;
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
            'reduction' => $this->reduction,
            'position' => $this->position,
        ];

        return array_filter(
            $data,
            static fn($v, $k) => $v !== null || \in_array($k, ['quantity', 'unit_price'], true),
            ARRAY_FILTER_USE_BOTH
        );
    }
}