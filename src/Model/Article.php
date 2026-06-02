<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;

use const DATE_ATOM;

/**
 * Artikel (Article) aus der Billomat-API.
 *
 * Doku: https://www.billomat.com/en/api/articles/
 */
final readonly class Article
{
    public function __construct(
        public ?int $id,
        public ?DateTimeImmutable $created,
        public ?string $articleNumber = null,
        public ?int $number = null,
        public ?string $numberPre = null,
        public ?int $numberLength = null,
        public ?string $title = null,
        public ?string $description = null,
        public ?float $salesPrice = null,
        public ?string $currencyCode = null,
        public ?float $salesPrice2 = null,
        public ?float $salesPrice3 = null,
        public ?float $salesPrice4 = null,
        public ?float $salesPrice5 = null,
        public ?string $unit = null,
        public ?int $unitId = null,
        public ?float $purchasePrice = null,
        public ?string $purchasePriceCurrencyCode = null,
        public ?int $supplierId = null,
        public ?int $taxId = null,
        public ?int $categoryId = null,
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ScalarCaster::toIntOrNull($data['id'] ?? null),
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            articleNumber: ScalarCaster::toStringOrNull($data['article_number'] ?? null),
            number: ScalarCaster::toIntOrNull($data['number'] ?? null),
            numberPre: ScalarCaster::toStringOrNull($data['number_pre'] ?? null),
            numberLength: ScalarCaster::toIntOrNull($data['number_length'] ?? null),
            title: ScalarCaster::toStringOrNull($data['title'] ?? null),
            description: ScalarCaster::toStringOrNull($data['description'] ?? null),
            salesPrice: ScalarCaster::toFloatOrNull($data['sales_price'] ?? null),
            currencyCode: ScalarCaster::toStringOrNull($data['currency_code'] ?? null),
            salesPrice2: ScalarCaster::toFloatOrNull($data['sales_price2'] ?? null),
            salesPrice3: ScalarCaster::toFloatOrNull($data['sales_price3'] ?? null),
            salesPrice4: ScalarCaster::toFloatOrNull($data['sales_price4'] ?? null),
            salesPrice5: ScalarCaster::toFloatOrNull($data['sales_price5'] ?? null),
            unit: ScalarCaster::toStringOrNull($data['unit'] ?? null),
            unitId: ScalarCaster::toIntOrNull($data['unit_id'] ?? null),
            purchasePrice: ScalarCaster::toFloatOrNull($data['purchase_price'] ?? null),
            purchasePriceCurrencyCode: ScalarCaster::toStringOrNull($data['purchase_price_currency_code'] ?? null),
            supplierId: ScalarCaster::toIntOrNull($data['supplier_id'] ?? null),
            taxId: ScalarCaster::toIntOrNull($data['tax_id'] ?? null),
            categoryId: ScalarCaster::toIntOrNull($data['category_id'] ?? null),
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'created' => $this->created?->format(DATE_ATOM),
            'article_number' => $this->articleNumber,
            'number' => $this->number,
            'number_pre' => $this->numberPre,
            'number_length' => $this->numberLength,
            'title' => $this->title,
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
            'supplier_id' => $this->supplierId,
            'tax_id' => $this->taxId,
            'category_id' => $this->categoryId,
        ];
    }
}
