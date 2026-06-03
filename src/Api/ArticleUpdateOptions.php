<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\ArticleType;
use Justpilot\Billomat\Model\Enum\NetGross;

/**
 * Typisierter Payload für PUT /articles/{id}.
 */
final class ArticleUpdateOptions
{
    public ?string $title = null;

    public ?string $numberPre = null;

    public ?int $number = null;

    public ?int $numberLength = null;

    public ?string $description = null;

    public ?float $salesPrice = null;

    public ?string $currencyCode = null;

    public ?float $salesPrice2 = null;

    public ?float $salesPrice3 = null;

    public ?float $salesPrice4 = null;

    public ?float $salesPrice5 = null;

    public ?string $unit = null;

    public ?int $unitId = null;

    public ?float $purchasePrice = null;

    public ?string $purchasePriceCurrencyCode = null;

    public ?NetGross $purchasePriceNetGross = null;

    public ?int $supplierId = null;

    public ?int $taxId = null;

    public ?int $categoryId = null;

    public ?ArticleType $type = null;

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [
            'title' => $this->title,
            'number_pre' => $this->numberPre,
            'number' => $this->number,
            'number_length' => $this->numberLength,
            'description' => $this->description,
            'sales_price' => $this->salesPrice,
            'currency_code' => $this->currencyCode,
            'sales_price2' => $this->salesPrice2,
            'sales_price3' => $this->salesPrice3,
            'sales_price4' => $this->salesPrice4,
            'sales_price5' => $this->salesPrice5,
            'unit' => $this->unit,
            'unit_id' => $this->unitId,
            'purchase_price' => $this->purchasePrice,
            'purchase_price_currency_code' => $this->purchasePriceCurrencyCode,
            'purchase_price_net_gross' => $this->purchasePriceNetGross?->value,
            'supplier_id' => $this->supplierId,
            'tax_id' => $this->taxId,
            'category_id' => $this->categoryId,
            'type' => $this->type?->value,
        ];

        return array_filter($data, static fn (int|string|float|null $v): bool => null !== $v);
    }
}
