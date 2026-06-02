<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Throwable;

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
        $created = null;
        if (!empty($data['created'])) {
            try {
                $created = new DateTimeImmutable((string) $data['created']);
            } catch (Throwable) {
                $created = null;
            }
        }

        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            created: $created,
            articleNumber: $data['article_number'] ?? null,
            number: isset($data['number']) && '' !== $data['number']
                ? (int) $data['number']
                : null,
            numberPre: $data['number_pre'] ?? null,
            numberLength: isset($data['number_length']) ? (int) $data['number_length'] : null,
            title: $data['title'] ?? null,
            description: $data['description'] ?? null,
            salesPrice: isset($data['sales_price']) ? (float) $data['sales_price'] : null,
            currencyCode: $data['currency_code'] ?? null,
            salesPrice2: isset($data['sales_price2']) ? (float) $data['sales_price2'] : null,
            salesPrice3: isset($data['sales_price3']) ? (float) $data['sales_price3'] : null,
            salesPrice4: isset($data['sales_price4']) ? (float) $data['sales_price4'] : null,
            salesPrice5: isset($data['sales_price5']) ? (float) $data['sales_price5'] : null,
            unit: $data['unit'] ?? null,
            unitId: isset($data['unit_id']) && '' !== $data['unit_id']
                ? (int) $data['unit_id']
                : null,
            purchasePrice: isset($data['purchase_price']) ? (float) $data['purchase_price'] : null,
            purchasePriceCurrencyCode: $data['purchase_price_currency_code'] ?? null,
            supplierId: isset($data['supplier_id']) && '' !== $data['supplier_id']
                ? (int) $data['supplier_id']
                : null,
            taxId: isset($data['tax_id']) && '' !== $data['tax_id']
                ? (int) $data['tax_id']
                : null,
            categoryId: isset($data['category_id']) && '' !== $data['category_id']
                ? (int) $data['category_id']
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
